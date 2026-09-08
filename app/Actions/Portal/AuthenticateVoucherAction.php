<?php

namespace App\Actions\Portal;

use App\Models\BlacklistedDevice;
use App\Models\Location;
use App\Models\PortalSession;
use App\Models\PortalVoucher;
use App\Models\RouterUserQueue;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\DB;

class AuthenticateVoucherAction
{
    /**
     * Execute voucher authentication with pessimistic locking and site scoping.
     */
    public function execute(string $locationId, string $mac, string $ip, string $code, ?string $userAgent = null): array
    {
        $mac  = strtoupper(trim($mac));
        $code = strtoupper(trim($code));

        // 1. Blacklist verification
        if (BlacklistedDevice::isBlocked($mac, $locationId)) {
            return [
                'success'    => false,
                'statusCode' => 403,
                'message'    => 'Perangkat Anda diblokir dari akses jaringan ini.',
            ];
        }

        $location = Location::find($locationId);
        if (!$location) {
            return [
                'success'    => false,
                'statusCode' => 404,
                'message'    => 'Lokasi hotspot tidak ditemukan.',
            ];
        }

        // 2. Pessimistic lock on voucher to prevent concurrent double-spending (TOCTOU)
        $lockResult = DB::transaction(function () use ($code, $locationId, $mac) {
            $voucher = PortalVoucher::where('code', $code)
                ->where(function ($q) use ($locationId) {
                    $q->where('location_id', $locationId)
                      ->orWhereNull('location_id'); // Allow global batches if location_id is null
                })
                ->lockForUpdate()
                ->first();

            if (!$voucher) {
                return ['error' => 'not_found'];
            }

            if ($voucher->is_used) {
                return ['error' => 'already_used'];
            }

            if ($voucher->isExpired()) {
                return ['error' => 'expired'];
            }

            // Mark voucher as used inside the lock
            $voucher->markUsed($mac);

            return ['voucher' => $voucher];
        });

        if (isset($lockResult['error'])) {
            $messages = [
                'not_found'    => 'Kode voucher tidak ditemukan pada lokasi ini.',
                'already_used' => 'Voucher sudah digunakan.',
                'expired'      => 'Voucher sudah kedaluwarsa.',
            ];
            $statuses = [
                'not_found'    => 404,
                'already_used' => 409,
                'expired'      => 410,
            ];

            return [
                'success'    => false,
                'statusCode' => $statuses[$lockResult['error']],
                'message'    => $messages[$lockResult['error']],
            ];
        }

        /** @var PortalVoucher $voucher */
        $voucher  = $lockResult['voucher'];
        $username = 'vc-' . strtolower($code);
        $password = $code;
        $profile  = $location->template_config['voucher_profile'] ?? config('mikrotik.voucher_profile', 'voucher-user');
        $comment  = 'voucher|' . $voucher->id;
        $uptime   = $voucher->duration_minutes
            ? sprintf('%02d:%02d:00', floor($voucher->duration_minutes / 60), $voucher->duration_minutes % 60)
            : null;

        // 3. Enqueue user creation for MikroTik Reverse Polling (CGNAT / No-Tunnel Ready)
        RouterUserQueue::enqueueUser(
            locationId:  $location->id,
            username:    $username,
            password:    $password,
            profile:     $profile,
            mac:         $mac,
            comment:     $comment,
            limitUptime: $uptime
        );

        // 4. Direct Router Authorization (if router is reachable via direct IP)
        $routerSuccess = false;
        if (!empty($location->router_ip)) {
            try {
                $mikrotik = new MikrotikService($location);
                $res = $mikrotik->authorizeUser($username, $password, $profile, $mac, $comment);
                $routerSuccess = $res['success'] ?? false;
            } catch (\Throwable $e) {
                $routerSuccess = false;
            }
        }

        // 5. Audit log the active session
        PortalSession::logLogin($location->id, $mac, $ip, 'voucher', $code, $userAgent);

        return [
            'success'   => true,
            'username'  => $username,
            'password'  => $password,
            'duration'  => $voucher->duration_minutes,
            'offline'   => !$routerSuccess,
        ];
    }
}
