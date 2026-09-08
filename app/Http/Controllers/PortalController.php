<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\SurveyCampaign;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    /**
     * Display the captive portal page.
     * Called by MikroTik hotspot redirect with query parameters.
     */
    public function index(Request $request)
    {
        // Collect MikroTik params (sent via redirect URL)
        $mac      = $request->query('mac', $request->query('MAC', ''));
        $ip       = $request->query('ip', $request->getClientIp());
        $linkLogin= $request->query('link-login-only', '');
        $linkOrig = $request->query('link-orig', 'http://google.com');
        $locationSlug = $request->query('location', $request->query('loc', ''));

        // Resolve location from slug or first active location
        $location = null;
        if ($locationSlug) {
            $location = Location::where(function ($q) use ($locationSlug) {
                $q->where('slug', $locationSlug)
                  ->orWhere('id', $locationSlug);
            })->where('is_active', true)->first();
        }

        if (!$location) {
            $location = Location::where('is_active', true)->first();
        }

        // Fallback simulation data for local testing
        $isSimulation = empty($mac) || empty($linkLogin);
        if ($isSimulation) {
            $mac       = $mac ?: 'AA:BB:CC:DD:EE:FF';
            $ip        = $ip  ?: '192.168.88.100';
            $linkLogin = $linkLogin ?: '#simulation';
        }

        // Get the active campaign for this location
        $campaign  = $location?->getActiveCampaign();
        $questions = $campaign?->questions()->orderBy('order')->get() ?? collect();

        // Check if MAC already responded today
        $alreadyResponded = false;
        if ($campaign && $mac !== 'AA:BB:CC:DD:EE:FF') {
            $alreadyResponded = SurveyResponse::hasRecentResponse($mac, $campaign->id);
        }

        // Resolve site template & branding configuration (supports live preview override)
        $previewTemplate = $request->query('preview_template', $request->query('template', ''));
        $activeTemplate  = $previewTemplate ?: ($location?->active_template ?: 'access-code');
        $siteConfig      = $location
            ? $location->getResolvedTemplateConfig($previewTemplate ?: null)
            : \App\Services\TemplateRegistryService::get($activeTemplate)['default_config'];

        $allTemplates    = \App\Services\TemplateRegistryService::all();

        return view('portal.index', compact(
            'mac', 'ip', 'linkLogin', 'linkOrig',
            'location', 'campaign', 'questions',
            'alreadyResponded', 'isSimulation',
            'siteConfig', 'activeTemplate', 'allTemplates'
        ));
    }
}
