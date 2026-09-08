<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessPoint;
use App\Models\Location;
use App\Services\ApWatchdogService;
use Illuminate\Http\Request;

class AccessPointController extends Controller
{
    public function index(Request $request)
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        $activeSiteId = session('active_site_id') ?: $request->input('location_id');
        $currentLocation = $activeSiteId ? Location::find($activeSiteId) : $locations->first();

        $accessPoints = collect();
        if ($currentLocation) {
            $accessPoints = AccessPoint::where('location_id', $currentLocation->id)->orderBy('name')->get();
        }

        // Metrics
        $totalAps   = $accessPoints->count();
        $onlineAps  = $accessPoints->where('status', 'online')->count();
        $offlineAps = $accessPoints->where('status', 'offline')->count();
        $avgLatency = $accessPoints->where('status', 'online')->avg('last_latency_ms');

        return view('admin.ap.index', compact(
            'accessPoints', 'locations', 'currentLocation',
            'totalAps', 'onlineAps', 'offlineAps', 'avgLatency'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id'   => 'required|string|exists:locations,id',
            'name'          => 'required|string|max:100',
            'ip_address'    => 'required|ip',
            'mac_address'   => 'nullable|string|max:17',
            'zone_location' => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:255',
        ]);

        if (!empty($validated['mac_address'])) {
            $validated['mac_address'] = strtoupper(trim($validated['mac_address']));
        }

        $ap = AccessPoint::create($validated);

        // Run initial health check immediately
        ApWatchdogService::pingAp($ap);

        return redirect()->back()
            ->with('success', "Access Point \"{$ap->name}\" berhasil ditambahkan ke pemantauan.");
    }

    public function update(Request $request, AccessPoint $accessPoint)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'ip_address'    => 'required|ip',
            'mac_address'   => 'nullable|string|max:17',
            'zone_location' => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:255',
        ]);

        if (!empty($validated['mac_address'])) {
            $validated['mac_address'] = strtoupper(trim($validated['mac_address']));
        }

        $accessPoint->update($validated);

        return redirect()->back()
            ->with('success', "Data AP \"{$accessPoint->name}\" berhasil diperbarui.");
    }

    public function destroy(AccessPoint $accessPoint)
    {
        $name = $accessPoint->name;
        $accessPoint->delete();

        return redirect()->back()
            ->with('success', "Access Point \"{$name}\" dihapus dari pemantauan.");
    }

    public function ping(AccessPoint $accessPoint)
    {
        $result = ApWatchdogService::pingAp($accessPoint);
        return response()->json($result);
    }

    public function pingAll(Request $request)
    {
        $locationId = $request->input('location_id');
        if (!$locationId) {
            return response()->json(['error' => 'Location ID required'], 400);
        }

        $results = ApWatchdogService::pingAll($locationId);
        return response()->json(['results' => $results]);
    }
}
