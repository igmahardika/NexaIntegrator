<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HotspotProfile;
use App\Models\Location;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class HotspotProfileController extends Controller
{
    public function index(Request $request)
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        $activeSiteId = session('active_site_id') ?: $request->input('location_id');
        $currentLocation = $activeSiteId ? Location::find($activeSiteId) : $locations->first();

        $profiles = collect();
        if ($currentLocation) {
            $profiles = HotspotProfile::where('location_id', $currentLocation->id)->latest()->get();
        }

        return view('admin.profiles.index', compact('profiles', 'locations', 'currentLocation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id'       => 'required|string|exists:locations,id',
            'name'              => 'required|string|max:50',
            'display_name'      => 'nullable|string|max:100',
            'rate_limit'        => 'required|string|max:50',
            'shared_users'      => 'required|integer|min:1|max:500',
            'session_timeout'   => 'required|integer|min:0',
            'idle_timeout'      => 'required|integer|min:0',
            'keepalive_timeout' => 'nullable|integer|min:0',
        ]);

        $location = Location::findOrFail($validated['location_id']);

        // Sync to MikroTik RouterOS
        $synced = false;
        $routerId = null;
        if (!empty($location->router_ip)) {
            $mikrotik = new MikrotikService($location);
            $res = $mikrotik->createOrUpdateHotspotProfile($validated);
            $synced = $res['success'] ?? false;
            $routerId = $res['id'] ?? null;
        }

        $validated['synced_to_router'] = $synced;
        $validated['router_profile_id'] = $routerId;

        HotspotProfile::updateOrCreate(
            ['location_id' => $location->id, 'name' => $validated['name']],
            $validated
        );

        $syncMsg = $synced ? ' & tersinkron ke router.' : ' (Router offline, tersimpan lokal).';

        return redirect()->back()
            ->with('success', "Hotspot Profile \"{$validated['name']}\" berhasil dibuat{$syncMsg}");
    }

    public function update(Request $request, HotspotProfile $hotspotProfile)
    {
        $validated = $request->validate([
            'display_name'      => 'nullable|string|max:100',
            'rate_limit'        => 'required|string|max:50',
            'shared_users'      => 'required|integer|min:1|max:500',
            'session_timeout'   => 'required|integer|min:0',
            'idle_timeout'      => 'required|integer|min:0',
            'keepalive_timeout' => 'nullable|integer|min:0',
        ]);

        $location = $hotspotProfile->location;
        $synced = false;

        if ($location && !empty($location->router_ip)) {
            $mikrotik = new MikrotikService($location);
            $res = $mikrotik->createOrUpdateHotspotProfile([
                'name' => $hotspotProfile->name,
                ...$validated,
            ]);
            $synced = $res['success'] ?? false;
        }

        $validated['synced_to_router'] = $synced;
        $hotspotProfile->update($validated);

        return redirect()->back()
            ->with('success', "Profil \"{$hotspotProfile->name}\" berhasil diperbarui.");
    }

    public function destroy(HotspotProfile $hotspotProfile)
    {
        $location = $hotspotProfile->location;
        if ($location && !empty($location->router_ip)) {
            $mikrotik = new MikrotikService($location);
            $mikrotik->removeHotspotProfile($hotspotProfile->name);
        }

        $hotspotProfile->delete();

        return redirect()->back()
            ->with('success', "Profil \"{$hotspotProfile->name}\" berhasil dihapus.");
    }
}
