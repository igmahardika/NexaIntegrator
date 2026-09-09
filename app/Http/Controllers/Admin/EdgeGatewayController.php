<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\MikrotikService;
use App\Services\RadiusService;
use App\Services\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EdgeGatewayController extends Controller
{
    /**
     * Dedicated entry point for Edge Gateway & RADIUS Management.
     * Renders site console if a site is active, or Fleet Hub in All Sites mode.
     */
    public function index(Request $request)
    {
        $siteId = $request->query('site_id');
        $activeSite = null;

        if (!empty($siteId) && $siteId !== 'all') {
            $activeSite = Location::where('id', $siteId)->where('is_active', true)->first();
        } elseif ($siteId === 'all' || session('active_site_id') === 'all') {
            $activeSite = null;
        } else {
            $activeSite = TenantManager::getActiveSite();
        }

        // If a specific site is active, show the interactive single-site RADIUS console
        if ($activeSite) {
            return $this->showSiteConsole($activeSite, $request);
        }

        // Global Fleet Hub: All Sites Mode
        $sites = Location::where('is_active', true)->orderBy('name')->get();

        return view('admin.radius.hub', compact('sites'));
    }

    /**
     * Interactive single-site RADIUS console renderer.
     */
    public function showSiteConsole(Location $site, Request $request): View
    {
        $radiusService = new RadiusService($site);
        $fallbackHost = parse_url(config('app.url', 'https://lcps.nexa.net.id'), PHP_URL_HOST) ?: 'lcps.nexa.net.id';

        $serverHost = $request->getHost();
        if (in_array($serverHost, ['localhost', '127.0.0.1', '']) || empty($serverHost)) {
            $serverHost = !empty($site->radius_server_ip) ? $site->radius_server_ip : $fallbackHost;
            $baseUrl = rtrim(config('app.url', 'https://lcps.nexa.net.id'), '/');
        } else {
            if (!empty($site->radius_server_ip) && in_array($serverHost, ['localhost', '127.0.0.1'])) {
                $serverHost = $site->radius_server_ip;
            }
            $scheme = $request->getScheme();
            $port = $request->getPort();
            $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : "";
            $baseUrl = "{$scheme}://{$serverHost}{$portSuffix}";
        }

        $scriptV7 = $radiusService->generateRouterOsScript($serverHost, 'v7');
        $scriptV6 = $radiusService->generateRouterOsScript($serverHost, 'v6');
        $sectionsV7 = $radiusService->getRouterOsSections($serverHost, 'v7');
        $sectionsV6 = $radiusService->getRouterOsSections($serverHost, 'v6');

        // Direct RouterOS API Provisioning Script (Single Unified Standard)
        $directApiScript = $site->getProvisioningScript($serverHost);
        $minimalLoginHtml = $radiusService->generateMinimalLoginHtml($baseUrl . '/portal');

        return view('admin.sites.radius', compact(
            'site',
            'directApiScript',
            'scriptV7',
            'scriptV6',
            'sectionsV7',
            'sectionsV6',
            'minimalLoginHtml',
            'serverHost'
        ));
    }

    /**
     * AJAX: Test direct RouterOS API connectivity and retrieve live hardware status.
     */
    public function testApi(Location $site): JsonResponse
    {
        $mikrotik = new MikrotikService($site);
        $result = $mikrotik->testConnection();

        return response()->json([
            'site' => $site->name,
            'ip'   => $site->router_ip,
            'port' => $site->router_port,
            ...$result,
        ]);
    }

    /**
     * AJAX: Test RFC 3576 Disconnect (CoA / PoD) to router.
     */
    public function testCoa(Request $request, Location $site): JsonResponse
    {
        $testMac = $request->input('mac', 'AA:BB:CC:DD:EE:FF');
        $service = new RadiusService($site);
        $result = $service->sendDisconnect($testMac, $site->router_ip ?? '192.168.88.1');

        return response()->json($result);
    }

    /**
     * 1-Click Download for minimalist login.html (< 1 KB).
     */
    public function downloadLoginHtml(Location $site): Response
    {
        $service = new RadiusService($site);
        $canonicalBase = rtrim(config('app.url', 'https://lcps.nexa.net.id'), '/');
        $baseUrl = in_array(request()->getHost(), ['127.0.0.1', 'localhost', ''])
            ? $canonicalBase . '/portal'
            : url('/portal');
        $html = $service->generateMinimalLoginHtml($baseUrl);

        return response($html, 200, [
            'Content-Type'        => 'text/html',
            'Content-Disposition' => 'attachment; filename="login.html"',
        ]);
    }

    /**
     * AJAX: Toggle Emergency Walled Garden Bypass (0.0.0.0/0 pass-all).
     */
    public function toggleEmergencyBypass(Request $request, Location $site): JsonResponse
    {
        $enable = filter_var($request->input('enable', true), FILTER_VALIDATE_BOOLEAN);
        $mikrotik = new MikrotikService($site);
        $result = $mikrotik->toggleEmergencyBypass($enable);

        return response()->json($result);
    }

    /**
     * AJAX: Toggle entire MikroTik Hotspot server (Enable / Disable Hotspot).
     */
    public function toggleHotspot(Request $request, Location $site): JsonResponse
    {
        $enable = filter_var($request->input('enable', true), FILTER_VALIDATE_BOOLEAN);
        $mikrotik = new MikrotikService($site);
        $result = $mikrotik->toggleHotspotService($enable);

        return response()->json($result);
    }

    /**
     * AJAX: Live WAN Traffic Telemetry Stream.
     */
    public function traffic(Request $request, Location $site): JsonResponse
    {
        $interface = $request->input('interface', '');
        $mikrotik = new MikrotikService($site);
        $result = $mikrotik->getWanTraffic($interface);

        return response()->json($result);
    }
}
