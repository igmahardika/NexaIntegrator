@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Overview')

@section('header-actions')
<a href="{{ route('admin.analytics.export') }}" class="btn-secondary text-xs flex items-center gap-1.5 py-1.5">
    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
    </svg>
    Export CSV
</a>
@endsection

@section('content')
<div class="space-y-5">

    <!-- ======================= 5 KPI METRICS ROW (SMARTIV DNA) ======================= -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        
        <!-- Metric 1: Occupancy -->
        <div class="kpi-card group">
            <div class="w-full">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 text-brand flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700">Occupancy</span>
                </div>
                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                    <div>
                        <div class="text-xs text-slate-500 font-semibold">Occupied</div>
                        <div class="text-base font-extrabold text-slate-900">{{ number_format($activeSessions) }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500 font-semibold">Ready</div>
                        <div class="text-base font-extrabold text-slate-900">{{ max(0, 50 - $activeSessions) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric 2: Device Status -->
        <div class="kpi-card group">
            <div class="w-full">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700">Device Status</span>
                </div>
                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                    <div>
                        <div class="text-xs text-slate-500 font-semibold">On</div>
                        <div class="text-base font-extrabold text-emerald-600">{{ number_format($activeSessions) }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500 font-semibold">Off</div>
                        <div class="text-base font-extrabold text-slate-500">{{ max(0, $todaySessions - $activeSessions) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric 3: Storage -->
        <div class="kpi-card group">
            <div class="w-full">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h4"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700">Storage & RAM</span>
                </div>
                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                    <div>
                        <div class="text-xs text-slate-500 font-semibold">Usage</div>
                        @if($hardwareTelemetry && !empty($hardwareTelemetry['online']))
                        <div class="text-xs font-extrabold text-slate-900 mt-0.5">{{ number_format(($hardwareTelemetry['memory_used'] ?? 0) / 1048576, 1) }} MB</div>
                        @else
                        <div class="text-xs font-extrabold text-slate-400 mt-0.5">-</div>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500 font-semibold">Capacity</div>
                        @if($hardwareTelemetry && !empty($hardwareTelemetry['online']))
                        <div class="text-xs font-extrabold text-slate-900 mt-0.5">{{ number_format(($hardwareTelemetry['memory_total'] ?? 0) / 1048576, 0) }} MB</div>
                        @else
                        <div class="text-xs font-extrabold text-slate-400 mt-0.5">-</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric 4: Staff & Sessions -->
        <div class="kpi-card group">
            <div class="w-full">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700">Staff & Users</span>
                </div>
                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                    <div>
                        <div class="text-xs text-slate-500 font-semibold">User</div>
                        <div class="text-base font-extrabold text-slate-900">{{ number_format($todaySessions) }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500 font-semibold">Role</div>
                        <div class="text-base font-extrabold text-slate-900">Admin</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric 5: Billing & Vouchers -->
        <div class="kpi-card group col-span-2 md:col-span-1">
            <div class="w-full">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700">Billing / Voucher</span>
                </div>
                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                    <div>
                        <div class="text-xs text-slate-500 font-semibold">Claimed</div>
                        <div class="text-base font-extrabold text-slate-900">{{ number_format($vouchersUsed) }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500 font-semibold">Overdue</div>
                        <div class="text-base font-extrabold text-slate-900">Rp 0</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ======================= HERO BANNER + ACTIVITY CHART ROW ======================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Left Promo Card (SMARTIV Hospitality DNA) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-brand-700 via-brand to-brand-900 rounded-2xl p-6 text-white relative overflow-hidden flex flex-col justify-between shadow-sm min-h-[260px]">
            <!-- Subtle Radial Highlights -->
            <div class="absolute -top-12 -right-12 w-48 h-48 bg-blue-400/20 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-indigo-950/60 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-6 h-6 rounded-md bg-white/20 flex items-center justify-center text-white backdrop-blur-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="2" y="5" width="20" height="14" rx="3" stroke="currentColor"/>
                            <path d="M7 19l2 2m8-2l-2 2m-6-2h6" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="text-xs font-extrabold tracking-wider uppercase text-blue-200">WIFIPADS CONTROLLER</span>
                </div>

                <div class="text-2xs font-bold tracking-widest text-blue-200/90 uppercase mt-2">Explore All New Features of</div>
                <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-0.5 leading-snug">
                    ENTERPRISE NETWORK ACCESS CONTROL
                </h2>
            </div>

            <div class="relative z-10 mt-6 pt-4 border-t border-white/15 flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-white">Download Our Business Overview</div>
                    <div class="text-xs text-blue-200/90">Edge gateway & captive portal documentation</div>
                </div>
                <a href="{{ route('admin.sites.index') }}" class="btn-accent text-xs font-bold py-2 px-3.5 shadow-md">
                    Explore Now
                </a>
            </div>
        </div>

        <!-- Right Activity Chart Card (Active TV / Connections) -->
        <div class="lg:col-span-7 card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Active Gateway Connections</h3>
                    <p class="text-xs text-slate-500">Daily gateway connection volume statistics</p>
                </div>
                <div class="flex items-center gap-2">
                    <select class="bg-slate-50 border border-slate-200 text-slate-600 text-xs rounded-lg px-2.5 py-1 focus:outline-none focus:border-brand font-medium">
                        <option>Last 7 Days</option>
                        <option>Last 14 Days</option>
                        <option>This Month</option>
                    </select>
                </div>
            </div>

            <div class="pt-3">
                <canvas id="trendChart" height="130"></canvas>
            </div>
        </div>

    </div>

    <!-- ======================= HARDWARE TELEMETRY & ROUTER HEALTH ======================= -->
    @if($hardwareTelemetry)
    <div class="card p-5 border border-slate-200/80 relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-50 border border-blue-100 rounded-xl text-brand">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Hardware Telemetry & Gateway Health</h3>
                        @if($hardwareTelemetry['online'])
                        <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 text-2xs px-2.5 py-0.5 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> RouterOS Live
                        </span>
                        @else
                        <span class="badge bg-brand/10 text-brand border border-brand/20 text-2xs px-2.5 py-0.5 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand"></span> Edge Standby
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5 font-mono">
                        Hardware: <span class="text-slate-800 font-semibold">{{ $hardwareTelemetry['board_name'] }}</span> • OS: <span class="text-brand font-semibold">{{ $hardwareTelemetry['version'] }}</span> ({{ $hardwareTelemetry['architecture'] }}) • Uptime: <span class="text-slate-700">{{ $hardwareTelemetry['uptime'] }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($routerLocation)
                <div class="text-left md:text-right">
                    <div class="text-xs font-bold text-slate-800">📍 {{ $routerLocation->name }}</div>
                    <div class="text-xs text-slate-500 font-mono">{{ $routerLocation->router_ip ?? '192.168.11.254' }}:{{ $routerLocation->router_port ?: 65000 }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Telemetry Gauges Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- CPU Load -->
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/60 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">CPU Load</span>
                    <span class="text-xs font-mono font-bold {{ $hardwareTelemetry['cpu_load'] > 80 ? 'text-rose-600' : ($hardwareTelemetry['cpu_load'] > 50 ? 'text-amber-600' : 'text-emerald-600') }}">
                        {{ $hardwareTelemetry['cpu_load'] }}%
                    </span>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-500 {{ $hardwareTelemetry['cpu_load'] > 80 ? 'bg-rose-500' : ($hardwareTelemetry['cpu_load'] > 50 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, max(5, $hardwareTelemetry['cpu_load'])) }}%"></div>
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-2 font-mono flex items-center justify-between">
                    <span>{{ $hardwareTelemetry['cpu_count'] }} Cores</span>
                    <span>{{ $hardwareTelemetry['cpu_frequency'] }}</span>
                </div>
            </div>

            <!-- RAM Usage -->
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/60 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">RAM Usage</span>
                    <span class="text-xs font-mono font-bold {{ $hardwareTelemetry['memory_percent'] > 85 ? 'text-rose-600' : 'text-brand' }}">
                        {{ $hardwareTelemetry['memory_percent'] }}%
                    </span>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full bg-brand transition-all duration-500" style="width: {{ min(100, max(5, $hardwareTelemetry['memory_percent'])) }}%"></div>
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-2 font-mono flex items-center justify-between">
                    <span>Used: {{ number_format($hardwareTelemetry['memory_used'] / 1048576, 1) }} MB</span>
                    <span>Total: {{ number_format($hardwareTelemetry['memory_total'] / 1048576, 0) }} MB</span>
                </div>
            </div>

            <!-- Disk Storage -->
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/60 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Flash Storage</span>
                    <span class="text-xs font-mono font-bold text-purple-600">
                        {{ $hardwareTelemetry['hdd_percent'] }}%
                    </span>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full bg-purple-600 transition-all duration-500" style="width: {{ min(100, max(5, $hardwareTelemetry['hdd_percent'])) }}%"></div>
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-2 font-mono flex items-center justify-between">
                    <span>Used: {{ number_format($hardwareTelemetry['hdd_used'] / 1048576, 1) }} MB</span>
                    <span>Total: {{ number_format($hardwareTelemetry['hdd_total'] / 1048576, 0) }} MB</span>
                </div>
            </div>

            <!-- Storage Health & Bad Blocks -->
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/60 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Storage Integrity</span>
                    <span class="badge {{ $hardwareTelemetry['bad_blocks'] === '0.0%' || $hardwareTelemetry['bad_blocks'] === '0%' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }} text-2xs px-2 py-0.5">
                        Bad Blocks: {{ $hardwareTelemetry['bad_blocks'] }}
                    </span>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-900">NAND Flash Healthy</div>
                        <div class="text-xs text-slate-500">Sektor memori normal</div>
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-1 font-mono">
                    AAA RADIUS: <span class="text-emerald-600 font-bold">RFC 2865 OK</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ======================= BOTTOM ROW: NOTIFICATIONS & ACTIVE USERS ======================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Left: Notifications Table (Image 3 DNA) -->
        <div class="lg:col-span-5 card p-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                <h3 class="text-sm font-extrabold text-slate-900">Notifications</h3>
                <span class="text-2xs font-bold text-slate-500 uppercase tracking-wider">System Logs</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="table-th">Device</th>
                            <th class="table-th">Date Time</th>
                            <th class="table-th">Content</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        <tr>
                            <td colspan="3" class="py-8 text-center text-slate-500 text-xs">
                                No data available
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Last Active Device / Hotspot Active Users (Image 3 DNA) -->
        <div class="lg:col-span-7 card p-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Last Active Devices</h3>
                    <p class="text-xs text-slate-500">Currently connected client devices on edge router</p>
                </div>
                <button onclick="refreshActiveUsers()" id="refresh-btn" class="btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" id="refresh-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>
            </div>

            @if($routerError)
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 text-xs text-rose-700 mb-3 font-semibold">
                ⚠ {{ $routerError }}
            </div>
            @endif

            <div id="active-users-container">
                @include('admin.dashboard._active_users_table', ['activeUsers' => $activeUsers, 'routerLocation' => $routerLocation])
            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
// ============================================================
// Chart.js configurations (Matching Image 3 Clean Vertical Bars)
// ============================================================
Chart.defaults.color = '#64748b';
Chart.defaults.borderColor = '#f1f5f9';

const trendCanvas = document.getElementById('trendChart');
if (trendCanvas) {
    new Chart(trendCanvas, {
        type: 'bar',
        data: {
            labels: {!! json_encode($trendDays->pluck('date')) !!},
            datasets: [{
                label: 'Connections',
                data: {!! json_encode($trendDays->pluck('count')) !!},
                backgroundColor: '#22449E', // Primary Royal Cobalt from SMARTIV DNA
                borderRadius: 4,
                barPercentage: 0.35,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0F172A',
                    titleColor: '#ffffff',
                    bodyColor: '#e2e8f0',
                    padding: 8,
                    cornerRadius: 8,
                }
            },
            scales: {
                x: { 
                    grid: { display: false }, 
                    ticks: { font: { size: 10, family: 'Inter' }, color: '#64748b' } 
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 10, family: 'Inter' }, color: '#64748b', stepSize: 1 }
                }
            }
        }
    });
}

// --- Refresh Active Users ---
const routerLocationId = '{{ $routerLocation?->id ?? "" }}';

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function renderActiveUsers(users, locationName) {
    const container = document.getElementById('active-users-container');
    if (!container) return;

    if (!Array.isArray(users) || users.length === 0) {
        container.innerHTML = '<p class="text-slate-500 text-xs text-center py-8">No active client sessions on edge router at this time.</p>';
        return;
    }

    let rowsHtml = '';
    users.forEach(function(user) {
        const userName = user.user ? user.user : 'Active Client';
        const mac = user.mac || '-';
        const ip = user.ip || '-';
        const uptime = user.uptime || '-';
        const rxMb = ((user.bytes_in || 0) / 1048576).toFixed(1);
        const txMb = ((user.bytes_out || 0) / 1048576).toFixed(1);

        const disconnectBtn = (routerLocationId && user.mac)
            ? `<button onclick="kickUser('${escapeHtml(user.mac)}', '${routerLocationId}')" class="btn-danger text-xs py-1 px-2.5 font-semibold">Disconnect</button>`
            : '';

        rowsHtml += `
            <tr class="table-row">
                <td class="py-2.5 px-3">
                    <div class="flex items-center gap-1.5 font-bold text-slate-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>${escapeHtml(userName)}</span>
                    </div>
                </td>
                <td class="py-2.5 px-3 font-mono text-slate-600 font-medium">${escapeHtml(mac)}</td>
                <td class="py-2.5 px-3 font-mono text-slate-600">${escapeHtml(ip)}</td>
                <td class="py-2.5 px-3 text-slate-500 font-medium">${escapeHtml(uptime)}</td>
                <td class="py-2.5 px-3 text-slate-500 font-mono">${rxMb}MB / ${txMb}MB</td>
                <td class="py-2.5 px-3 text-right">${disconnectBtn}</td>
            </tr>
        `;
    });

    container.innerHTML = `
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-2xs font-bold uppercase tracking-wider">
                        <th class="text-left py-2 px-3 rounded-l-lg">Device / User</th>
                        <th class="text-left py-2 px-3">MAC Address</th>
                        <th class="text-left py-2 px-3">IP Subnet</th>
                        <th class="text-left py-2 px-3">Up Time</th>
                        <th class="text-left py-2 px-3">Traffic (Rx/Tx)</th>
                        <th class="py-2 px-3 rounded-r-lg text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    ${rowsHtml}
                </tbody>
            </table>
        </div>
    `;
}

function refreshActiveUsers() {
    var icon = document.getElementById('refresh-icon');
    if (icon) icon.style.animation = 'spin 1s linear infinite';

    fetch('{{ route("admin.dashboard.active-users") }}')
        .then(r => r.json())
        .then(data => {
            if (data.users) {
                renderActiveUsers(data.users, data.location);
            } else if (data.error) {
                const container = document.getElementById('active-users-container');
                if (container) {
                    container.innerHTML = `<p class="text-rose-600 text-xs text-center py-6 font-semibold">⚠ ${escapeHtml(data.error)}</p>`;
                }
            }
        })
        .catch(err => {
            console.error('Failed to fetch active users:', err);
        })
        .finally(() => {
            if (icon) icon.style.animation = '';
        });
}

function kickUser(mac, locationId) {
    if (!confirm('Disconnect ' + mac + '?')) return;

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    fetch('{{ route("admin.dashboard.kick") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : '',
        },
        body: JSON.stringify({ mac: mac, location_id: locationId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            refreshActiveUsers();
        } else {
            alert('Error: ' + (data.error || 'Failed to disconnect user'));
        }
    })
    .catch(err => {
        alert('Gagal memutuskan koneksi client: ' + err.message);
    });
}
</script>
@endsection
