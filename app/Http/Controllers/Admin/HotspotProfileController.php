<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HotspotProfile;
use App\Models\Location;
use App\Services\MikrotikService;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Throwable;

class HotspotProfileController extends Controller
{
    public function index(Request $request)
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        if ($locations->isEmpty()) {
            $locations = Location::orderBy('name')->get();
        }

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

        // 1. Sync to MikroTik RouterOS (if router reachable)
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

        // 2. Persist in Central Database
        $profile = HotspotProfile::updateOrCreate(
            ['location_id' => $location->id, 'name' => $validated['name']],
            $validated
        );

        // 3. Mirror into Tenant Database (for Hotspot Users & Portal)
        try {
            TenantManager::switchConnection($location);
            \App\Models\Tenant\HotspotProfile::updateOrCreate(
                ['name' => $validated['name']],
                [
                    'rate_limit'   => $validated['rate_limit'],
                    'shared_users' => $validated['shared_users'],
                    'uptime_limit' => $validated['session_timeout'] ? ($validated['session_timeout'] * 60) : 7200,
                    'description'  => $validated['display_name'] ?? $validated['name'],
                ]
            );
        } catch (Throwable $e) {
            // Tenant DB sync error shouldn't crash central
        }

        $syncMsg = $synced ? ' & tersinkron ke router MikroTik.' : ' (Router offline, tersimpan di database).';

        return redirect()->back()
            ->with('success', "Hotspot Profile \"{$validated['name']}\" berhasil disimpan{$syncMsg}");
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

        // Mirror update to Tenant Database
        if ($location) {
            try {
                TenantManager::switchConnection($location);
                \App\Models\Tenant\HotspotProfile::updateOrCreate(
                    ['name' => $hotspotProfile->name],
                    [
                        'rate_limit'   => $validated['rate_limit'],
                        'shared_users' => $validated['shared_users'],
                        'uptime_limit' => $validated['session_timeout'] ? ($validated['session_timeout'] * 60) : 7200,
                        'description'  => $validated['display_name'] ?? $hotspotProfile->name,
                    ]
                );
            } catch (Throwable $e) {
                // Ignore tenant DB error
            }
        }

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

        if ($location) {
            try {
                TenantManager::switchConnection($location);
                \App\Models\Tenant\HotspotProfile::where('name', $hotspotProfile->name)->delete();
            } catch (Throwable $e) {
                // Ignore
            }
        }

        $hotspotProfile->delete();

        return redirect()->back()
            ->with('success', "Profil \"{$hotspotProfile->name}\" berhasil dihapus.");
    }

    /**
     * Push all defined profiles for the current site to MikroTik RouterOS.
     */
    public function syncAll(Request $request)
    {
        $locationId = $request->input('location_id') ?: session('active_site_id');
        $location = Location::findOrFail($locationId);

        if (empty($location->router_ip)) {
            return redirect()->back()->with('error', 'Router IP belum dikonfigurasi untuk site ini.');
        }

        $profiles = HotspotProfile::where('location_id', $location->id)->get();

        if ($profiles->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada profil untuk disinkronkan. Buat profil terlebih dahulu.');
        }

        $mikrotik = new MikrotikService($location);
        $successCount = 0;
        $failCount = 0;

        foreach ($profiles as $profile) {
            $res = $mikrotik->createOrUpdateHotspotProfile([
                'name'              => $profile->name,
                'rate_limit'        => $profile->rate_limit,
                'shared_users'      => $profile->shared_users,
                'session_timeout'   => $profile->session_timeout,
                'idle_timeout'      => $profile->idle_timeout,
                'keepalive_timeout' => $profile->keepalive_timeout,
            ]);

            if ($res['success'] ?? false) {
                $profile->update([
                    'synced_to_router'  => true,
                    'router_profile_id' => $res['id'] ?? $profile->router_profile_id,
                ]);
                $successCount++;
            } else {
                $profile->update(['synced_to_router' => false]);
                $failCount++;
            }
        }

        // Mirror all profiles to Tenant Database
        try {
            TenantManager::switchConnection($location);
            foreach ($profiles as $profile) {
                \App\Models\Tenant\HotspotProfile::updateOrCreate(
                    ['name' => $profile->name],
                    [
                        'rate_limit'   => $profile->rate_limit,
                        'shared_users' => $profile->shared_users,
                        'uptime_limit' => $profile->session_timeout ? ($profile->session_timeout * 60) : 7200,
                        'description'  => $profile->display_name ?? $profile->name,
                    ]
                );
            }
        } catch (Throwable $e) {
            // Ignore
        }

        if ($failCount > 0) {
            return redirect()->back()->with('warning', "Sinkronisasi selesai: {$successCount} berhasil, {$failCount} gagal (cek koneksi router).");
        }

        return redirect()->back()->with('success', "Berhasil menyinkronkan seluruh ({$successCount}) profil ke router MikroTik.");
    }

    /**
     * Import existing hotspot user profiles from MikroTik RouterOS into WiFiPads.
     */
    public function importFromRouter(Request $request)
    {
        $locationId = $request->input('location_id') ?: session('active_site_id');
        $location = Location::findOrFail($locationId);

        if (empty($location->router_ip)) {
            return redirect()->back()->with('error', 'Router IP belum dikonfigurasi untuk site ini.');
        }

        $mikrotik = new MikrotikService($location);
        $res = $mikrotik->getHotspotProfilesFromRouter();

        if (!($res['success'] ?? false)) {
            return redirect()->back()->with('error', 'Gagal membaca profil dari router: ' . ($res['error'] ?? 'Koneksi gagal'));
        }

        $routerProfiles = $res['profiles'] ?? [];
        $imported = 0;

        foreach ($routerProfiles as $rp) {
            $name = $rp['name'] ?? '';
            if (empty($name) || $name === 'default') {
                continue;
            }

            // Convert session-timeout to minutes for UI consistency (default 120 min)
            $sessionTimeoutMin = 120;
            if (!empty($rp['session-timeout'])) {
                $raw = $rp['session-timeout'];
                if (str_contains($raw, ':')) {
                    $parts = explode(':', $raw);
                    if (count($parts) === 3) {
                        $sessionTimeoutMin = ((int)$parts[0] * 60) + (int)$parts[1];
                    }
                } elseif (str_ends_with($raw, 's')) {
                    $sessionTimeoutMin = (int) round((int) rtrim($raw, 's') / 60);
                } elseif (str_ends_with($raw, 'm')) {
                    $sessionTimeoutMin = (int) rtrim($raw, 'm');
                } elseif (str_ends_with($raw, 'h')) {
                    $sessionTimeoutMin = (int) rtrim($raw, 'h') * 60;
                }
            }

            $rateLimit = !empty($rp['rate-limit']) ? $rp['rate-limit'] : '5M/10M';
            $sharedUsers = !empty($rp['shared-users']) ? (int)$rp['shared-users'] : 1;

            HotspotProfile::updateOrCreate(
                ['location_id' => $location->id, 'name' => $name],
                [
                    'display_name'      => $name,
                    'rate_limit'        => $rateLimit,
                    'shared_users'      => $sharedUsers,
                    'session_timeout'   => $sessionTimeoutMin,
                    'idle_timeout'      => 15,
                    'keepalive_timeout' => 5,
                    'synced_to_router'  => true,
                    'router_profile_id' => $rp['.id'] ?? null,
                ]
            );

            // Mirror into tenant DB
            try {
                TenantManager::switchConnection($location);
                \App\Models\Tenant\HotspotProfile::updateOrCreate(
                    ['name' => $name],
                    [
                        'rate_limit'   => $rateLimit,
                        'shared_users' => $sharedUsers,
                        'uptime_limit' => $sessionTimeoutMin * 60,
                        'description'  => $name,
                    ]
                );
            } catch (Throwable $e) {
                // Ignore
            }

            $imported++;
        }

        return redirect()->back()->with('success', "Berhasil mengimpor {$imported} profil hotspot dari MikroTik RouterOS.");
    }
}
