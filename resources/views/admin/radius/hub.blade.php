@extends('layouts.admin')
@section('title', 'Edge Gateway Fleet & RADIUS Hub')
@section('page-title', 'Edge Gateway Fleet & RADIUS Hub')
@section('page-subtitle', 'Monitoring status koneksi router MikroTik cabang, autentikasi AAA RADIUS, dan gateway zero-burden')

@section('content')
<div x-data="fleetGatewayManager()" class="space-y-6">

    <!-- Top Architecture Banner -->
    <div class="bg-gradient-to-br from-slate-900 via-brand-dark to-slate-900 rounded-2xl p-6 text-white border border-slate-800 shadow-md flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="space-y-2 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-semibold border border-blue-400/30">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Arsitektur MikroTik Zero-Burden Flash Protection</span>
            </div>
            <h2 class="text-xl font-extrabold tracking-tight">MikroTik Edge Gateway & AAA RADIUS Fleet Hub</h2>
            <p class="text-xs text-blue-100/80 leading-relaxed">
                Platform ini mengontrol sesi captive portal secara terpusat tanpa membebani memori flash router MikroTik (16MB). 
                Router hanya menampung sesi aktif di RAM (<code class="bg-black/40 px-1 py-0.5 rounded text-amber-300 font-mono">/ip hotspot active</code>), sedangkan kredensial dan otentikasi diproses secara instan melalui Cloud RADIUS AAA (Port 1812/1813).
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0">
            <a href="{{ route('admin.docs.index') }}?tab=mikrotik" class="w-full sm:w-auto px-4 py-2.5 text-xs font-bold text-slate-200 bg-white/10 hover:bg-white/20 rounded-xl border border-white/10 transition text-center flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Dokumentasi RouterOS</span>
            </a>
            <a href="{{ route('admin.sites.index') }}" class="w-full sm:w-auto px-4 py-2.5 text-xs font-bold text-slate-900 bg-white hover:bg-slate-100 rounded-xl shadow-sm transition text-center">
                Kelola Site &rarr;
            </a>
        </div>
    </div>

    <!-- Fleet Health KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-brand shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Edge Router</div>
                <div class="text-2xl font-black text-slate-900">{{ $sites->count() }} Router</div>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Router Terkonfigurasi</div>
                <div class="text-2xl font-black text-emerald-600">{{ $sites->whereNotNull('router_ip')->where('router_ip', '!=', '')->count() }} Site</div>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">RADIUS AAA Aktif</div>
                <div class="text-2xl font-black text-purple-600">{{ $sites->where('radius_enabled', true)->count() }} Site</div>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-600 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Sesi Tamu (RAM)</div>
                <div class="text-2xl font-black text-cyan-600">{{ $sites->sum('active_sessions_count') }} Online</div>
            </div>
        </div>
    </div>

    <!-- Edge Gateway Fleet List -->
    <div class="card bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                    <span>Daftar Edge Gateway MikroTik Seluruh Cabang</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Pilih salah satu cabang untuk mengonfigurasi skrip RADIUS, Walled Garden, dan file login.html.</p>
            </div>

            <div class="text-xs font-semibold text-slate-500 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                Pilih site untuk konfigurasi individual &rarr;
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 border-b border-slate-200 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Nama Site Cabang</th>
                        <th class="py-3 px-4">Router Gateway IP & Port</th>
                        <th class="py-3 px-4">Status RADIUS AAA</th>
                        <th class="py-3 px-4">PoD / CoA Port</th>
                        <th class="py-3 px-4">Sesi Aktif (RAM)</th>
                        <th class="py-3 px-4">Live Test & Telemetri</th>
                        <th class="py-3 px-4 text-right">Konfigurasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sites as $site)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-brand flex items-center justify-center font-bold text-xs shrink-0 border border-blue-100">
                                    {{ strtoupper(substr($site->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $site->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $site->slug }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="py-3 px-4">
                            @if(!empty($site->router_ip))
                            <div class="font-mono font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>{{ $site->router_ip }}</span>
                                <span class="text-slate-400 font-normal">:{{ $site->router_port ?: 8728 }}</span>
                            </div>
                            <div class="text-[10px] text-slate-400">DNS: {{ $site->dns_name ?: 'wifi.login' }}</div>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                192.168.88.1 (Default)
                            </span>
                            @endif
                        </td>

                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $site->radius_enabled ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $site->radius_enabled ? 'bg-purple-500' : 'bg-slate-400' }}"></span>
                                {{ $site->radius_enabled ? 'AAA Active' : 'Standby' }}
                            </span>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                NAS: {{ $site->radius_nas_id ?: $site->slug }}
                            </div>
                        </td>

                        <td class="py-3 px-4">
                            <span class="font-mono text-slate-700 font-semibold bg-slate-100 px-2 py-0.5 rounded text-[11px]">
                                UDP {{ $site->radius_coa_port ?: 3799 }}
                            </span>
                        </td>

                        <td class="py-3 px-4">
                            <span class="font-bold text-brand text-sm">{{ $site->active_sessions_count }}</span>
                            <span class="text-slate-400 text-[10px]">klien</span>
                        </td>

                        <td class="py-3 px-4">
                            <!-- Telemetry Result Container -->
                            <div x-show="testResults['{{ $site->id }}']" class="mb-1.5">
                                <template x-if="testResults['{{ $site->id }}']?.connected">
                                    <div class="p-1.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-mono leading-tight">
                                        <div class="font-bold text-emerald-900">✓ Online (<span x-text="testResults['{{ $site->id }}'].latency_ms"></span>ms)</div>
                                        <div class="text-slate-600" x-text="testResults['{{ $site->id }}'].board_name + ' — ' + testResults['{{ $site->id }}'].version"></div>
                                        <div class="text-slate-500">CPU: <span x-text="testResults['{{ $site->id }}'].cpu_load"></span> | RAM: <span x-text="testResults['{{ $site->id }}'].free_memory"></span></div>
                                    </div>
                                </template>
                                <template x-if="testResults['{{ $site->id }}'] && !testResults['{{ $site->id }}']?.connected">
                                    <div class="p-1.5 rounded-lg bg-rose-50 text-rose-800 border border-rose-200 text-[10px] font-mono leading-tight">
                                        <div class="font-bold text-rose-900">✕ Router Offline</div>
                                        <div class="text-rose-600 truncate" x-text="testResults['{{ $site->id }}'].error"></div>
                                    </div>
                                </template>
                            </div>

                            <button 
                                @click="runApiTest('{{ $site->id }}')" 
                                :disabled="testing['{{ $site->id }}']"
                                class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center gap-1"
                            >
                                <span x-show="!testing['{{ $site->id }}']">⚡ Test RouterOS API</span>
                                <span x-show="testing['{{ $site->id }}']" x-cloak>Testing...</span>
                            </button>
                        </td>

                        <td class="py-3 px-4 text-right">
                            <a 
                                href="{{ route('admin.radius.index', ['site_id' => $site->id]) }}" 
                                class="btn-primary text-xs py-1 px-3 shadow-xs inline-flex items-center gap-1.5"
                            >
                                <span>Konfigurasi RADIUS</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            Belum ada site yang terdaftar. Tambahkan site baru terlebih dahulu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
function fleetGatewayManager() {
    return {
        testing: {},
        testResults: {},
        runApiTest(siteId) {
            this.testing[siteId] = true;
            fetch('/admin/radius/test-api/' + siteId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                this.testResults[siteId] = data;
                this.testing[siteId] = false;
            })
            .catch(err => {
                this.testResults[siteId] = { connected: false, error: 'Network communication error' };
                this.testing[siteId] = false;
            });
        }
    };
}
</script>
@endpush
@endsection
