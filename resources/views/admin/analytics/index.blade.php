@extends('layouts.admin')
@section('title', 'Analytics & Reports')
@section('page-title', 'Analytics & Reports')
@section('page-subtitle', 'Sponsor campaign insights, visitor survey feedback, and captive portal traffic metrics')

@section('header-actions')
<a href="{{ route('admin.analytics.export', ['campaign_id' => request('campaign_id')]) }}" class="btn-primary flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
    </svg>
    <span>Export CSV Report</span>
</a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Filter & Campaign Selector Bar -->
    <div class="card p-4 bg-white border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.analytics.index') }}" class="flex flex-wrap items-center gap-3">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Campaign Filter:</label>
            <select name="campaign_id" onchange="this.form.submit()" class="input bg-white border-slate-200 text-slate-800 text-xs py-2 px-3 w-auto min-w-[280px] rounded-lg">
                <option value="">-- All Campaigns (Global Overview) --</option>
                @foreach($campaigns as $c)
                <option value="{{ $c->id }}" {{ request('campaign_id') == $c->id ? 'selected' : '' }}>
                    {{ $c->title }} ({{ $c->responses_count }} responses)
                </option>
                @endforeach
            </select>
            @if(request('campaign_id'))
            <a href="{{ route('admin.analytics.index') }}" class="btn-secondary text-xs py-2 px-3 font-semibold">
                Reset Filter
            </a>
            @endif
        </form>

        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Real-time Telemetry Active</span>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Total Impressions (30 Days) -->
        <div class="kpi-card bg-white border border-slate-200/80 shadow-xs group">
            <div class="flex-1">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Total Ad Impressions</span>
                <div class="text-2xl font-black text-slate-900 mb-1 tracking-tight">
                    {{ number_format($impressionData->sum('impressions')) }}
                </div>
                <div class="text-xs text-slate-500 font-medium">Past 30 days cumulative</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-50 border border-brand-100 text-brand flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </div>
        </div>

        <!-- Metric 2: Completed Surveys -->
        <div class="kpi-card bg-white border border-slate-200/80 shadow-xs group">
            <div class="flex-1">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Completed Surveys</span>
                <div class="text-2xl font-black text-emerald-600 mb-1 tracking-tight">
                    @if($selectedCampaign)
                        {{ number_format($selectedCampaign->responses()->count()) }}
                    @else
                        {{ number_format($campaigns->sum('responses_count')) }}
                    @endif
                </div>
                <div class="text-xs text-slate-500 font-medium">
                    {{ $selectedCampaign ? 'Selected campaign responses' : 'All campaigns total' }}
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>

        <!-- Metric 3: Active Locations -->
        <div class="kpi-card bg-white border border-slate-200/80 shadow-xs group">
            <div class="flex-1">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Active Sites & Nodes</span>
                <div class="text-2xl font-black text-purple-700 mb-1 tracking-tight">
                    {{ number_format($locationStats->count()) }}
                </div>
                <div class="text-xs text-slate-500 font-medium">Configured edge gateways</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>

        <!-- Metric 4: Today's Sessions -->
        <div class="kpi-card bg-white border border-slate-200/80 shadow-xs group">
            <div class="flex-1">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Today's WiFi Sessions</span>
                <div class="text-2xl font-black text-brand mb-1 tracking-tight">
                    {{ number_format($locationStats->sum('today_sessions_count')) }}
                </div>
                <div class="text-xs text-slate-500 font-medium">Total authentications today</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-50 border border-brand-100 text-brand flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Charts Section: Impressions Trend & Location Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Line Chart: 30-Day Impressions -->
        <div class="card p-6 bg-white border border-slate-200/80 shadow-xs lg:col-span-2 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">30-Day Ad Impressions & Engagement</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daily volume of visitors viewing interstitial videos and engaging with portals</p>
                </div>
                <span class="badge bg-brand-50 text-brand border border-brand-100 text-2xs">Daily Trend</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="impressionChart"></canvas>
            </div>
        </div>

        <!-- Bar Chart: Sessions by Location -->
        <div class="card p-6 bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Traffic Volume by Site</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Cumulative guest logins per edge site</p>
                </div>
                <span class="badge bg-purple-50 text-purple-700 border border-purple-100 text-[10px]">Node Ranking</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="locationChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Dynamic Campaign Survey Analysis -->
    @if($selectedCampaign)
    <div class="space-y-4">
        <div class="card p-5 bg-gradient-to-r from-brand-50/70 via-white to-indigo-50/50 border border-brand-100 shadow-xs">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="badge bg-brand-50 text-brand border border-brand-200">Active Campaign</span>
                        <h2 class="text-lg font-extrabold text-slate-900">{{ $selectedCampaign->title }}</h2>
                    </div>
                    <p class="text-xs text-slate-600 font-medium">
                        Sponsor: <strong class="text-slate-900">{{ $selectedCampaign->sponsor_name }}</strong> •
                        Schedule: <span class="text-slate-700">{{ $selectedCampaign->start_date?->format('d M Y') ?? 'Immediate' }} to {{ $selectedCampaign->end_date?->format('d M Y') ?? 'Ongoing' }}</span> •
                        Min. Watch Duration: <span class="text-slate-700">{{ $selectedCampaign->min_watch_duration }}s</span>
                    </p>
                </div>
                <div class="text-left md:text-right">
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Completed Responses</div>
                    <div class="text-2xl font-black text-brand">{{ number_format(count($questionStats) > 0 ? $questionStats[0]['total'] : 0) }}</div>
                </div>
            </div>
        </div>

        @if(empty($questionStats))
        <div class="card p-12 text-center bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h4 class="font-bold text-slate-900 text-sm mb-1">No Survey Questions Configured</h4>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">This campaign is running video interstitial only without questionnaire surveys.</p>
        </div>
        @else
        <!-- Questions Breakdown Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($questionStats as $stat)
            @php
                $q = $stat['question'];
                $totalResponses = max(1, $stat['total']);
            @endphp
            <div class="card p-5 bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3 mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <div class="text-2xs font-bold text-brand uppercase tracking-wider mb-1">Question #{{ $loop->iteration }} • {{ strtoupper(str_replace('_', ' ', $q->question_type)) }}</div>
                        <h4 class="font-bold text-slate-900 text-sm leading-snug">{{ $q->question_text }}</h4>
                    </div>
                    @if($q->is_required)
                    <span class="badge bg-amber-50 text-amber-800 border border-amber-200 text-2xs">Required</span>
                    @endif
                </div>

                <!-- Responses Breakdown for Choice / Rating -->
                @if(!$q->isText())
                <div class="space-y-3">
                    @forelse($stat['counts'] as $opt => $count)
                    @php
                        $pct = round(($count / $totalResponses) * 100, 1);
                    @endphp
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-700 font-semibold truncate max-w-[240px]">
                                @if($q->isRating())
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-amber-500 fill-amber-500" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <span>Rating {{ $opt }}</span>
                                    </span>
                                @else
                                    {{ $opt }}
                                @endif
                            </span>
                            <span class="text-slate-500 font-mono text-2xs font-bold">
                                {{ $count }} <span class="text-slate-500 font-normal">({{ $pct }}%)</span>
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div
                                class="h-2 rounded-full transition-all duration-500 {{ $loop->first ? 'bg-brand' : 'bg-slate-400' }}"
                                style="width: {{ $pct }}%"
                            ></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-xs text-slate-500">No responses recorded yet</div>
                    @endforelse
                </div>

                <!-- Responses Breakdown for Text Feedback -->
                @else
                <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                    @forelse(array_slice($stat['texts'], 0, 10) as $tIndex => $textAnswer)
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-xs text-slate-700 leading-relaxed flex items-start gap-2">
                        <span class="text-slate-500 flex-shrink-0 font-mono text-2xs">#{{ $tIndex + 1 }}</span>
                        <div class="flex-1 break-words">"{{ $textAnswer }}"</div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-xs text-slate-500">No text comments submitted yet</div>
                    @endforelse

                    @if(count($stat['texts']) > 10)
                    <p class="text-2xs text-slate-500 text-center pt-2 italic">
                        Displaying 10 of {{ count($stat['texts']) }} written responses. Export CSV for full data.
                    </p>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @else
    <!-- Prompt to select campaign -->
    <div class="card p-6 text-center bg-white border border-dashed border-slate-200/80 shadow-xs">
        <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <h4 class="font-bold text-slate-900 text-sm mb-1">Detailed Survey & Questionnaire Breakdown</h4>
        <p class="text-xs text-slate-500 max-w-md mx-auto">
            Select an active campaign from the dropdown filter at the top of this page to inspect individual respondent answers and rating distributions.
        </p>
    </div>
    @endif

    <!-- Location Table Overview -->
    <div class="card bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider">Session Overview by Site</h3>
                <p class="text-xs text-slate-500">Connection throughput and session count per registered edge gateway</p>
            </div>
            <a href="{{ route('admin.sites.index') }}" class="btn-secondary text-xs py-1.5 px-3">
                Manage Sites
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Site Name</th>
                        <th class="py-3 px-4">Router Host IP</th>
                        <th class="py-3 px-4">Gateway Status</th>
                        <th class="py-3 px-4">Today's Sessions</th>
                        <th class="py-3 px-4">Total Cumulative Sessions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($locationStats as $loc)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $loc->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $loc->address ?? 'No physical address provided' }}</div>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-600">
                            {{ $loc->router_ip ?? '192.168.88.1' }}:{{ $loc->router_port ?? 8728 }}
                        </td>
                        <td class="py-3 px-4">
                            @if($loc->is_active)
                            <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200">Live Active</span>
                            @else
                            <span class="badge bg-slate-100 text-slate-500">Disabled</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-bold text-emerald-600 font-mono">
                            {{ number_format($loc->today_sessions_count) }}
                        </td>
                        <td class="py-3 px-4 font-extrabold text-slate-900 font-mono">
                            {{ number_format($loc->sessions_count) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">No edge sites configured yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Impression 30-Day Line Chart
    const impressionDataRaw = @js($impressionData);
    const impressionLabels = impressionDataRaw.map(item => item.date);
    const impressionCounts = impressionDataRaw.map(item => parseInt(item.impressions) || 0);

    const impCtx = document.getElementById('impressionChart');
    if (impCtx) {
        const imp2d = impCtx.getContext('2d');
        const impGrad = imp2d.createLinearGradient(0, 0, 0, 240);
        impGrad.addColorStop(0, 'rgba(34, 68, 158, 0.25)');
        impGrad.addColorStop(1, 'rgba(34, 68, 158, 0.0)');

        new Chart(impCtx, {
            type: 'line',
            data: {
                labels: impressionLabels.length ? impressionLabels : ['Today'],
                datasets: [{
                    label: 'Ad Impressions',
                    data: impressionCounts.length ? impressionCounts : [0],
                    borderColor: '#22449E',
                    backgroundColor: impGrad,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#22449E',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1.5,
                    pointRadius: 3.5,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        borderColor: '#334155',
                        borderWidth: 1,
                        titleColor: '#FFFFFF',
                        bodyColor: '#E2E8F0',
                        cornerRadius: 8,
                        padding: 10,
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#F1F5F9' },
                        ticks: { color: '#64748B', font: { size: 10, family: 'Inter, sans-serif' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: { color: '#64748B', font: { size: 10, family: 'Inter, sans-serif' }, precision: 0 }
                    }
                }
            }
        });
    }

    // 2. Location Sessions Bar Chart
    const locationDataRaw = @js($locationStats);
    const locationLabels = locationDataRaw.map(item => item.name);
    const locationSessions = locationDataRaw.map(item => item.sessions_count || 0);

    const locCtx = document.getElementById('locationChart');
    if (locCtx) {
        new Chart(locCtx, {
            type: 'bar',
            data: {
                labels: locationLabels.length ? locationLabels : ['No Data'],
                datasets: [{
                    label: 'Total Sessions',
                    data: locationSessions.length ? locationSessions : [0],
                    backgroundColor: '#7C3AED',
                    hoverBackgroundColor: '#6D28D9',
                    borderRadius: 6,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        borderColor: '#334155',
                        borderWidth: 1,
                        titleColor: '#FFFFFF',
                        bodyColor: '#E2E8F0',
                        cornerRadius: 8,
                        padding: 10,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748B', font: { size: 10, family: 'Inter, sans-serif' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: { color: '#64748B', font: { size: 10, family: 'Inter, sans-serif' }, precision: 0 }
                    }
                }
            }
        });
    }
});
</script>
@endsection
