@extends('layouts.admin')
@section('title', 'Traffic & Device Monitoring')
@section('page-title', 'Traffic Monitoring & Device Intelligence')
@section('page-subtitle', 'Bandwidth consumption telemetry, smartphone vendor market share, and visitor device trends')

@section('content')
<div class="space-y-6">

    <!-- Top KPI Metrics Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Total Bandwidth -->
        <div class="card p-5 bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Bandwidth Volume</span>
                <span class="p-1.5 rounded-lg bg-brand-50 text-brand">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight">{{ $formattedTotalBytes }}</div>
            <div class="text-xs text-slate-500 mt-2 flex items-center gap-3 pt-2 border-t border-slate-100 font-medium">
                <span class="text-sky-600 font-semibold flex items-center gap-1">⬇ {{ $formattedDownload }}</span>
                <span class="text-brand font-semibold flex items-center gap-1">⬆ {{ $formattedUpload }}</span>
            </div>
        </div>

        <!-- Metric 2: Unique Devices -->
        <div class="card p-5 bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unique Devices (MAC)</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600 tracking-tight">{{ number_format($totalDevices) }}</div>
            <div class="text-xs text-slate-500 mt-2 pt-2 border-t border-slate-100 font-medium">
                Identified hardware MAC addresses
            </div>
        </div>

        <!-- Metric 3: Total Sessions -->
        <div class="card p-5 bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Connected Sessions</span>
                <span class="p-1.5 rounded-lg bg-purple-50 text-purple-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-black text-purple-700 tracking-tight">{{ number_format($totalSessions) }}</div>
            <div class="text-xs text-slate-500 mt-2 pt-2 border-t border-slate-100 font-medium">
                Successful authentication records
            </div>
        </div>

        <!-- Metric 4: Top Brand -->
        <div class="card p-5 bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Leading Device Brand</span>
                <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 truncate tracking-tight">
                {{ $brandStats->first()?->device_brand ?? 'None Detected' }}
            </div>
            <div class="text-xs text-slate-500 mt-2 pt-2 border-t border-slate-100 font-medium">
                {{ number_format($brandStats->first()?->count ?? 0) }} devices recorded
            </div>
        </div>
    </div>

    <!-- Site Switcher Bar -->
    <div class="card p-4 bg-white border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="text-xs text-slate-600 font-medium">
            Filtering telemetry for:
            <strong class="text-slate-900 font-bold">{{ $locationId ? ($locations->find($locationId)?->name ?? 'Selected Site') : 'All Sites (Global NOC)' }}</strong>
        </div>

        <form method="GET" action="{{ route('admin.devices.monitoring') }}" class="flex items-center gap-2 w-full sm:w-auto">
            <select name="location_id" class="input text-xs py-1.5 px-3 w-full sm:w-64 bg-white border-slate-200 text-slate-800 rounded-lg" onchange="this.form.submit()">
                <option value="">All Sites (Global NOC)</option>
                @foreach($locations as $loc)
                <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }}>
                    📍 {{ $loc->name }}
                </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- 14-Day Traffic Trend Chart (Line Chart) -->
    <div class="card p-6 bg-white border border-slate-200/80 shadow-xs">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">14-Day Bandwidth Throughput Trend</h3>
                <p class="text-xs text-slate-500 mt-0.5">Comparative download (⬇) and upload (⬆) volume in Megabytes (MB)</p>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <span class="flex items-center gap-1.5 text-sky-600">
                    <span class="w-3 h-3 rounded-full bg-sky-500 shadow-xs"></span> Download (MB)
                </span>
                <span class="flex items-center gap-1.5 text-brand">
                    <span class="w-3 h-3 rounded-full bg-brand shadow-xs"></span> Upload (MB)
                </span>
            </div>
        </div>

        <div class="h-64 w-full">
            <canvas id="trafficTrendChart"></canvas>
        </div>
    </div>

    <!-- Charts Row: Brand Distribution & OS Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Brand Distribution Doughnut -->
        <div class="lg:col-span-6 card p-6 bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Device Brand Market Share</h3>
                    <span class="badge bg-brand-50 text-brand border border-brand-100 text-2xs">Vendor Telemetry</span>
                </div>
                <p class="text-xs text-slate-500 mb-4">Manufacturer distribution among connected visitor hardware</p>

                <div class="h-60 relative flex items-center justify-center">
                    <canvas id="brandChart"></canvas>
                </div>
            </div>

            <!-- Brand Legend Table -->
            <div class="grid grid-cols-2 gap-2 pt-4 border-t border-slate-100 text-xs">
                @foreach($brandStats as $b)
                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/60">
                    <span class="text-slate-800 font-semibold truncate">{{ $b->device_brand }}</span>
                    <span class="text-brand font-bold ml-2 font-mono">{{ $b->count }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Operating System & Category Bar -->
        <div class="lg:col-span-6 card p-6 bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Operating Systems & Form Factors</h3>
                    <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-100 text-[10px]">OS Breakdown</span>
                </div>
                <p class="text-xs text-slate-500 mb-4">Device platforms (Android, iOS, Windows, macOS) and categories</p>

                <div class="h-60">
                    <canvas id="osChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 pt-4 border-t border-slate-100 text-center text-xs">
                @foreach($categoryStats as $c)
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200/60">
                    <div class="text-slate-500 uppercase text-[10px] font-bold tracking-wider capitalize">{{ $c->device_type }}</div>
                    <div class="text-slate-900 font-black text-base mt-0.5">{{ number_format($c->count) }}</div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Top 10 Bandwidth Consuming Devices Leaderboard -->
    <div class="card bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Top 10 Bandwidth Consumers</h3>
                <p class="text-xs text-slate-500 mt-0.5">High-volume hardware MAC addresses ranked by cumulative data transfer</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="table-th">Rank</th>
                        <th class="table-th">MAC Address & Device</th>
                        <th class="table-th">Brand / Model</th>
                        <th class="table-th">Total Visits</th>
                        <th class="table-th">Download (⬇)</th>
                        <th class="table-th">Upload (⬆)</th>
                        <th class="table-th">Total Data Usage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($topDevices as $idx => $dev)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-3.5 font-bold">
                            @if($idx === 0)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-2xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                <svg class="w-3 h-3 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.003 0H9.497m5.003 0A6.75 6.75 0 0018 7.5V4.5a1.5 1.5 0 00-1.5-1.5h-9A1.5 1.5 0 006 4.5v3a6.75 6.75 0 003.497 5.875"/>
                                </svg>
                                <span>#1</span>
                            </span>
                            @elseif($idx === 1)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-2xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                <svg class="w-3 h-3 text-slate-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.003 0H9.497m5.003 0A6.75 6.75 0 0018 7.5V4.5a1.5 1.5 0 00-1.5-1.5h-9A1.5 1.5 0 006 4.5v3a6.75 6.75 0 003.497 5.875"/>
                                </svg>
                                <span>#2</span>
                            </span>
                            @elseif($idx === 2)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-2xs font-bold bg-amber-100/70 text-amber-900 border border-amber-300">
                                <svg class="w-3 h-3 text-amber-700 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.003 0H9.497m5.003 0A6.75 6.75 0 0018 7.5V4.5a1.5 1.5 0 00-1.5-1.5h-9A1.5 1.5 0 006 4.5v3a6.75 6.75 0 003.497 5.875"/>
                                </svg>
                                <span>#3</span>
                            </span>
                            @else
                            <span class="text-slate-500 font-mono text-xs">#{{ $idx + 1 }}</span>
                            @endif
                        </td>
                        <td class="p-3.5">
                            <div class="font-mono font-bold text-slate-900">{{ $dev->client_mac }}</div>
                            <div class="text-2xs text-slate-500">
                                First seen: {{ \Carbon\Carbon::parse($dev->first_seen)->format('d M Y') }}
                            </div>
                        </td>
                        <td class="p-3.5">
                            <div class="font-semibold text-slate-800">{{ $dev->device_brand }}</div>
                            <div class="text-2xs text-slate-500">{{ $dev->device_model }}</div>
                        </td>
                        <td class="p-3.5 font-bold text-slate-900">
                            {{ number_format($dev->total_visits) }}x
                        </td>
                        <td class="p-3.5 font-mono font-semibold text-sky-600">
                            {{ \App\Models\PortalSession::formatBytes($dev->total_out) }}
                        </td>
                        <td class="p-3.5 font-mono font-semibold text-brand">
                            {{ \App\Models\PortalSession::formatBytes($dev->total_in) }}
                        </td>
                        <td class="p-3.5 font-mono font-black text-emerald-600">
                            {{ \App\Models\PortalSession::formatBytes($dev->total_bytes) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">
                            No bandwidth consumption logs recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js Scripts (Styling strictly conforms to UI UX Pro Max standards) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. 14-Day Traffic Trend Line Chart
    const trendCtx = document.getElementById('trafficTrendChart');
    if (trendCtx) {
        const trendData = @json($trendDays);
        const labels = trendData.map(d => d.label);
        const downloads = trendData.map(d => d.download_mb);
        const uploads = trendData.map(d => d.upload_mb);

        const ctx = trendCtx.getContext('2d');
        const dlGrad = ctx.createLinearGradient(0, 0, 0, 240);
        dlGrad.addColorStop(0, 'rgba(2, 132, 199, 0.22)');
        dlGrad.addColorStop(1, 'rgba(2, 132, 199, 0.0)');

        const upGrad = ctx.createLinearGradient(0, 0, 0, 240);
        upGrad.addColorStop(0, 'rgba(34, 68, 158, 0.22)');
        upGrad.addColorStop(1, 'rgba(34, 68, 158, 0.0)');

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Download (MB)',
                        data: downloads,
                        borderColor: '#0284C7',
                        backgroundColor: dlGrad,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3.5,
                        pointBackgroundColor: '#0284C7',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'Upload (MB)',
                        data: uploads,
                        borderColor: '#22449E',
                        backgroundColor: upGrad,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3.5,
                        pointBackgroundColor: '#22449E',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleColor: '#FFFFFF',
                        bodyColor: '#E2E8F0',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#F1F5F9' },
                        ticks: { color: '#64748B', font: { size: 11, family: 'Inter, sans-serif' } }
                    },
                    y: {
                        grid: { color: '#F1F5F9' },
                        ticks: { color: '#64748B', font: { size: 11, family: 'Inter, sans-serif' }, callback: v => v + ' MB' }
                    }
                }
            }
        });
    }

    // 2. Brand Distribution Doughnut Chart (72% Cutout, White Ring Borders)
    const brandCtx = document.getElementById('brandChart');
    if (brandCtx) {
        const brands = @json($brandStats);
        const palette = ['#22449E', '#0284C7', '#10B981', '#F59E0B', '#7C3AED', '#EC4899', '#0EA5E9', '#64748B'];

        new Chart(brandCtx, {
            type: 'doughnut',
            data: {
                labels: brands.map(b => b.device_brand),
                datasets: [{
                    data: brands.map(b => b.count),
                    backgroundColor: palette.slice(0, brands.length),
                    borderColor: '#FFFFFF',
                    borderWidth: 2.5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom', 
                        labels: { 
                            color: '#475569', 
                            font: { size: 11, family: 'Inter, sans-serif', weight: 600 },
                            padding: 14,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        } 
                    },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleColor: '#FFFFFF',
                        bodyColor: '#E2E8F0',
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                cutout: '72%',
            }
        });
    }

    // 3. Operating System Horizontal Bar Chart
    const osCtx = document.getElementById('osChart');
    if (osCtx) {
        const osData = @json($osStats);
        new Chart(osCtx, {
            type: 'bar',
            data: {
                labels: osData.map(o => o.device_os),
                datasets: [{
                    label: 'Devices',
                    data: osData.map(o => o.count),
                    backgroundColor: ['#10B981', '#22449E', '#0284C7', '#7C3AED', '#64748B'],
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleColor: '#FFFFFF',
                        bodyColor: '#E2E8F0',
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748B', font: { size: 11, family: 'Inter, sans-serif' } }
                    },
                    y: {
                        grid: { color: '#F1F5F9' },
                        ticks: { color: '#64748B', font: { size: 11, family: 'Inter, sans-serif' } }
                    }
                }
            }
        });
    }
});
</script>
@endsection
