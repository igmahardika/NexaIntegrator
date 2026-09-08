<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\PortalSession;
use App\Models\PortalVoucher;
use App\Models\SurveyResponse;
use App\Services\MikrotikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        // Check active tenant context
        $activeSiteId = session('active_site_id');
        $siteScope = $activeSiteId ? Location::find($activeSiteId) : null;

        // ---- KPI Cards (scoped) ----
        $sessionsQuery = PortalSession::query();
        $impressionsQuery = SurveyResponse::query();
        $vouchersQuery = PortalVoucher::query();

        if ($siteScope) {
            $sessionsQuery->where('location_id', $siteScope->id);
            $impressionsQuery->where('location_id', $siteScope->id);
            $vouchersQuery->where('location_id', $siteScope->id);
        }

        $todaySessions    = (clone $sessionsQuery)->whereDate('login_time', today())->count();
        $todayImpressions = (clone $impressionsQuery)->whereDate('created_at', today())->count();
        $vouchersUsed     = (clone $vouchersQuery)->where('is_used', true)->whereDate('used_at', today())->count();
        $activeSessions   = (clone $sessionsQuery)->where('status', 'active')->count();

        // ---- 7-day connection trend (scoped - single aggregated query) ----
        $startDate = now()->subDays(6)->startOfDay();
        $dailyCounts = (clone $sessionsQuery)
            ->where('login_time', '>=', $startDate)
            ->select(DB::raw('DATE(login_time) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        $trendDays = collect(range(6, 0))->map(function ($daysAgo) use ($dailyCounts) {
            $dt = now()->subDays($daysAgo);
            $dateKey = $dt->toDateString();
            return [
                'date'  => $dt->format('d M'),
                'count' => (int) ($dailyCounts[$dateKey] ?? 0),
            ];
        });

        // ---- Login method composition (scoped) ----
        $methodCounts = (clone $sessionsQuery)->select('method', DB::raw('count(*) as total'))
            ->whereDate('login_time', '>=', now()->subDays(30))
            ->groupBy('method')
            ->pluck('total', 'method');

        // ---- Active users & Hardware Telemetry from Router ----
        $routerLocation = $siteScope ?: Location::where('is_active', true)->whereNotNull('router_ip')->first();
        $activeUsers = [];
        $routerError = null;
        $hardwareTelemetry = null;

        if ($routerLocation) {
            $mikrotik = new MikrotikService($routerLocation);

            try {
                $activeUsers = $mikrotik->getActiveUsers();
            } catch (\Throwable $e) {
                $routerError = 'Router API offline: ' . $e->getMessage();
            }

            $telemetry = $mikrotik->getSystemResources();
            if ($telemetry['online']) {
                $hardwareTelemetry = $telemetry;
            } else {
                // Standby / simulated telemetry for preview/onboarding
                $hardwareTelemetry = [
                    'online'            => false,
                    'simulated'         => true,
                    'cpu_load'          => 8,
                    'cpu_count'         => 4,
                    'cpu_frequency'     => '880 MHz (MT7621A)',
                    'uptime'            => '18d 04:12:08',
                    'version'           => 'RouterOS v7.14.3 (stable)',
                    'board_name'        => 'RB750Gr3 (hEX)',
                    'architecture'      => 'MMIPS',
                    'bad_blocks'        => '0.0%',
                    'memory_total'      => 268435456,
                    'memory_used'       => 72876032,
                    'memory_percent'    => 27.1,
                    'hdd_total'         => 16777216,
                    'hdd_used'          => 8388608,
                    'hdd_percent'       => 50.0,
                ];
            }
        }

        // ---- Demographic data from answers (age + gender - cached) ----
        $demographics = $this->extractDemographics($siteScope);
        $ageData    = $demographics['age'];
        $genderData = $demographics['gender'];

        return view('admin.dashboard.index', compact(
            'todaySessions', 'todayImpressions', 'vouchersUsed', 'activeSessions',
            'trendDays', 'methodCounts',
            'activeUsers', 'routerError', 'routerLocation', 'hardwareTelemetry',
            'ageData', 'genderData', 'siteScope'
        ));
    }

    /**
     * AJAX: Kick a user from the router.
     */
    public function kickUser(Request $request): JsonResponse
    {
        $request->validate([
            'mac'         => 'required|string',
            'location_id' => 'required|string|exists:locations,id',
        ]);

        $location = Location::findOrFail($request->location_id);
        $mikrotik = new MikrotikService($location);
        $result   = $mikrotik->kickUser($request->mac);

        // Update session status scoped to this location
        PortalSession::where('client_mac', strtoupper($request->mac))
            ->where('location_id', $location->id)
            ->where('status', 'active')
            ->update(['status' => 'disconnected', 'logout_time' => now()]);

        return response()->json($result);
    }

    /**
     * AJAX: Get fresh active users list.
     */
    public function getActiveUsers(Request $request): JsonResponse
    {
        $locationId = $request->query('location_id');
        $location   = $locationId ? Location::find($locationId) : Location::where('is_active', true)
            ->whereNotNull('router_ip')->first();

        if (!$location) {
            return response()->json(['users' => [], 'error' => 'No router configured']);
        }

        try {
            $mikrotik = new MikrotikService($location);
            $users    = $mikrotik->getActiveUsers();
            return response()->json(['users' => $users, 'location' => $location->name]);
        } catch (\Throwable $e) {
            return response()->json(['users' => [], 'error' => $e->getMessage()]);
        }
    }

    private function extractDemographics(?Location $siteScope): array
    {
        $cacheKey = 'dashboard_demographics_' . ($siteScope ? $siteScope->id : 'all');

        return cache()->remember($cacheKey, 600, function () use ($siteScope) {
            $query = SurveyResponse::whereNotNull('answers')
                ->whereDate('created_at', '>=', now()->subDays(30));

            if ($siteScope) {
                $query->where('location_id', $siteScope->id);
            }

            $responses = $query->select('answers')->get();

            $ageCounts = [];
            $genderCounts = [];

            foreach ($responses as $response) {
                $answers = $response->answers ?? [];
                foreach ($answers as $qId => $answer) {
                    $qIdLower = strtolower((string) $qId);

                    // Age extraction
                    if (str_contains($qIdLower, 'age') || (is_string($answer) && $this->looksLike($answer, 'age'))) {
                        $val = is_array($answer) ? implode(', ', $answer) : (string) $answer;
                        $ageCounts[$val] = ($ageCounts[$val] ?? 0) + 1;
                    }

                    // Gender extraction
                    if (str_contains($qIdLower, 'gender') || (is_string($answer) && $this->looksLike($answer, 'gender'))) {
                        $val = is_array($answer) ? implode(', ', $answer) : (string) $answer;
                        $genderCounts[$val] = ($genderCounts[$val] ?? 0) + 1;
                    }
                }
            }

            arsort($ageCounts);
            arsort($genderCounts);

            return [
                'age'    => array_slice($ageCounts, 0, 10, true),
                'gender' => array_slice($genderCounts, 0, 10, true),
            ];
        });
    }

    private function looksLike(string $answer, string $type): bool
    {
        $agePatterns    = ['<18', '18-24', '25-34', '35-44', '45-54', '55+'];
        $genderPatterns = ['Laki-laki', 'Perempuan', 'Male', 'Female', 'Pria', 'Wanita'];

        $patterns = $type === 'age' ? $agePatterns : $genderPatterns;
        return in_array($answer, $patterns, true);
    }
}
