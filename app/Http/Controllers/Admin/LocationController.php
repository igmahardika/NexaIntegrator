<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::withCount(['vouchers', 'sessions', 'responses'])->latest()->get();
        return view('admin.locations.index', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'address'         => 'nullable|string|max:500',
            'router_ip'       => 'nullable|ip',
            'router_port'     => 'nullable|integer|between:1,65535',
            'router_user'     => 'nullable|string|max:100',
            'router_password' => 'nullable|string|max:255',
            'dns_name'        => 'nullable|string|max:255',
            'is_active'       => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);
        $validated['router_port'] = $validated['router_port'] ?? 8728;

        Location::create($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', "Lokasi \"{$validated['name']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'address'         => 'nullable|string|max:500',
            'router_ip'       => 'nullable|ip',
            'router_port'     => 'nullable|integer|between:1,65535',
            'router_user'     => 'nullable|string|max:100',
            'router_password' => 'nullable|string|max:255',
            'dns_name'        => 'nullable|string|max:255',
            'is_active'       => 'boolean',
        ]);

        // Don't overwrite password if empty (keep existing)
        if (empty($validated['router_password'])) {
            unset($validated['router_password']);
        }

        $location->update($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', "Lokasi \"{$location->name}\" berhasil diperbarui.");
    }

    public function destroy(Location $location)
    {
        $location->delete();
        return redirect()->route('admin.locations.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }

    /**
     * AJAX: Test RouterOS connection
     */
    public function testConnection(Location $location)
    {
        $mikrotik = new MikrotikService($location);
        $result   = $mikrotik->testConnection();

        return response()->json([
            'location' => $location->name,
            'ip'       => $location->router_ip,
            'port'     => $location->router_port,
            ...$result,
        ]);
    }

    /**
     * AJAX: Get router system health
     */
    public function health(Location $location)
    {
        $mikrotik = new MikrotikService($location);
        $health   = $mikrotik->getSystemHealth();

        return response()->json($health);
    }
}
