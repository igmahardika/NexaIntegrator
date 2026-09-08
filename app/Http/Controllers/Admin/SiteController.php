<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteRequest;
use App\Models\Location;
use App\Services\MikrotikService;
use App\Services\TemplateRegistryService;
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

    public function store(SiteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);
        $validated['router_port'] = $validated['router_port'] ?? 8728;
        $validated['active_template'] = $validated['active_template'] ?? 'modern-glass';
        $validated['radius_nas_id'] = $validated['slug'];
        $validated['radius_secret'] = $validated['radius_secret'] ?? Str::random(32);

        Location::create($validated);

        return redirect()->route('admin.sites.index')
            ->with('success', "Site & Customer \"{$validated['name']}\" berhasil ditambahkan.");
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
