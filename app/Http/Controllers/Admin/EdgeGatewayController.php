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

        $activeCount = 0;
        if (!empty($result['connected'])) {
            $users = $mikrotik->getActiveUsers();
            $activeCount = count($users);

            // Synchronize database sessions: mark disconnected users as disconnected
            $activeMacs = array_values(array_filter(array_map(function ($u) {
                return !empty($u['mac']) ? strtoupper($u['mac']) : null;
            }, $users)));

            \App\Models\PortalSession::where('location_id', $site->id)
                ->where('status', 'active')
                ->whereNotIn('client_mac', $activeMacs)
                ->update([
                    'status'      => 'disconnected',
                    'logout_time' => now(),
                ]);
        }

        return response()->json([
            'site'         => $site->name,
            'ip'           => $site->router_ip,
            'port'         => $site->router_port,
            'active_users' => $activeCount,
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
     * AJAX: Live WAN Traffic Telemetry Stream.
     */
    public function traffic(Request $request, Location $site): JsonResponse
    {
        $interface = $request->input('interface', '');
        $mikrotik = new MikrotikService($site);
        $result = $mikrotik->getWanTraffic($interface);

        return response()->json($result);
    }

    /**
     * AJAX: Get real-time active hotspot users directly from router RAM.
     * When user is disconnected, they are NOT returned.
     */
    public function activeUsers(Location $site): JsonResponse
    {
        $mikrotik = new MikrotikService($site);
        $conn = $mikrotik->testConnection();
        if (empty($conn['connected'])) {
            return response()->json([
                'online' => false,
                'count'  => 0,
                'users'  => [],
                'error'  => $conn['error'] ?? 'Router unreachable',
            ]);
        }

        $users = $mikrotik->getActiveUsers();

        // Synchronize database sessions: Any session marked active whose MAC is not in active router list is disconnected!
        $activeMacs = array_values(array_filter(array_map(function ($u) {
            return !empty($u['mac']) ? strtoupper($u['mac']) : null;
        }, $users)));

        \App\Models\PortalSession::where('location_id', $site->id)
            ->where('status', 'active')
            ->whereNotIn('client_mac', $activeMacs)
            ->update([
                'status'      => 'disconnected',
                'logout_time' => now(),
            ]);

        return response()->json([
            'online' => true,
            'count'  => count($users),
            'users'  => $users,
        ]);
    }

    /**
     * AJAX: Force disconnect an active user from the router.
     */
    public function kickUser(Request $request, Location $site): JsonResponse
    {
        $mac = $request->input('mac');
        if (empty($mac)) {
            return response()->json(['success' => false, 'error' => 'MAC address is required.'], 422);
        }

        $mikrotik = new MikrotikService($site);
        $result = $mikrotik->kickUser($mac);

        // Update database session status so it does not appear active
        \App\Models\PortalSession::where('client_mac', strtoupper($mac))
            ->where('location_id', $site->id)
            ->where('status', 'active')
            ->update(['status' => 'disconnected', 'logout_time' => now()]);

        return response()->json($result);
    }
}
