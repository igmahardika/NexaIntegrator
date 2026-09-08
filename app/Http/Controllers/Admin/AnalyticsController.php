<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\PortalSession;
use App\Models\SurveyCampaign;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Advertisers can only see their own campaigns
        $campaignQuery = SurveyCampaign::query();
        if ($user->isAdvertiser()) {
            $campaignQuery->where('advertiser_id', $user->id);
        }

        $campaigns = $campaignQuery->withCount('responses')->get();

        $selectedCampaign = null;
        $questionStats    = [];

        if ($request->filled('campaign_id')) {
            $selectedCampaign = $campaignQuery->clone()
                ->where('id', $request->campaign_id)
                ->firstOrFail();

            $questionStats = $this->aggregateCampaignResults($selectedCampaign);
        }

        // Session stats by location
        $locationStats = Location::withCount([
            'sessions',
            'sessions as today_sessions_count' => fn($q) => $q->whereDate('login_time', today()),
        ])->get();

        // CTR / Impression data
        $impressionData = SurveyResponse::select(
                DB::raw("DATE(created_at) as date"),
                DB::raw("count(*) as impressions")
            )
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.analytics.index', compact(
            'campaigns', 'selectedCampaign', 'questionStats',
            'locationStats', 'impressionData'
        ));
    }

    /**
     * Stream CSV export of survey responses.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = Auth::user();

        $campaignQuery = SurveyCampaign::query();
        if ($user->isAdvertiser()) {
            $campaignQuery->where('advertiser_id', $user->id);
        }

        if ($request->filled('campaign_id')) {
            $campaignQuery->where('id', $request->campaign_id);
        }

        $campaigns = $campaignQuery->with('questions')->get();
        $campaignIds = $campaigns->pluck('id');

        $filename = 'wifipads-analytics-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($campaignIds, $campaigns) {
            $handle = fopen('php://output', 'w');

            // Escape formula characters (=, +, -, @, \t, \r) to prevent spreadsheet formula injection
            $escapeFormula = function ($value) {
                if (is_null($value)) return '';
                $str = (string) $value;
                if (preg_match('/^[=\+\-@\t\r]/', $str)) {
                    return "'" . $str;
                }
                return $str;
            };

            // Headers
            fputcsv($handle, [
                'ID', 'Campaign', 'Location', 'MAC Address', 'IP Address',
                'Method', 'Tanggal', 'Jawaban'
            ]);

            SurveyResponse::whereIn('campaign_id', $campaignIds)
                ->with(['campaign', 'location'])
                ->orderBy('created_at')
                ->chunk(200, function ($responses) use ($handle, $escapeFormula) {
                    foreach ($responses as $r) {
                        fputcsv($handle, [
                            $r->id,
                            $escapeFormula($r->campaign?->title ?? '-'),
                            $escapeFormula($r->location?->name ?? '-'),
                            $escapeFormula($r->client_mac),
                            $escapeFormula($r->client_ip),
                            'survey',
                            $r->created_at?->format('Y-m-d H:i:s'),
                            $escapeFormula(json_encode($r->answers ?? [], JSON_UNESCAPED_UNICODE)),
                        ]);
                    }
                });

            // Also export portal sessions
            fputcsv($handle, []);
            fputcsv($handle, ['--- SESSION LOG ---']);
            fputcsv($handle, ['ID', 'Lokasi', 'MAC', 'IP', 'Method', 'Login', 'Logout', 'Status', 'Bytes In', 'Bytes Out']);

            \App\Models\PortalSession::with('location')
                ->orderBy('login_time')
                ->chunk(200, function ($sessions) use ($handle, $escapeFormula) {
                    foreach ($sessions as $s) {
                        fputcsv($handle, [
                            $s->id,
                            $escapeFormula($s->location?->name ?? '-'),
                            $escapeFormula($s->client_mac),
                            $escapeFormula($s->client_ip),
                            $escapeFormula($s->method),
                            $s->login_time?->format('Y-m-d H:i:s'),
                            $s->logout_time?->format('Y-m-d H:i:s') ?? '-',
                            $escapeFormula($s->status),
                            $s->bytes_in,
                            $s->bytes_out,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Aggregate answer statistics per question for a campaign.
     */
    private function aggregateCampaignResults(SurveyCampaign $campaign): array
    {
        $questions = $campaign->questions()->orderBy('order')->get();
        $responses = $campaign->responses()->get();

        $stats = [];

        foreach ($questions as $question) {
            $qId     = $question->id;
            $counts  = [];
            $texts   = [];

            foreach ($responses as $response) {
                $answers = $response->answers ?? [];
                $answer  = $answers[$qId] ?? null;

                if ($answer === null) continue;

                if (is_array($answer)) {
                    foreach ($answer as $a) {
                        $counts[$a] = ($counts[$a] ?? 0) + 1;
                    }
                } elseif ($question->isText()) {
                    $texts[] = $answer;
                } else {
                    $counts[(string) $answer] = ($counts[(string) $answer] ?? 0) + 1;
                }
            }

            arsort($counts);

            $stats[] = [
                'question' => $question,
                'counts'   => $counts,
                'texts'    => $texts,
                'total'    => $responses->count(),
            ];
        }

        return $stats;
    }

    // ============================================================
    // Lawful Interception & Forensic Sesi (Monitor Duration Login)
    // ============================================================

    /**
     * Lawful Interception & Session Forensic Audit Hub (Legacy: Monitor Duration Login).
     */
    public function durationLogs(Request $request)
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        $query = PortalSession::with('location')->latest('login_time');

        // Location filter
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // Method filter (pms, voucher, member, whatsapp, survey, email, quick)
        if ($request->filled('method') && $request->method !== 'all') {
            $query->where('method', $request->method);
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('login_time', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('login_time', '<=', $request->date_to);
        }

        // Search: IP, MAC, Identifier (Room/Email/Phone/Username)
        if ($request->filled('search')) {
            $search = strtoupper(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->where('client_mac', 'like', "%{$search}%")
                  ->orWhere('client_ip', 'like', "%{$search}%")
                  ->orWhere('identifier', 'like', "%{$search}%");
            });
        }

        // Clone query for aggregate statistics
        $statsQuery = clone $query;
        $totalSessions = $statsQuery->count();
        $totalBytes = $statsQuery->sum(DB::raw('bytes_in + bytes_out'));
        $activeSessions = (clone $statsQuery)->where('status', 'active')->count();

        $sessions = $query->paginate(30)->withQueryString();

        return view('admin.analytics.duration-log', compact(
            'sessions', 'locations', 'totalSessions', 'totalBytes', 'activeSessions'
        ));
    }

    /**
     * Export Lawful Interception Session Logs to CSV for legal/forensic compliance.
     */
    public function exportDurationLogsCsv(Request $request): StreamedResponse
    {
        $query = PortalSession::with('location')->latest('login_time');

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        if ($request->filled('method') && $request->method !== 'all') {
            $query->where('method', $request->method);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('login_time', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('login_time', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = strtoupper(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->where('client_mac', 'like', "%{$search}%")
                  ->orWhere('client_ip', 'like', "%{$search}%")
                  ->orWhere('identifier', 'like', "%{$search}%");
            });
        }

        $filename = 'lawful-interception-sessions-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Prevent CSV injection
            $escape = function ($val) {
                if (is_null($val)) return '';
                $str = (string) $val;
                if (preg_match('/^[=\+\-@\t\r]/', $str)) {
                    return "'" . $str;
                }
                return $str;
            };

            // Lawful Interception Header Specification
            fputcsv($handle, [
                'No',
                'ID Sesi',
                'Site / Cabang Properti',
                'IP Lokal Klien',
                'MAC Address Fisik',
                'Identitas Pengguna (Kamar / Email / No HP)',
                'Metode Login',
                'Waktu Start (Login)',
                'Waktu Stop (Logout)',
                'Durasi Sesi',
                'Status Sesi',
                'Download (Bytes In)',
                'Upload (Bytes Out)',
                'Total Bandwidth (Bytes)',
                'Sistem Operasi (OS)',
                'Browser',
                'User Agent Header',
            ]);

            $index = 1;
            $query->chunk(200, function ($rows) use ($handle, $escape, &$index) {
                foreach ($rows as $session) {
                    $end = $session->logout_time ?? ($session->status === 'active' ? now() : null);
                    $duration = $session->login_time && $end ? $session->login_time->diffForHumans($end, true) : '—';

                    fputcsv($handle, [
                        $index++,
                        $escape($session->id),
                        $escape($session->location?->name ?? 'Global Site'),
                        $escape($session->client_ip),
                        $escape($session->client_mac),
                        $escape($session->identifier),
                        $escape(strtoupper($session->method)),
                        $session->login_time ? $session->login_time->format('Y-m-d H:i:s') : '—',
                        $session->logout_time ? $session->logout_time->format('Y-m-d H:i:s') : 'Aktif',
                        $duration,
                        $escape(strtoupper($session->status)),
                        $session->bytes_in,
                        $session->bytes_out,
                        $session->bytes_in + $session->bytes_out,
                        $escape($session->device_os ?: 'Unknown'),
                        $escape($session->browser ?: 'Unknown'),
                        $escape($session->user_agent),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
