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
        $totalToday  = PortalSession::where('login_time', '>=', today()->startOfDay())->count();
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

        $mac = BlacklistedDevice::normalizeMac($request->mac);

        // Kick directly via RouterOS API
        try {
            $mikrotik = new \App\Services\MikrotikService($location);
            $mikrotik->kickUser($mac);
        } catch (\Throwable $e) {
            // Ignore if router is offline
        }

        $radiusService = new RadiusService($location);
        $result = $radiusService->sendDisconnect($mac);

        // Mark session as disconnected in database
        PortalSession::where('client_mac', $mac)
            ->where('status', 'active')
            ->update([
                'status'      => 'disconnected',
                'logout_time' => now(),
            ]);

        return redirect()->back()->with('success', "Koneksi perangkat {$mac} berhasil diputuskan.");
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

        $mac = BlacklistedDevice::normalizeMac($request->mac);

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
                try {
                    (new \App\Services\MikrotikService($location))->kickUser($mac);
                } catch (\Throwable $e) {}
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

        // ---- Summary Metrics (Consolidated Single Aggregation) ----
        $summary = (clone $sessionQuery)
            ->selectRaw('
                COUNT(*) as total_sessions,
                COALESCE(SUM(bytes_in), 0) as total_bytes_in,
                COALESCE(SUM(bytes_out), 0) as total_bytes_out,
                COUNT(DISTINCT client_mac) as total_devices
            ')
            ->first();

        $totalSessions = (int) ($summary->total_sessions ?? 0);
        $totalBytesIn  = (int) ($summary->total_bytes_in ?? 0);
        $totalBytesOut = (int) ($summary->total_bytes_out ?? 0);
        $totalBytes    = $totalBytesIn + $totalBytesOut;
        $totalDevices  = (int) ($summary->total_devices ?? 0);

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

        // ---- 4. 14-Day Traffic Trend (Consolidated Single Aggregated Query) ----
        $startDate = now()->subDays(13)->startOfDay();
        $endDate   = now()->endOfDay();

        $dailyStats = (clone $sessionQuery)
            ->where('login_time', '>=', $startDate)
            ->where('login_time', '<=', $endDate)
            ->selectRaw('
                DATE(login_time) as date_key,
                COALESCE(SUM(bytes_in), 0) as total_in,
                COALESCE(SUM(bytes_out), 0) as total_out,
                COUNT(*) as session_count
            ')
            ->groupBy(DB::raw('DATE(login_time)'))
            ->get()
            ->keyBy('date_key');

        $trendDays = collect(range(13, 0))->map(function ($daysAgo) use ($dailyStats) {
            $dt      = now()->subDays($daysAgo);
            $dateKey = $dt->toDateString();
            $stat    = $dailyStats->get($dateKey);

            $in  = $stat ? (int) $stat->total_in : 0;
            $out = $stat ? (int) $stat->total_out : 0;
            $cnt = $stat ? (int) $stat->session_count : 0;

            return [
                'label'       => $dt->format('d M'),
                'download_mb' => round($out / (1024 * 1024), 2),
                'upload_mb'   => round($in / (1024 * 1024), 2),
                'sessions'    => $cnt,
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
