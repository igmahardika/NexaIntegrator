<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\RadiusService;
use Illuminate\Http\Request;

class SiteRadiusController extends Controller
{
    /**
     * Display the RADIUS configuration and MikroTik script generator page.
     */
    public function show(Location $site)
    {
        $radiusService = new RadiusService($site);
        $fallbackHost = parse_url(config('app.url', 'https://lcps.nexa.net.id'), PHP_URL_HOST) ?: 'lcps.nexa.net.id';
        
        $serverHost = request()->getHost();
        if (in_array($serverHost, ['localhost', '127.0.0.1', '']) || empty($serverHost)) {
            $serverHost = !empty($site->radius_server_ip) ? $site->radius_server_ip : $fallbackHost;
            $baseUrl = rtrim(config('app.url', 'https://lcps.nexa.net.id'), '/');
        } else {
            if (!empty($site->radius_server_ip) && in_array($serverHost, ['localhost', '127.0.0.1'])) {
                $serverHost = $site->radius_server_ip;
            }
            $scheme = request()->getScheme();
            $port = request()->getPort();
            $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : "";
            $baseUrl = "{$scheme}://{$serverHost}{$portSuffix}";
        }

        $scriptV7 = $radiusService->generateRouterOsScript($serverHost, 'v7');
        $scriptV6 = $radiusService->generateRouterOsScript($serverHost, 'v6');
        $sectionsV7 = $radiusService->getRouterOsSections($serverHost, 'v7');
        $sectionsV6 = $radiusService->getRouterOsSections($serverHost, 'v6');
        $mikrotikScript = $scriptV7;

        // Generate No-Tunnel Reverse Polling Script (1-Click for WinBox)
        $syncKeyParam = $site->radius_secret ? "?key=" . urlencode($site->radius_secret) : "";
        $syncUrl = "{$baseUrl}/api/router/{$site->slug}/sync-script{$syncKeyParam}";
        $fetchMode = str_starts_with($syncUrl, 'https://') ? 'mode=https check-certificate=no' : 'mode=http';
        $dnsHost = $serverHost;

        $noTunnelScript = <<<RSC
# =====================================================================
# WiFiPads No-Tunnel Reverse Polling Provisioning Script
# Site: {$site->name} ({$site->slug})
# Arsitektur: Reverse Polling Scheduler (Aman di balik CGNAT/ISP Swasta)
# =====================================================================

# 1. Walled Garden (Mengizinkan akses ke Cloud Controller, DNS & CDN)
/ip hotspot walled-garden ip
:do { add dst-port=53 protocol=udp action=accept comment="WiFiPads DNS UDP" } on-error={ :nothing }
:do { add dst-port=53 protocol=tcp action=accept comment="WiFiPads DNS TCP" } on-error={ :nothing }

/ip hotspot walled-garden
:do { add dst-host="*{$dnsHost}*" action=allow comment="WiFiPads Controller" } on-error={ :nothing }
:do { add dst-host="*fonts.googleapis.com*" action=allow comment="Google Fonts" } on-error={ :nothing }
:do { add dst-host="*fonts.gstatic.com*" action=allow comment="Google Fonts Static" } on-error={ :nothing }
:do { add dst-host="*unpkg.com*" action=allow comment="Alpine.js CDN" } on-error={ :nothing }

# 2. Hotspot Server & User Profiles (Izinkan HTTP-PAP & QoS)
/ip hotspot profile
:do { set [find] login-by=http-pap,http-chap } on-error={ :nothing }

/ip hotspot user profile
:do { add name="survey-user" rate-limit="2M/5M" shared-users=1 status-autorefresh=1m transparent-proxy=no } on-error={ :nothing }
:do { add name="voucher-user" rate-limit="5M/10M" shared-users=1 status-autorefresh=1m transparent-proxy=no } on-error={ :nothing }
:do { add name="member-user" rate-limit="10M/20M" shared-users=2 status-autorefresh=1m transparent-proxy=no } on-error={ :nothing }

# 3. Background Sync Script
/system script
:do { remove [find name="wifipads-sync"] } on-error={ :nothing }
add name="wifipads-sync" policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive source="
    :do {
        /tool fetch url=\"{$syncUrl}\" dst-path=\"wifipads_queue.rsc\" {$fetchMode} keep-result=yes
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
            'sectionsV7',
            'sectionsV6',
            'mikrotikScript',
            'noTunnelScript',
            'minimalLoginHtml',
            'serverHost',
            'syncUrl'
        ));
    }

    /**
     * Update RADIUS configuration for this site.
     */
    public function update(Request $request, Location $site)
    {
        $validated = $request->validate([
            'radius_enabled'          => 'boolean',
            'radius_server_ip'        => 'nullable|string|max:100',
            'radius_auth_port'        => 'required|integer|between:1,65535',
            'radius_acct_port'        => 'required|integer|between:1,65535',
            'radius_secret'           => 'required|string|max:100',
            'radius_nas_id'           => 'required|string|max:50',
            'radius_coa_port'         => 'required|integer|between:1,65535',
            'default_rate_limit'      => 'required|string|max:50',
            'default_session_timeout' => 'required|integer|min:60',
        ]);

        $validated['radius_enabled'] = (bool) $request->input('radius_enabled', 0);

        $site->update($validated);

        return redirect()->back()
            ->with('success', 'Konfigurasi RADIUS berhasil diperbarui.');
    }

    /**
     * AJAX: Test Disconnect (CoA / PoD) to router.
     */
    public function testCoa(Request $request, Location $site)
    {
        $testMac = $request->input('mac', 'AA:BB:CC:DD:EE:FF');
        $service = new RadiusService($site);
        $result = $service->sendDisconnect($testMac, $site->router_ip ?? '192.168.88.1');

        return response()->json($result);
    }
}
