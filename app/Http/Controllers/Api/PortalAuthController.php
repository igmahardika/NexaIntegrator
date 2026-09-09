<?php

namespace App\Http\Controllers\Api;

use App\Actions\Portal\AuthenticateVoucherAction;
use App\Actions\Portal\ProcessSurveyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\SurveyAuthRequest;
use App\Http\Requests\Portal\VoucherAuthRequest;
use App\Models\BlacklistedDevice;
use App\Models\Location;
use App\Models\PortalMember;
use App\Models\PortalSession;
use App\Models\PortalVoucher;
use App\Models\RouterUserQueue;
use App\Models\SurveyCampaign;
use App\Models\SurveyResponse;
use App\Models\Tenant\HotspotUser;
use App\Services\MikrotikService;
use App\Services\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PortalAuthController extends Controller
{
    // ============================================================
    // SURVEY LOGIN
    // ============================================================

    public function submitSurvey(SurveyAuthRequest $request, ProcessSurveyAction $action): JsonResponse
    {
        $result = $action->execute(
            locationId: $request->location_id,
            campaignId: $request->campaign_id,
            mac:        $request->mac,
            ip:         $request->ip,
            answers:    $request->answers,
            userAgent:  $request->userAgent()
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['statusCode'] ?? 400);
        }

        return response()->json($result);
    }

    // ============================================================
    // VOUCHER LOGIN
    // ============================================================

    public function submitVoucher(VoucherAuthRequest $request, AuthenticateVoucherAction $action): JsonResponse
    {
        $result = $action->execute(
            locationId: $request->location_id,
            mac:        $request->mac,
            ip:         $request->ip,
            code:       $request->code,
            userAgent:  $request->userAgent()
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['statusCode'] ?? 400);
        }

        return response()->json($result);
    }

    // ============================================================
    // MEMBER LOGIN
    // ============================================================

    public function submitMember(Request $request): JsonResponse
    {
        $request->validate([
            'mac'         => 'required|string|max:17',
            'ip'          => 'required|string|max:45',
            'location_id' => 'required|string|exists:locations,id',
            'username'    => 'required|string|max:50',
            'password'    => 'required|string',
        ]);

        $mac    = strtoupper($request->mac);

        // Check if MAC is blacklisted
        if (BlacklistedDevice::isBlocked($mac, $request->location_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat Anda diblokir dari akses jaringan ini.',
            ], 403);
        }

        $location = Location::find($request->location_id);
        if (!$location) {
            return response()->json(['success' => false, 'message' => 'Lokasi tidak ditemukan.'], 404);
        }

        // Switch to tenant isolated database
        TenantManager::switchConnection($location);

        // 1. Check Unified HotspotUser in Tenant Database
        $hotspotUser = HotspotUser::where('identifier', $request->username)
            ->where('auth_method', HotspotUser::AUTH_MEMBER)
            ->first();

        if ($hotspotUser) {
            if (!$hotspotUser->verifySecret($request->password)) {
                return response()->json(['success' => false, 'message' => 'Password salah.'], 401);
            }

            if (!$hotspotUser->isUsable()) {
                return response()->json(['success' => false, 'message' => 'Akun dinonaktifkan atau kedaluwarsa.'], 403);
            }

            $hotspotUser->recordLogin($mac, $request->ip, $request->userAgent());
            PortalSession::logLogin($location->id, $mac, $request->ip, 'member', $hotspotUser->identifier, $request->userAgent());

            return response()->json([
                'success'  => true,
                'username' => $hotspotUser->identifier,
                'password' => $request->password,
                'role'     => 'member',
                'offline'  => false,
            ]);
        }

        // 2. Fallback to legacy PortalMember
        $member = PortalMember::where('username', $request->username)
            ->where('is_active', true)
            ->first();

        if (!$member || !$member->verifyPassword($request->password)) {
            return response()->json(['success' => false, 'message' => 'Username atau password salah.'], 401);
        }

        $member->touchLogin();
        $profile = config('mikrotik.member_profile', 'member-user');
        $comment = 'member|' . $member->id . '|' . $member->role;

        $routerResult = $this->authorize(
            $location,
            $member->username,
            $request->password,
            $profile,
            $mac,
            $comment,
            '',
            $request->ip
        );

        PortalSession::logLogin($location->id, $mac, $request->ip, 'member', $member->username, $request->userAgent());

        return response()->json([
            'success'   => true,
            'username'  => $member->username,
            'password'  => $request->password,
            'role'      => $member->role,
            'offline'   => !$routerResult['success'],
            'activated' => $routerResult['activated'] ?? false,
        ]);
    }

    // ============================================================
    // WHATSAPP LOGIN
    // ============================================================

    public function submitWhatsapp(Request $request): JsonResponse
    {
        $request->validate([
            'mac'         => 'required|string|max:17',
            'ip'          => 'required|string|max:45',
            'location_id' => 'required|string|exists:locations,id',
            'name'        => 'required|string|max:100',
            'phone'       => 'required|string|max:25',
        ]);

        $mac = strtoupper($request->mac);
        if (BlacklistedDevice::isBlocked($mac, $request->location_id)) {
            return response()->json(['success' => false, 'message' => 'Perangkat Anda diblokir.'], 403);
        }

        $location = Location::find($request->location_id);
        if (!$location) {
            return response()->json(['success' => false, 'message' => 'Lokasi tidak ditemukan.'], 404);
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
        $username   = 'wa-' . substr($cleanPhone, -8);
        $password   = Str::random(10);
        $comment    = 'wa|' . $request->name . '|' . $cleanPhone;
        $profile    = $location->template_config['survey_profile'] ?? config('mikrotik.survey_profile', 'survey-user');

        // Record in Tenant Isolated Database as HotspotUser
        TenantManager::switchConnection($location);
        $hotspotUser = HotspotUser::firstOrCreate(
            ['identifier' => $cleanPhone],
            [
                'auth_method'    => HotspotUser::AUTH_WHATSAPP,
                'status'         => HotspotUser::STATUS_ACTIVE,
                'uptime_limit'   => 7200,
                'guest_metadata' => [
                    'full_name' => $request->name,
                    'phone'     => $cleanPhone,
                ],
            ]
        );
        $hotspotUser->recordLogin($mac, $request->ip, $request->userAgent());

        $routerResult = $this->authorize($location, $username, $password, $profile, $mac, $comment, '02:00:00', $request->ip);
        PortalSession::logLogin($location->id, $mac, $request->ip, 'whatsapp', $cleanPhone, $request->userAgent());

        return response()->json([
            'success'   => true,
            'username'  => $username,
            'password'  => $password,
            'name'      => $request->name,
            'offline'   => !$routerResult['success'],
            'activated' => $routerResult['activated'] ?? false,
        ]);
    }

    // ============================================================
    // 1-CLICK BUTTON LOGIN (FREE ACCESS)
    // ============================================================

    public function submitQuick(Request $request): JsonResponse
    {
        $request->validate([
            'mac'         => 'required|string|max:17',
            'ip'          => 'required|string|max:45',
            'location_id' => 'required|string|exists:locations,id',
        ]);

        $mac = strtoupper($request->mac);
        if (BlacklistedDevice::isBlocked($mac, $request->location_id)) {
            return response()->json(['success' => false, 'message' => 'Perangkat Anda diblokir.'], 403);
        }

        $location = Location::find($request->location_id);
        if (!$location) {
            return response()->json(['success' => false, 'message' => 'Lokasi tidak ditemukan.'], 404);
        }

        $cleanMac = strtolower(str_replace([':', '-'], '', $mac));
        $username = 'btn-' . $cleanMac;
        $password = Str::random(10);
        $comment  = '1click|free-access|' . $cleanMac;
        $profile  = $location->template_config['survey_profile'] ?? config('mikrotik.survey_profile', 'survey-user');

        // Record in Tenant Isolated Database
        TenantManager::switchConnection($location);
        $hotspotUser = HotspotUser::firstOrCreate(
            ['identifier' => $username],
            [
                'auth_method'    => HotspotUser::AUTH_QUICK,
                'status'         => HotspotUser::STATUS_ACTIVE,
                'uptime_limit'   => 7200,
                'bound_mac'      => $mac,
                'guest_metadata' => [
                    'type' => 'free_one_click',
                    'mac'  => $mac,
                ],
            ]
        );
        $hotspotUser->recordLogin($mac, $request->ip, $request->userAgent());

        $routerResult = $this->authorize($location, $username, $password, $profile, $mac, $comment, '02:00:00', $request->ip);
        PortalSession::logLogin($location->id, $mac, $request->ip, 'quick_click', 'free-button', $request->userAgent());

        return response()->json([
            'success'   => true,
            'username'  => $username,
            'password'  => $password,
            'offline'   => !$routerResult['success'],
            'activated' => $routerResult['activated'] ?? false,
        ]);
    }

    // ============================================================
    // EMAIL LOGIN
    // ============================================================

    public function submitEmail(Request $request): JsonResponse
    {
        $request->validate([
            'mac'         => 'required|string|max:17',
            'ip'          => 'required|string|max:45',
            'location_id' => 'required|string|exists:locations,id',
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|max:150',
        ]);

        $mac = strtoupper($request->mac);
        if (BlacklistedDevice::isBlocked($mac, $request->location_id)) {
            return response()->json(['success' => false, 'message' => 'Perangkat Anda diblokir.'], 403);
        }

        $location = Location::find($request->location_id);
        if (!$location) {
            return response()->json(['success' => false, 'message' => 'Lokasi tidak ditemukan.'], 404);
        }

        $username = 'em-' . strtolower(Str::random(8));
        $password = Str::random(10);
        $comment  = 'email|' . $request->name . '|' . $request->email;
        $profile  = $location->template_config['survey_profile'] ?? config('mikrotik.survey_profile', 'survey-user');

        // Record in Tenant Isolated Database
        TenantManager::switchConnection($location);
        $hotspotUser = HotspotUser::firstOrCreate(
            ['identifier' => $request->email],
            [
                'auth_method'    => HotspotUser::AUTH_EMAIL,
                'status'         => HotspotUser::STATUS_ACTIVE,
                'uptime_limit'   => 7200,
                'guest_metadata' => [
                    'full_name' => $request->name,
                    'email'     => $request->email,
                ],
            ]
        );
        $hotspotUser->recordLogin($mac, $request->ip, $request->userAgent());

        $routerResult = $this->authorize($location, $username, $password, $profile, $mac, $comment, '02:00:00', $request->ip);
        PortalSession::logLogin($location->id, $mac, $request->ip, 'email', $request->email, $request->userAgent());

        return response()->json([
            'success'   => true,
            'username'  => $username,
            'password'  => $password,
            'email'     => $request->email,
            'offline'   => !$routerResult['success'],
            'activated' => $routerResult['activated'] ?? false,
        ]);
    }

    // ============================================================
    // HOTEL PMS LOGIN (Room Number & Guest Last Name)
    // ============================================================

    public function submitPms(Request $request): JsonResponse
    {
        $request->validate([
            'mac'         => 'required|string|max:17',
            'ip'          => 'required|string|max:45',
            'location_id' => 'required|string|exists:locations,id',
            'room_number' => 'required|string|max:20',
            'last_name'   => 'required|string|max:100',
        ]);

        $mac = strtoupper($request->mac);

        // Check if MAC is blacklisted
        if (BlacklistedDevice::isBlocked($mac, $request->location_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat Anda diblokir dari akses jaringan ini.',
            ], 403);
        }

        $location = Location::find($request->location_id);
        if (!$location) {
            return response()->json(['success' => false, 'message' => 'Lokasi site tidak ditemukan.'], 404);
        }

        // Verify with Hotel PMS Integration Engine
        $pmsService = new \App\Services\PmsIntegrationService();
        $verifyResult = $pmsService->verifyGuest($location, $request->room_number, $request->last_name);

        if (!$verifyResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $verifyResult['message'] ?? 'Data kamar atau nama belakang tidak cocok dengan data reservasi hotel.',
            ], 401);
        }

        $room = trim($request->room_number);
        $lastName = trim($request->last_name);
        $identifier = 'ROOM-' . strtoupper($room);
        $username = 'pms-' . strtolower($room) . '-' . strtolower(Str::random(4));
        $password = Str::random(10);
        $comment  = 'pms|' . $room . '|' . $lastName;
        $profile  = $location->template_config['pms_profile'] ?? config('mikrotik.member_profile', 'member-user');

        // Record in Tenant Isolated Database
        try {
            TenantManager::switchConnection($location);
            $hotspotUser = HotspotUser::updateOrCreate(
                ['identifier' => $identifier],
                [
                    'auth_method'    => HotspotUser::AUTH_PMS,
                    'status'         => HotspotUser::STATUS_ACTIVE,
                    'bound_mac'      => $mac,
                    'uptime_limit'   => 86400, // 24 hours standard hotel session
                    'guest_metadata' => [
                        'room'       => $room,
                        'last_name'  => $lastName,
                        'verified'   => true,
                        'pms_mode'   => $verifyResult['guest']['pms_mode'] ?? 'auto',
                    ],
                ]
            );
            $hotspotUser->recordLogin($mac, $request->ip, $request->userAgent());
        } catch (\Throwable $e) {
            // Non-fatal if tenant DB is in fallback mode
        }

        // Authorize session on MikroTik / RADIUS
        $routerResult = $this->authorize($location, $username, $password, $profile, $mac, $comment, '24:00:00', $request->ip);

        // Lawful Interception & Forensic Session Logging
        PortalSession::logLogin(
            $location->id,
            $mac,
            $request->ip,
            'pms',
            "Room {$room} - {$lastName}",
            $request->userAgent()
        );

        return response()->json([
            'success'     => true,
            'username'    => $username,
            'password'    => $password,
            'room_number' => $room,
            'guest_name'  => $verifyResult['guest']['name'] ?? $lastName,
            'message'     => 'Selamat datang! Akses internet kamar berhasil diaktifkan.',
            'offline'     => !$routerResult['success'],
            'activated'   => $routerResult['activated'] ?? false,
        ]);
    }

    // ============================================================
    // Internal: Authorize on Router
    // ============================================================

    private function authorize(
        Location $location,
        string $username,
        string $password,
        string $profile,
        string $mac,
        string $comment,
        string $uptimeLimit = '',
        string $ip = ''
    ): array {
        if (empty($location->router_ip)) {
            return ['success' => false, 'activated' => false, 'error' => 'Router IP not configured'];
        }

        try {
            $mikrotik = new MikrotikService($location);
            return $mikrotik->authorizeUser($username, $password, $profile, $mac, $comment, $uptimeLimit, $ip);
        } catch (\Throwable $e) {
            return ['success' => false, 'activated' => false, 'error' => $e->getMessage()];
        }
    }
}
