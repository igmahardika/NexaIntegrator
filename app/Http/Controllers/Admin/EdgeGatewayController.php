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
        $serverHost = $request->getHost();
        if (in_array($serverHost, ['localhost', '127.0.0.1']) && !empty($site->radius_server_ip)) {
            $serverHost = $site->radius_server_ip;
        }
        $scheme = $request->getScheme();
        $port = $request->getPort();
        $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : "";
        $baseUrl = "{$scheme}://{$serverHost}{$portSuffix}";

        $scriptV7 = $radiusService->generateRouterOsScript($serverHost, 'v7');
        $scriptV6 = $radiusService->generateRouterOsScript($serverHost, 'v6');

        // Generate No-Tunnel Reverse Polling Script (1-Click for WinBox)
        $syncUrl = "{$baseUrl}/api/router/{$site->slug}/sync-script" . ($site->radius_secret ? "?key=" . urlencode($site->radius_secret) : "");
        $dnsHost = $serverHost;

        $noTunnelScript = <<<RSC
# =====================================================================
# WiFiPads Edge Provisioning Script (Tanpa VPN / Tanpa Tunnel)
# Site: {$site->name} ({$site->slug})
# Arsitektur: Reverse Polling Scheduler (Aman di balik CGNAT/ISP Swasta)
# =====================================================================

# 1. Walled Garden (Mengizinkan akses ke Cloud Controller & Web Fonts)
/ip hotspot walled-garden ip
:do { add dst-host="{$dnsHost}" action=accept comment="WiFiPads Controller" } on-error={ :nothing }
:do { add dst-host="fonts.googleapis.com" action=accept comment="Google Fonts" } on-error={ :nothing }
:do { add dst-host="fonts.gstatic.com" action=accept comment="Google Fonts Static" } on-error={ :nothing }
:do { add dst-host="unpkg.com" action=accept comment="Alpine.js CDN" } on-error={ :nothing }

# 2. Background Sync Script
/system script
:do { remove [find name="wifipads-sync"] } on-error={ :nothing }
add name="wifipads-sync" policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive source="
    :do {
        /tool fetch url=\"{$syncUrl}\" dst-path=\"wifipads_queue.rsc\" mode=http keep-result=yes
        :delay 1s
        /import file-name=\"wifipads_queue.rsc\"
    } on-error={
        :log debug \"WiFiPads: Sync check completed (no update or network wait)\"
    }
"

# 4. Auto Scheduler (Setiap 5 Detik secara otomatis menarik user baru)
/system scheduler
:do { remove [find name="wifipads-auto-sync"] } on-error={ :nothing }
add name="wifipads-auto-sync" interval=5s on-event="wifipads-sync" start-time=startup comment="WiFiPads Edge User Provisioning"

:log info "WiFiPads No-Tunnel Auto Sync berhasil diaktifkan pada {$site->name}!"
RSC;

        $minimalLoginHtml = $radiusService->generateMinimalLoginHtml($baseUrl . '/portal');

        return view('admin.sites.radius', compact(
            'site',
            'scriptV7',
            'scriptV6',
            'noTunnelScript',
            'minimalLoginHtml',
            'serverHost',
            'syncUrl'
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
        $baseUrl = url('/portal');
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
