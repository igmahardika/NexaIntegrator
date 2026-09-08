<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlacklistedDevice;
use App\Models\Location;
use App\Models\PortalSession;
use App\Services\RadiusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    /**
     * Connected Devices List (Active & Recent Sessions).
     */
    public function index(Request $request)
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        $query = PortalSession::with('location')->latest('login_time');

        // Status filter: default to active
        $status = $request->input('status', 'active');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Location filter
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // Device Brand filter
        if ($request->filled('brand')) {
            $query->where('device_brand', $request->brand);
        }

        // Device Type filter
        if ($request->filled('type')) {
            $query->where('device_type', $request->type);
        }

        // Search MAC or IP
        if ($request->filled('search')) {
            $search = strtoupper(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->where('client_mac', 'like', "%{$search}%")
                  ->orWhere('client_ip', 'like', "%{$search}%")
                  ->orWhere('identifier', 'like', "%{$search}%");
            });
        }

        $sessions = $query->paginate(25)->withQueryString();

        // Summary counts
        $totalActive = PortalSession::where('status', 'active')->count();
        $totalToday  = PortalSession::whereDate('login_time', today())->count();
        $totalBlacklisted = BlacklistedDevice::count();

        // Brands list for filter dropdown
        $brands = PortalSession::whereNotNull('device_brand')
            ->distinct()
            ->orderBy('device_brand')
            ->pluck('device_brand');

        $blacklisted = BlacklistedDevice::with('location')->latest()->take(10)->get();

        return view('admin.devices.index', compact(
            'sessions', 'locations', 'status', 'totalActive', 'totalToday',
            'totalBlacklisted', 'brands', 'blacklisted'
        ));
    }

    /**
     * Kick / Disconnect a device.
     */
    public function kick(Request $request)
    {
        $request->validate([
            'mac'         => 'required|string',
            'location_id' => 'required|string|exists:locations,id',
        ]);

        $location = Location::findOrFail($request->location_id);
        $radiusService = new RadiusService($location);
        $result = $radiusService->sendDisconnect($request->mac);

        // Mark session as disconnected in database
        PortalSession::where('client_mac', strtoupper($request->mac))
            ->where('status', 'active')
            ->update([
                'status'      => 'disconnected',
                'logout_time' => now(),
            ]);

        return redirect()->back()->with('success', "Koneksi perangkat {$request->mac} berhasil diputuskan.");
    }

    /**
     * Block / Blacklist a device by MAC.
     */
    public function block(Request $request)
    {
        $request->validate([
            'mac'         => 'required|string',
            'location_id' => 'nullable|string|exists:locations,id',
            'reason'      => 'nullable|string|max:255',
        ]);

        $mac = strtoupper(trim($request->mac));

        BlacklistedDevice::firstOrCreate(
            ['mac_address' => $mac, 'location_id' => $request->location_id],
            [
                'reason'     => $request->reason ?: 'Diblokir oleh administrator',
                'blocked_by' => auth()->user()?->name ?? 'Admin',
            ]
        );

        // Immediately kick if active
        if ($request->filled('location_id')) {
            $location = Location::find($request->location_id);
            if ($location) {
                (new RadiusService($location))->sendDisconnect($mac);
            }
        }

        PortalSession::where('client_mac', $mac)
            ->where('status', 'active')
            ->update(['status' => 'disconnected', 'logout_time' => now()]);

        return redirect()->back()->with('success', "MAC Address {$mac} berhasil diblokir.");
    }

    /**
     * Unblock / Remove from blacklist.
     */
    public function unblock(BlacklistedDevice $blacklistedDevice)
    {
        $mac = $blacklistedDevice->mac_address;
        $blacklistedDevice->delete();

        return redirect()->back()->with('success', "MAC Address {$mac} berhasil dibuka dari blokir.");
    }

    /**
     * Traffic & Device Intelligence Monitoring Dashboard.
     */
    public function monitoring(Request $request)
    {
        $locationId = $request->input('location_id');
        $locations  = Location::where('is_active', true)->orderBy('name')->get();

        $sessionQuery = PortalSession::query();
        if ($locationId) {
            $sessionQuery->where('location_id', $locationId);
        }

        // ---- Summary Metrics ----
        $totalSessions = (clone $sessionQuery)->count();
        $totalBytesIn  = (clone $sessionQuery)->sum('bytes_in');
        $totalBytesOut = (clone $sessionQuery)->sum('bytes_out');
        $totalBytes    = $totalBytesIn + $totalBytesOut;
        $totalDevices  = (clone $sessionQuery)->distinct('client_mac')->count('client_mac');

        // ---- 1. Device Brand Breakdown (Chart.js Doughnut) ----
        $brandStats = (clone $sessionQuery)
            ->select('device_brand', DB::raw('count(*) as count'))
            ->groupBy('device_brand')
            ->orderByDesc('count')
            ->take(8)
            ->get();

        // ---- 2. Device Category Breakdown (Mobile, Tablet, Desktop) ----
        $categoryStats = (clone $sessionQuery)
            ->select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();

        // ---- 3. Operating System Breakdown ----
        $osStats = (clone $sessionQuery)
            ->select('device_os', DB::raw('count(*) as count'))
            ->groupBy('device_os')
            ->orderByDesc('count')
            ->take(6)
            ->get();

        // ---- 4. 14-Day Traffic Trend (Download vs Upload in MB) ----
        $trendDays = collect(range(13, 0))->map(function ($daysAgo) use ($locationId) {
            $date = now()->subDays($daysAgo)->toDateString();
            $q = PortalSession::whereDate('login_time', $date);
            if ($locationId) $q->where('location_id', $locationId);

            $in  = $q->sum('bytes_in');
            $out = $q->sum('bytes_out');

            return [
                'label'        => now()->subDays($daysAgo)->format('d M'),
                'download_mb'  => round($out / (1024 * 1024), 2),
                'upload_mb'    => round($in / (1024 * 1024), 2),
                'sessions'     => $q->count(),
            ];
        });

        // ---- 5. Top 10 Bandwidth Consuming Devices ----
        $topDevices = (clone $sessionQuery)
            ->select(
                'client_mac', 'device_brand', 'device_model', 'device_os', 'method',
                DB::raw('SUM(bytes_in) as total_in'),
                DB::raw('SUM(bytes_out) as total_out'),
                DB::raw('SUM(bytes_in + bytes_out) as total_bytes'),
                DB::raw('COUNT(*) as total_visits')
            )
            ->groupBy('client_mac', 'device_brand', 'device_model', 'device_os', 'method')
            ->orderByDesc('total_bytes')
            ->take(10)
            ->get();

        // Format byte values
        $formattedTotalBytes = PortalSession::formatBytes($totalBytes);
        $formattedDownload   = PortalSession::formatBytes($totalBytesOut);
        $formattedUpload     = PortalSession::formatBytes($totalBytesIn);

        return view('admin.devices.monitoring', compact(
            'locations', 'locationId',
            'totalSessions', 'totalDevices',
            'formattedTotalBytes', 'formattedDownload', 'formattedUpload',
            'brandStats', 'categoryStats', 'osStats',
            'trendDays', 'topDevices'
        ));
    }
}
