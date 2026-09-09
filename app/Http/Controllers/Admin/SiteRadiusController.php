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
            'mikrotikScript',
            'minimalLoginHtml',
            'serverHost'
        ));
    }

    /**
     * Update router & RADIUS configuration for this site.
     */
    public function update(Request $request, Location $site)
    {
        $validated = $request->validate([
            'router_ip'               => 'nullable|string|max:100',
            'router_port'             => 'nullable|integer|between:1,65535',
            'router_user'             => 'nullable|string|max:100',
            'router_password'         => 'nullable|string|max:255',
            'dns_name'                => 'nullable|string|max:255',
            'gateway_mode'            => 'nullable|string|in:direct_api,zero_tunnel,radius',
            'radius_enabled'          => 'nullable|boolean',
            'radius_server_ip'        => 'nullable|string|max:100',
            'radius_auth_port'        => 'nullable|integer|between:1,65535',
            'radius_acct_port'        => 'nullable|integer|between:1,65535',
            'radius_secret'           => 'nullable|string|max:100',
            'radius_nas_id'           => 'nullable|string|max:50',
            'radius_coa_port'         => 'nullable|integer|between:1,65535',
            'default_rate_limit'      => 'nullable|string|max:50',
            'default_session_timeout' => 'nullable|integer|min:60',
        ]);

        if (empty($validated['router_password'])) {
            unset($validated['router_password']);
        }

        if (empty($validated['gateway_mode'])) {
            $validated['gateway_mode'] = 'direct_api';
        }

        $validated['radius_enabled'] = (bool) $request->input('radius_enabled', 0);

        $site->update($validated);

        return redirect()->back()
            ->with('success', 'Konfigurasi router & edge gateway berhasil disimpan.');
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
