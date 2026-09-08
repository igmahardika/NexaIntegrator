<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpBinding;
use App\Models\Location;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class IpBindingController extends Controller
{
    public function index(Request $request)
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        // Check active tenant context from session or query param
        $activeSiteId = session('active_site_id') ?: $request->input('location_id');
        $currentLocation = $activeSiteId ? Location::find($activeSiteId) : $locations->first();

        $query = IpBinding::with('location');

        if ($currentLocation) {
            $query->where('location_id', $currentLocation->id);
        }

        $type = $request->input('type', 'bypassed');
        if ($type !== 'all') {
            $query->where('type', $type);
        }

        if ($request->filled('search')) {
            $search = strtoupper(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->where('mac_address', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $bindings = $query->latest()->paginate(25)->withQueryString();

        // Counts
        $totalBypassed = IpBinding::when($currentLocation, fn($q) => $q->where('location_id', $currentLocation->id))->bypassed()->count();
        $totalBlocked  = IpBinding::when($currentLocation, fn($q) => $q->where('location_id', $currentLocation->id))->blocked()->count();

        return view('admin.policy.bindings', compact(
            'bindings', 'locations', 'currentLocation', 'type',
            'totalBypassed', 'totalBlocked'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id'     => 'required|string|exists:locations,id',
            'mac_address'     => 'required|string|max:17',
            'address'         => 'nullable|ip',
            'type'            => 'required|in:bypassed,blocked,regular',
            'device_category' => 'required|string|max:50',
            'comment'         => 'nullable|string|max:255',
        ]);

        $validated['mac_address'] = strtoupper(trim($validated['mac_address']));
        $location = Location::findOrFail($validated['location_id']);

        // Injeksi ke Router MikroTik
        $synced = false;
        $routerId = null;
        if (!empty($location->router_ip)) {
            $mikrotik = new MikrotikService($location);
            $result = $mikrotik->syncIpBinding(
                $validated['mac_address'],
                $validated['type'],
                $validated['comment'] ?? '',
                $validated['address'] ?? ''
            );
            $synced = $result['success'] ?? false;
            $routerId = $result['id'] ?? null;
        }

        $validated['synced_to_router'] = $synced;
        $validated['router_binding_id'] = $routerId;

        IpBinding::updateOrCreate(
            ['location_id' => $location->id, 'mac_address' => $validated['mac_address']],
            $validated
        );

        $actionText = $validated['type'] === 'bypassed' ? 'Whitelisted (Bypass Portal)' : 'Blacklisted (Blokir L2)';
        $syncNote = $synced ? ' & tersinkron ke Router MikroTik.' : ' (Router offline, tersimpan lokal).';

        return redirect()->back()
            ->with('success', "MAC {$validated['mac_address']} berhasil di-{$actionText}{$syncNote}");
    }

    public function destroy(IpBinding $ipBinding)
    {
        $location = $ipBinding->location;
        $mac = $ipBinding->mac_address;

        if ($location && !empty($location->router_ip)) {
            $mikrotik = new MikrotikService($location);
            $mikrotik->removeIpBinding($mac);
        }

        $ipBinding->delete();

        return redirect()->back()
            ->with('success', "Kebijakan MAC {$mac} berhasil dihapus dari sistem dan router.");
    }

    public function sync(Location $site)
    {
        if (empty($site->router_ip)) {
            return redirect()->back()->with('error', 'Router IP belum diatur pada site ini.');
        }

        $mikrotik = new MikrotikService($site);
        $bindings = IpBinding::where('location_id', $site->id)->get();
        $count = 0;

        foreach ($bindings as $b) {
            $res = $mikrotik->syncIpBinding($b->mac_address, $b->type, $b->comment ?? '', $b->address ?? '');
            if ($res['success'] ?? false) {
                $b->update(['synced_to_router' => true, 'router_binding_id' => $res['id'] ?? null]);
                $count++;
            }
        }

        return redirect()->back()
            ->with('success', "Berhasil menyinkronkan {$count} aturan IP Binding ke router {$site->name}.");
    }
}
