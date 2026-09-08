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
use App\Services\MikrotikService;
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

        $member = PortalMember::where('username', $request->username)
            ->where('is_active', true)
            ->first();

        if (!$member || !$member->verifyPassword($request->password)) {
            return response()->json(['success' => false, 'message' => 'Username atau password salah.'], 401);
        }

        $location = Location::find($request->location_id);
        if (!$location) {
            return response()->json(['success' => false, 'message' => 'Lokasi tidak ditemukan.'], 404);
        }

        $member->touchLogin();

        $profile = config('mikrotik.member_profile', 'member-user');
        $comment = 'member|' . $member->id . '|' . $member->role;

        // Enqueue user creation for MikroTik Reverse Polling (No VPN / CGNAT ready)
        RouterUserQueue::enqueueUser(
            locationId: $location->id,
            username: $member->username,
            password: $request->password,
            profile: $profile,
            mac: $mac,
            comment: $comment,
            limitUptime: null
        );

        $routerResult = $this->authorize(
            $location,
            $member->username,
            $request->password,
            $profile,
            $mac,
            $comment
        );

        PortalSession::logLogin($location->id, $mac, $request->ip, 'member', $member->username, $request->userAgent());

        return response()->json([
            'success'  => true,
            'username' => $member->username,
            'password' => $request->password,
            'role'     => $member->role,
            'offline'  => !$routerResult['success'],
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

        RouterUserQueue::enqueueUser(
            locationId: $location->id,
            username: $username,
            password: $password,
            profile: $profile,
            mac: $mac,
            comment: $comment,
            limitUptime: '02:00:00'
        );

        $routerResult = $this->authorize($location, $username, $password, $profile, $mac, $comment);
        PortalSession::logLogin($location->id, $mac, $request->ip, 'whatsapp', $cleanPhone, $request->userAgent());

        return response()->json([
            'success'  => true,
            'username' => $username,
            'password' => $password,
            'name'     => $request->name,
            'offline'  => !$routerResult['success'],
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

        $username = 'btn-' . strtolower(Str::random(8));
        $password = Str::random(10);
        $comment  = '1click|free-access';
        $profile  = $location->template_config['survey_profile'] ?? config('mikrotik.survey_profile', 'survey-user');

        RouterUserQueue::enqueueUser(
            locationId: $location->id,
            username: $username,
            password: $password,
            profile: $profile,
            mac: $mac,
            comment: $comment,
            limitUptime: '02:00:00'
        );

        $routerResult = $this->authorize($location, $username, $password, $profile, $mac, $comment);
        PortalSession::logLogin($location->id, $mac, $request->ip, 'quick_click', 'free-button', $request->userAgent());

        return response()->json([
            'success'  => true,
            'username' => $username,
            'password' => $password,
            'offline'  => !$routerResult['success'],
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

        RouterUserQueue::enqueueUser(
            locationId: $location->id,
            username: $username,
            password: $password,
            profile: $profile,
            mac: $mac,
            comment: $comment,
            limitUptime: '02:00:00'
        );

        $routerResult = $this->authorize($location, $username, $password, $profile, $mac, $comment);
        PortalSession::logLogin($location->id, $mac, $request->ip, 'email', $request->email, $request->userAgent());

        return response()->json([
            'success'  => true,
            'username' => $username,
            'password' => $password,
            'email'    => $request->email,
            'offline'  => !$routerResult['success'],
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
        string $comment
    ): array {
        if (empty($location->router_ip)) {
            return ['success' => false, 'error' => 'Router IP not configured'];
        }

        try {
            $mikrotik = new MikrotikService($location);
            return $mikrotik->authorizeUser($username, $password, $profile, $mac, $comment);
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
