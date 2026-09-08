@extends('layouts.admin')
@section('title', 'Monitor Duration Login — Lawful Interception Hub')
@section('page-title', 'Monitor Duration Login & Audit Forensik')
@section('page-subtitle', 'Pencatatan sesi internet, relasi IP Lokal, MAC Fisik, identitas tamu (Kamar/Email/WA), dan instrumen kepatuhan forensik telekomunikasi.')

@section('header-actions')
<a href="{{ route('admin.analytics.duration.export', request()->query()) }}" class="btn-primary text-xs py-2 px-3.5 flex items-center gap-1.5 font-bold shadow-xs">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
    </svg>
    <span>Export Forensic CSV</span>
</a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4 bg-white border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-brand shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Total Sesi Tercatat</div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($totalSessions) }}</div>
                <div class="text-2xs text-slate-400">Jejak Log Forensik</div>
            </div>
        </div>

        <div class="card p-4 bg-white border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" />
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Sesi Aktif Saat Ini</div>
                <div class="text-2xl font-black text-emerald-600">{{ number_format($activeSessions) }}</div>
                <div class="text-2xs text-emerald-600 font-semibold">Online di Router RAM</div>
            </div>
        </div>

        <div class="card p-4 bg-white border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Agregat Bandwidth</div>
                <div class="text-2xl font-black text-purple-600">{{ \App\Models\PortalSession::formatBytes($totalBytes) }}</div>
                <div class="text-2xs text-slate-400">Total Trafik In + Out</div>
            </div>
        </div>

        <div class="card p-4 bg-white border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Kepatuhan Forensik</div>
                <div class="text-2xl font-black text-amber-600">100%</div>
                <div class="text-2xs text-slate-500">Lawful Interception Ready</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card p-4 bg-white border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.analytics.duration') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <!-- Search Query -->
            <div class="lg:col-span-2">
                <label class="label">Cari IP / MAC / Identitas (Kamar/Email/WA)</label>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Contoh: 192.168.88.20, AA:BB:CC, Room 301..." 
                    class="input input-sm font-mono"
                >
            </div>

            <!-- Site Filter -->
            <div>
                <label class="label">Site / Cabang</label>
                <select name="location_id" class="input input-sm">
                    <option value="">Semua Site (Global)</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                        {{ $loc->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Auth Method Filter -->
            <div>
                <label class="label">Metode Login</label>
                <select name="method" class="input input-sm">
                    <option value="all">Semua Metode</option>
                    <option value="pms" {{ request('method') === 'pms' ? 'selected' : '' }}>Hotel PMS (Room #)</option>
                    <option value="voucher" {{ request('method') === 'voucher' ? 'selected' : '' }}>Voucher / Kode</option>
                    <option value="whatsapp" {{ request('method') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="member" {{ request('method') === 'member' ? 'selected' : '' }}>Member / Staff</option>
                    <option value="survey" {{ request('method') === 'survey' ? 'selected' : '' }}>Survei / Kuesioner</option>
                    <option value="email" {{ request('method') === 'email' ? 'selected' : '' }}>Email</option>
                    <option value="quick" {{ request('method') === 'quick' ? 'selected' : '' }}>1-Click Button</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="label">Status Sesi</label>
                <select name="status" class="input input-sm">
                    <option value="all">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif (Online)</option>
                    <option value="disconnected" {{ request('status') === 'disconnected' ? 'selected' : '' }}>Selesai / Terputus</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary text-xs py-2 px-3 w-full font-bold justify-center">
                    Filter
                </button>
                <a href="{{ route('admin.analytics.duration') }}" class="btn-secondary text-xs py-2 px-3 justify-center" title="Reset Filter">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Forensic Session Logs Table -->
    <div class="card bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span>Log Durasi Sesi & Audit Forensik Klien</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Seluruh relasi IP, MAC fisik, dan identitas disimpan sebagai rekam jejak kepatuhan hukum ISP.</p>
            </div>
            <div class="text-2xs text-slate-500 font-mono">
                Menampilkan {{ $sessions->firstItem() ?? 0 }} - {{ $sessions->lastItem() ?? 0 }} dari {{ number_format($sessions->total()) }} log
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="table-th">Waktu Start / Stop</th>
                        <th class="table-th">Identitas Tamu / User</th>
                        <th class="table-th">IP Lokal & MAC Fisik</th>
                        <th class="table-th">Site / Cabang</th>
                        <th class="table-th">Metode & Status</th>
                        <th class="table-th">Durasi</th>
                        <th class="table-th">Bandwidth Usage</th>
                        <th class="table-th">Perangkat & OS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sessions as $session)
                    <tr class="hover:bg-slate-50/60 transition font-sans">
                        <!-- Start / Stop Timestamps -->
                        <td class="py-3 px-4 font-mono text-2xs whitespace-nowrap">
                            <div class="font-bold text-slate-900">
                                {{ $session->login_time ? $session->login_time->format('Y-m-d H:i:s') : '—' }}
                            </div>
                            <div class="text-slate-500 mt-0.5">
                                @if($session->logout_time)
                                    Stop: {{ $session->logout_time->format('H:i:s') }}
                                @elseif($session->status === 'active')
                                    <span class="inline-flex items-center text-emerald-600 font-bold gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Sedang Berjalan
                                    </span>
                                @else
                                    Stop: —
                                @endif
                            </div>
                        </td>

                        <!-- Identity (Room / Email / WhatsApp / Voucher) -->
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 max-w-[200px] truncate" title="{{ $session->identifier }}">
                                {{ $session->identifier ?: 'Tamu Anonim' }}
                            </div>
                            <div class="text-2xs text-slate-500 uppercase tracking-wider font-semibold">
                                {{ $session->method }}
                            </div>
                        </td>

                        <!-- IP & MAC Address -->
                        <td class="py-3 px-4 font-mono text-2xs">
                            <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $session->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                <span>{{ $session->client_ip ?: '—' }}</span>
                            </div>
                            <div class="text-slate-500 font-semibold tracking-wider mt-0.5">
                                {{ $session->client_mac }}
                            </div>
                        </td>

                        <!-- Site Location -->
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-xs">
                                {{ $session->location?->name ?? 'Global Site' }}
                            </div>
                            <div class="text-2xs text-slate-500 font-mono">
                                {{ $session->location?->slug ?? '—' }}
                            </div>
                        </td>

                        <!-- Auth Method & Status Badge -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-bold uppercase tracking-wider {{ $session->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                {{ $session->status === 'active' ? 'Online' : 'Closed' }}
                            </span>
                            <div class="text-2xs text-slate-400 font-mono mt-0.5">
                                {{ strtoupper($session->method) }}
                            </div>
                        </td>

                        <!-- Duration -->
                        <td class="py-3 px-4 font-mono text-2xs whitespace-nowrap">
                            <span class="font-bold text-slate-800">{{ $session->duration ?? '—' }}</span>
                        </td>

                        <!-- Bandwidth Usage -->
                        <td class="py-3 px-4 font-mono text-2xs whitespace-nowrap">
                            <div class="font-bold text-slate-900">
                                {{ $session->formatted_traffic }}
                            </div>
                            <div class="text-slate-400 text-[10px]">
                                ↓ {{ $session->formatted_in }} | ↑ {{ $session->formatted_out }}
                            </div>
                        </td>

                        <!-- Device / OS / Browser -->
                        <td class="py-3 px-4 text-2xs">
                            <div class="text-slate-800 font-semibold truncate max-w-[150px]">
                                {{ $session->device_os ?: 'Unknown OS' }} / {{ $session->browser ?: 'Browser' }}
                            </div>
                            <div class="text-slate-400 truncate max-w-[150px]" title="{{ $session->user_agent }}">
                                {{ $session->device_brand ?: 'Perangkat Mobile/PC' }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            Tidak ada log sesi yang cocok dengan kriteria filter pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($sessions->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
