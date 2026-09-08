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

        // 2. Switch tenant database connection to the site's isolated database
        \App\Services\TenantManager::switchConnection($location);

        // 3. Check Unified HotspotUser in Tenant Database
        $hotspotUser = \App\Models\Tenant\HotspotUser::where('identifier', $code)
            ->where('auth_method', \App\Models\Tenant\HotspotUser::AUTH_VOUCHER)
            ->first();

        if ($hotspotUser) {
            if (!$hotspotUser->isUsable()) {
                return [
                    'success'    => false,
                    'statusCode' => 410,
                    'message'    => 'Voucher sudah digunakan atau kedaluwarsa.',
                ];
            }

            // Record login session in tenant database
            $session = $hotspotUser->recordLogin($mac, $ip, $userAgent);

            $profileName = $hotspotUser->profile?->rate_limit ?? ($location->template_config['voucher_profile'] ?? '5M/10M');

            return [
                'success'   => true,
                'username'  => $hotspotUser->identifier,
                'password'  => $hotspotUser->secret ?: $hotspotUser->identifier,
                'duration'  => $hotspotUser->uptime_limit ? round($hotspotUser->uptime_limit / 60) : 120,
                'offline'   => false,
            ];
        }

        // Fallback: Legacy PortalVoucher check with pessimistic lock
        $lockResult = DB::transaction(function () use ($code, $locationId, $mac) {
            $voucher = PortalVoucher::where('code', $code)
                ->where(function ($q) use ($locationId) {
                    $q->where('location_id', $locationId)
                      ->orWhereNull('location_id');
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
