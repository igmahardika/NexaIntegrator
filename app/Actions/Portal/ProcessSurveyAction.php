<?php

namespace App\Actions\Portal;

use App\Models\BlacklistedDevice;
use App\Models\Location;
use App\Models\PortalSession;
use App\Models\RouterUserQueue;
use App\Models\SurveyCampaign;
use App\Models\SurveyResponse;
use App\Services\MikrotikService;
use Illuminate\Support\Str;

class ProcessSurveyAction
{
    /**
     * Process survey response and grant temporary internet access credentials.
     */
    public function execute(string $locationId, string $campaignId, string $mac, string $ip, array $answers, ?string $userAgent = null): array
    {
        $mac = strtoupper(trim($mac));

        // 1. Blacklist check
        if (BlacklistedDevice::isBlocked($mac, $locationId)) {
            return [
                'success'    => false,
                'statusCode' => 403,
                'message'    => 'Perangkat Anda diblokir dari akses jaringan ini.',
            ];
        }

        // 2. 24h cooldown per MAC per campaign
        if (SurveyResponse::hasRecentResponse($mac, $campaignId)) {
            return [
                'success'    => false,
                'statusCode' => 429,
                'message'    => 'Kamu sudah mengisi survei dalam 24 jam terakhir. Coba metode lain.',
            ];
        }

        $location = Location::find($locationId);
        $campaign = SurveyCampaign::find($campaignId);

        if (!$location || !$campaign) {
            return [
                'success'    => false,
                'statusCode' => 404,
                'message'    => 'Data lokasi atau kampanye tidak ditemukan.',
            ];
        }

        // 3. Record survey response
        SurveyResponse::create([
            'campaign_id' => $campaignId,
            'location_id' => $locationId,
            'client_mac'  => $mac,
            'client_ip'   => $ip,
            'answers'     => $answers,
            'created_at'  => now(),
        ]);

        // 4. Generate guest credentials and record in tenant database
        \App\Services\TenantManager::switchConnection($location);
        $username = 'sv-' . strtolower(Str::random(8));
        $password = Str::random(12);

        $hotspotUser = \App\Models\Tenant\HotspotUser::create([
            'identifier'     => $username,
            'secret'         => $password,
            'auth_method'    => \App\Models\Tenant\HotspotUser::AUTH_SURVEY,
            'status'         => \App\Models\Tenant\HotspotUser::STATUS_ACTIVE,
            'uptime_limit'   => 7200,
            'guest_metadata' => [
                'campaign_title' => $campaign->title,
                'answers'        => $answers,
            ],
        ]);
        $hotspotUser->recordLogin($mac, $ip, $userAgent);

        $comment  = 'survey|' . $campaignId;
        $profile  = $location->template_config['survey_profile'] ?? config('mikrotik.survey_profile', 'survey-user');

        // 5. Enqueue user creation for Reverse Polling
        RouterUserQueue::enqueueUser(
            locationId:  $locationId,
            username:    $username,
            password:    $password,
            profile:     $profile,
            mac:         $mac,
            comment:     $comment,
            limitUptime: '02:00:00'
        );

        // 6. Direct Router authorization
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

        // 7. Audit log the active session
        PortalSession::logLogin($locationId, $mac, $ip, 'survey', $campaignId, $userAgent);

        return [
            'success'  => true,
            'username' => $username,
            'password' => $password,
            'offline'  => !$routerSuccess,
        ];
    }
}
