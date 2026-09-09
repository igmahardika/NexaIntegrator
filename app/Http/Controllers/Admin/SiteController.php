<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteRequest;
use App\Models\HotspotProfile;
use App\Models\Location;
use App\Services\MikrotikService;
use App\Services\RadiusService;
use App\Services\TemplateRegistryService;
use App\Services\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(Request $request): View
    {
        $query = Location::withCount([
            'vouchers',
            'sessions',
            'sessions as active_sessions_count' => fn($q) => $q->where('status', 'active'),
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('business_type')) {
            $query->where('business_type', $request->business_type);
        }

        $sites = $query->latest()->get();
        $templates = TemplateRegistryService::all();

        return view('admin.sites.index', compact('sites', 'templates'));
    }

    /**
     * Dedicated 3-Step Site Creation & Gateway Provisioning Wizard.
     */
    public function create(Request $request): View
    {
        $templates = TemplateRegistryService::all();
        $serverHost = $request->getHost();
        $scheme = $request->getScheme();
        $port = $request->getPort();
        $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : "";
        $baseUrl = "{$scheme}://{$serverHost}{$portSuffix}";

        return view('admin.sites.create', compact('templates', 'serverHost', 'baseUrl'));
    }

    public function store(SiteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);
        $validated['gateway_mode'] = $validated['gateway_mode'] ?? 'zero_tunnel';
        $validated['router_port'] = $validated['router_port'] ?? 8728;
        $validated['active_template'] = $validated['active_template'] ?? 'modern-glass';
        $validated['radius_nas_id'] = $validated['slug'];
        $validated['radius_secret'] = $validated['radius_secret'] ?? Str::random(32);
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        if ($validated['gateway_mode'] === 'radius') {
            $validated['radius_enabled'] = true;
            $validated['radius_server_ip'] = $validated['radius_server_ip'] ?? $request->getHost();
            $validated['radius_auth_port'] = $validated['radius_auth_port'] ?? 1812;
            $validated['radius_acct_port'] = $validated['radius_acct_port'] ?? 1813;
            $validated['radius_coa_port'] = $validated['radius_coa_port'] ?? 3799;
        }

        $site = Location::create($validated);

        // Inisialisasi Database Tenant Terisolasi
        if ($request->input('auto_init_tenant', true)) {
            TenantManager::ensureDatabase($site);
        }

        return redirect()->route('admin.sites.provision', $site)
            ->with('success', "Site & Customer \"{$site->name}\" berhasil ditambahkan dan siap di-deploy!");
    }

    /**
     * Dedicated Provisioning Script Handover Screen.
     */
    public function provision(Location $site, Request $request): View
    {
        $serverHost = $request->getHost();
        $scheme = $request->getScheme();
        $port = $request->getPort();
        $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : "";
        $baseUrl = "{$scheme}://{$serverHost}{$portSuffix}";

        $script = $site->getProvisioningScript($serverHost, $baseUrl);
        $radiusService = new RadiusService($site);
        $minimalLoginHtml = $radiusService->generateMinimalLoginHtml("{$baseUrl}/portal");
        $syncUrl = "{$baseUrl}/api/router/{$site->slug}/sync-script" . ($site->radius_secret ? "?key=" . urlencode($site->radius_secret) : "");

        return view('admin.sites.provision', compact('site', 'script', 'minimalLoginHtml', 'serverHost', 'baseUrl', 'syncUrl'));
    }

    /**
     * Live test connection for draft parameters before site creation.
     */
    public function testDraftConnection(Request $request): JsonResponse
    {
        $request->validate([
            'router_ip' => 'required|string',
            'router_port' => 'nullable|integer|between:1,65535',
            'router_user' => 'required|string',
            'router_password' => 'nullable|string',
        ]);

        $dummy = new Location([
            'name' => 'Draft Site',
            'router_ip' => $request->input('router_ip'),
            'router_port' => (int) $request->input('router_port', 8728),
            'router_user' => $request->input('router_user'),
            'router_password' => $request->input('router_password'),
        ]);

        $mikrotik = new MikrotikService($dummy);
        $result = $mikrotik->testConnection();

        return response()->json($result);
    }

    public function update(SiteRequest $request, Location $site): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['router_password'])) {
            unset($validated['router_password']);
        }

        $site->update($validated);

        return redirect()->route('admin.sites.index')
            ->with('success', "Site \"{$site->name}\" berhasil diperbarui.");
    }

    public function destroy(Location $site): RedirectResponse
    {
        $site->delete();
        return redirect()->route('admin.sites.index')
            ->with('success', 'Site & data customer berhasil dihapus.');
    }

    public function toggle(Location $site): RedirectResponse
    {
        $site->update(['is_active' => !$site->is_active]);
        $status = $site->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Site \"{$site->name}\" berhasil {$status}.");
    }

    public function testConnection(Location $site): JsonResponse
    {
        $mikrotik = new MikrotikService($site);
        $result   = $mikrotik->testConnection();

        return response()->json([
            'site'     => $site->name,
            'ip'       => $site->router_ip,
            'port'     => $site->router_port,
            ...$result,
        ]);
    }
}
