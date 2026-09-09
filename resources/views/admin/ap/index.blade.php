@extends('layouts.admin')

@section('title', 'Node Watchdog — Access Point Monitoring')
@section('page-title', 'AP Watchdog')
@section('page-subtitle', 'Monitor Wi-Fi access point distribution nodes, ICMP ping latency, and SLA downtime')

@section('content')
<div x-data="{
    showAddModal: false,
    showEditModal: false,
    isPingingAll: false,
    pingResults: {},
    editData: {
        id: '',
        name: '',
        ip_address: '',
        mac_address: '',
        zone_location: '',
        notes: ''
    },
    openEdit(ap) {
        this.editData = { ...ap };
        this.showEditModal = true;
    },
    async pingSingle(apId, btnEl) {
        btnEl.disabled = true;
        const icon = btnEl.querySelector('svg');
        if (icon) icon.classList.add('animate-spin');

        try {
            const res = await fetch(`{{ url('/admin/ap') }}/${apId}/ping`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            
            // Update row UI reactively
            const statusEl = document.getElementById(`status-badge-${apId}`);
            const latencyEl = document.getElementById(`latency-val-${apId}`);
            
            if (statusEl) {
                if (data.status === 'online') {
                    statusEl.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
                    statusEl.innerHTML = '<span class=\'w-2 h-2 rounded-full bg-emerald-500 animate-pulse\'></span> Online';
                } else if (data.status === 'degraded') {
                    statusEl.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200';
                    statusEl.innerHTML = '<span class=\'w-2 h-2 rounded-full bg-amber-500\'></span> Degraded';
                } else {
                    statusEl.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200';
                    statusEl.innerHTML = '<span class=\'w-2 h-2 rounded-full bg-rose-500\'></span> Offline';
                }
            }

            if (latencyEl) {
                latencyEl.innerText = data.latency !== null ? `${data.latency} ms` : 'RTO';
                latencyEl.className = data.status === 'online' ? 'text-xs font-mono font-bold text-emerald-600' : 'text-xs font-mono font-bold text-rose-600';
            }
        } catch (e) {
            console.error('Ping failed', e);
        } finally {
            btnEl.disabled = false;
            if (icon) icon.classList.remove('animate-spin');
        }
    },
    async pingAllAps() {
        if (!'{{ $currentLocation ? $currentLocation->id : '' }}') return;
        this.isPingingAll = true;
        try {
            const res = await fetch('{{ route('admin.ap.ping-all') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ location_id: '{{ $currentLocation ? $currentLocation->id : '' }}' })
            });
            const data = await res.json();
            window.location.reload();
        } catch (err) {
            console.error(err);
            this.isPingingAll = false;
        }
    }
}" class="space-y-5">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Node Watchdog (Access Point Checker)</h1>
                    <p class="text-slate-500 text-xs mt-0.5">Monitor Wi-Fi access point distribution nodes, ICMP ping latency, and SLA downtime</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <!-- Site Context Selector -->
            @if($locations->count() > 1)
            <form method="GET" action="{{ route('admin.ap.index') }}" class="flex items-center gap-2">
                <select name="location_id" onchange="this.form.submit()" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 font-semibold focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand">
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ $currentLocation && $currentLocation->id === $loc->id ? 'selected' : '' }}>
                        📍 {{ $loc->name }}
                    </option>
                    @endforeach
                </select>
            </form>
            @endif

            <button @click="pingAllAps()" :disabled="isPingingAll" class="btn-secondary text-xs py-2 px-3 flex items-center gap-2 font-semibold">
                <svg :class="isPingingAll ? 'animate-spin' : ''" class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span x-text="isPingingAll ? 'Pinging Nodes...' : 'Ping All APs'"></span>
            </button>

            <button @click="showAddModal = true" class="btn-primary text-xs py-2 px-4 flex items-center gap-2 shadow-sm font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add AP Node</span>
            </button>
        </div>
    </div>

    <!-- Active Site Banner -->
    @if($currentLocation)
    <div class="card p-4 bg-white border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-3 h-3 rounded-full {{ $onlineAps > 0 ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></div>
            <div>
                <div class="text-xs font-bold text-slate-700">Site Location: <span class="text-slate-900 font-extrabold">{{ $currentLocation->name }}</span></div>
                <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                    Gateway Subnet: {{ $currentLocation->router_ip ?? '192.168.88.1' }} • Watchdog Protocol: <span class="text-slate-900 font-bold">TCP & ICMP Probe</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                {{ $totalAps }} APs Registered
            </span>
        </div>
    </div>
    @endif

    <!-- Watchdog KPI Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
        <div class="card p-4 border border-slate-200/80 bg-white flex items-center justify-between shadow-xs">
            <div>
                <div class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">Total APs</div>
                <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $totalAps }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Installed Transmitters</div>
            </div>
            <div class="p-2.5 bg-slate-50 rounded-xl text-slate-500 border border-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/>
                </svg>
            </div>
        </div>

        <div class="card p-4 border border-emerald-200/80 bg-white flex items-center justify-between shadow-xs">
            <div>
                <div class="text-emerald-700 text-[10px] font-bold uppercase tracking-wider">Online</div>
                <div class="text-2xl font-black text-emerald-600 mt-0.5 flex items-center gap-1.5">
                    {{ $onlineAps }}
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">Responding to Ping</div>
            </div>
            <div class="p-2.5 bg-emerald-50 rounded-xl text-emerald-600 border border-emerald-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>

        <div class="card p-4 border border-rose-200/80 bg-white flex items-center justify-between shadow-xs">
            <div>
                <div class="text-rose-700 text-[10px] font-bold uppercase tracking-wider">Offline</div>
                <div class="text-2xl font-black text-rose-600 mt-0.5">{{ $offlineAps }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Attention Required</div>
            </div>
            <div class="p-2.5 bg-rose-50 rounded-xl text-rose-600 border border-rose-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>

        <div class="card p-4 border border-brand-200/80 bg-white flex items-center justify-between shadow-xs">
            <div>
                <div class="text-brand text-2xs font-bold uppercase tracking-wider">Average Latency</div>
                <div class="text-2xl font-black text-slate-900 mt-0.5 font-mono">
                    {{ $avgLatency ? number_format($avgLatency, 1) . ' ms' : '—' }}
                </div>
                <div class="text-2xs text-slate-500 mt-0.5">ICMP Response Time</div>
            </div>
            <div class="p-2.5 bg-brand-50 rounded-xl text-brand border border-brand-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Access Points Table -->
    <div class="card overflow-hidden border border-slate-200/80 bg-white shadow-xs">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                <span>Access Point (AP) Nodes</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-semibold bg-slate-100 text-slate-700">{{ $accessPoints->count() }} nodes</span>
            </h3>
            <span class="text-xs text-slate-500">Real-time probe via TCP & ICMP</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="table-th">AP Node & Location</th>
                        <th class="table-th">IP Address / MAC</th>
                        <th class="table-th text-center">Watchdog Status</th>
                        <th class="table-th text-center">Latency (ms)</th>
                        <th class="table-th">Last Heartbeat</th>
                        <th class="table-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($accessPoints as $ap)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-brand-50 border border-brand-100 flex items-center justify-center text-brand">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $ap->name }}</div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        @if($ap->zone_location)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                            📍 {{ $ap->zone_location }}
                                        </span>
                                        @endif
                                        @if($ap->notes)
                                        <span class="text-[10px] text-slate-500 italic truncate max-w-[180px]" title="{{ $ap->notes }}">
                                            {{ $ap->notes }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="py-3 px-4 font-mono">
                            <div class="font-bold text-slate-900">{{ $ap->ip_address }}</div>
                            <div class="text-[10px] text-slate-500">{{ $ap->mac_address ?: '—' }}</div>
                        </td>

                        <td class="py-3 px-4 text-center">
                            <span id="status-badge-{{ $ap->id }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $ap->status === 'online' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($ap->status === 'degraded' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                <span class="w-2 h-2 rounded-full {{ $ap->status === 'online' ? 'bg-emerald-500 animate-pulse' : ($ap->status === 'degraded' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                {{ ucfirst($ap->status) }}
                            </span>
                            @if($ap->status === 'offline' && $ap->downtime_minutes > 0)
                            <div class="text-[10px] text-rose-600 font-bold mt-1">
                                Down {{ $ap->downtime_minutes }}m
                            </div>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-center">
                            <span id="latency-val-{{ $ap->id }}" class="{{ $ap->status === 'online' ? 'text-emerald-600' : 'text-rose-600' }} font-mono font-bold">
                                {{ $ap->last_latency_ms !== null ? $ap->last_latency_ms . ' ms' : 'RTO' }}
                            </span>
                        </td>

                        <td class="py-3 px-4 text-slate-500 text-[11px] font-mono">
                            @if($ap->last_ping_at)
                            <div>{{ $ap->last_ping_at->diffForHumans() }}</div>
                            <div class="text-[10px] text-slate-400">{{ $ap->last_ping_at->format('H:i:s d/m') }}</div>
                            @else
                            <span class="text-slate-400">Never probed</span>
                            @endif
                        </td>

                        <td class="py-3 px-4">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" @click="pingSingle('{{ $ap->id }}', $el)" class="px-2.5 py-1 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand border border-brand-200 text-2xs font-bold flex items-center gap-1 transition-all" title="Probe this AP node now" aria-label="Probe AP {{ $ap->name }} now">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Ping
                                </button>

                                <button type="button" @click="openEdit({{ json_encode($ap) }})" class="p-1.5 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 focus-visible:ring-2 focus-visible:ring-brand" title="Edit AP" aria-label="Edit AP {{ $ap->name }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>

                                <form id="delete-ap-{{ $ap->id }}" method="POST" action="{{ route('admin.ap.destroy', $ap) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="button" 
                                        @click="$dispatch('open-confirm', {
                                            title: 'Hapus Node Access Point',
                                            message: 'Apakah Anda yakin ingin menghapus access point {{ addslashes($ap->name) }} ({{ $ap->ip_address }})? Riwayat telemetri ping akan dihapus.',
                                            confirmText: 'Ya, Hapus AP',
                                            cancelText: 'Batal',
                                            danger: true,
                                            onConfirm: () => document.getElementById('delete-ap-{{ $ap->id }}').submit()
                                        })"
                                        class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 focus-visible:ring-2 focus-visible:ring-rose-500" 
                                        title="Hapus AP" 
                                        aria-label="Hapus AP {{ $ap->name }}"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <div class="text-sm font-bold text-slate-900">Belum Ada Node Access Point Terdaftar</div>
                            <p class="text-xs text-slate-500 mt-1">Daftarkan IP Address AP Anda untuk monitoring telemetri otomatis.</p>
                            <button @click="showAddModal = true" class="btn-primary text-xs mt-3 shadow-sm font-semibold">
                                + Daftarkan Node AP Baru
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah AP -->
    <x-modal name="showAddModal" title="Tambah Node Access Point" max-width="lg">
        <form method="POST" action="{{ route('admin.ap.store') }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="location_id" value="{{ $currentLocation ? $currentLocation->id : '' }}">

            <div>
                <label class="label text-slate-700 font-semibold">Nama / Identifier Node AP <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="cth. AP-Lobby-UniFi-U6" class="input text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label text-slate-700 font-semibold">Alamat IP AP <span class="text-rose-500">*</span></label>
                    <input type="text" name="ip_address" required placeholder="192.168.88.10" class="input font-mono text-xs">
                </div>
                <div>
                    <label class="label text-slate-700 font-semibold">MAC Address (Opsional)</label>
                    <input type="text" name="mac_address" placeholder="AA:BB:CC:DD:EE:FF" class="input font-mono text-xs">
                </div>
            </div>

            <div>
                <label class="label text-slate-700 font-semibold">Zona Fisik / Lokasi Penempatan</label>
                <input type="text" name="zone_location" placeholder="cth. Lobby Tamu Lantai 1" class="input text-xs">
            </div>

            <div>
                <label class="label text-slate-700 font-semibold">Catatan Administratif (Opsional)</label>
                <input type="text" name="notes" placeholder="cth. Switch PoE Port 4" class="input text-xs">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" @click="showAddModal = false" class="btn-secondary text-xs font-semibold">Batal</button>
                <button type="submit" class="btn-primary text-xs font-semibold shadow-sm">Simpan & Mulai Monitoring</button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Edit AP -->
    <x-modal name="showEditModal" title="Edit Node Access Point" max-width="lg">
        <form :action="'{{ url('/admin/ap') }}/' + editData.id" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="label text-slate-700 font-semibold">Nama / Identifier Node AP <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required x-model="editData.name" class="input text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label text-slate-700 font-semibold">Alamat IP AP <span class="text-rose-500">*</span></label>
                    <input type="text" name="ip_address" required x-model="editData.ip_address" class="input font-mono text-xs">
                </div>
                <div>
                    <label class="label text-slate-700 font-semibold">MAC Address</label>
                    <input type="text" name="mac_address" x-model="editData.mac_address" class="input font-mono text-xs">
                </div>
            </div>

            <div>
                <label class="label text-slate-700 font-semibold">Zona Fisik / Lokasi Penempatan</label>
                <input type="text" name="zone_location" x-model="editData.zone_location" class="input text-xs">
            </div>

            <div>
                <label class="label text-slate-700 font-semibold">Catatan Administratif</label>
                <input type="text" name="notes" x-model="editData.notes" class="input text-xs">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" @click="showEditModal = false" class="btn-secondary text-xs font-semibold">Batal</button>
                <button type="submit" class="btn-primary text-xs font-semibold shadow-sm">Perbarui Node AP</button>
            </div>
        </form>
    </x-modal>

</div>
@endsection
