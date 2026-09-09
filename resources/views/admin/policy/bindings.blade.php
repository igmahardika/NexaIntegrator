@extends('layouts.admin')
@section('title', 'Layer-2 MAC Policies (Whitelist & Blacklist)')
@section('page-title', 'Layer-2 MAC Policies')
@section('page-subtitle', 'Manage portal bypass whitelisting for IoT/Smart TV/CCTV and Layer-2 hardware blacklist bindings')

@section('content')
<div x-data="policyManager()" class="space-y-5">

    <!-- Top Summary & Tenant Selector Banner -->
    <div class="card p-5 bg-white border border-slate-200/80 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Target Site:</span>
                    <h2 class="text-base font-extrabold text-slate-900">{{ $currentLocation?->name ?? 'Select Site' }}</h2>
                    @if($currentLocation)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-semibold {{ $currentLocation->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                        Router: {{ $currentLocation->router_ip ?: 'Not configured' }}
                    </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 font-medium">
                    Active Bypass: <strong class="text-emerald-700 font-bold">{{ $totalBypassed }} Devices</strong> ·
                    Blocked: <strong class="text-rose-700 font-bold">{{ $totalBlocked }} Devices</strong>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Location Switcher -->
                <form method="GET" action="{{ route('admin.policy.bindings') }}" class="flex items-center gap-2">
                    <select name="location_id" class="input text-xs py-1.5 px-3 w-48 font-semibold bg-white" onchange="this.form.submit()">
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ $currentLocation?->id === $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }}
                        </option>
                        @endforeach
                    </select>
                </form>

                @if($currentLocation)
                <form method="POST" action="{{ route('admin.policy.bindings.sync', $currentLocation) }}">
                    @csrf
                    <button type="submit" class="btn-secondary text-xs py-2 px-3 flex items-center gap-1.5 hover:border-brand hover:text-brand font-semibold">
                        <svg class="w-3.5 h-3.5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Sync to Router</span>
                    </button>
                </form>
                @endif

                <button @click="openModal('create')" class="btn-primary text-xs py-2 px-4 flex items-center gap-2 shadow-sm font-semibold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Add MAC Policy</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Segmented Filter Tabs: Bypass vs Blocked -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-2">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.policy.bindings', ['type' => 'bypassed', 'location_id' => $currentLocation?->id]) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $type === 'bypassed' ? 'bg-brand text-white shadow-sm' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200' }}">
                <span>Bypassed Devices (Whitelist)</span>
                <span class="px-1.5 py-0.5 rounded-full text-2xs {{ $type === 'bypassed' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $totalBypassed }}</span>
            </a>

            <a href="{{ route('admin.policy.bindings', ['type' => 'blocked', 'location_id' => $currentLocation?->id]) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $type === 'blocked' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200' }}">
                <span>Blocked Devices (Blacklist)</span>
                <span class="px-1.5 py-0.5 rounded-full text-2xs {{ $type === 'blocked' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $totalBlocked }}</span>
            </a>
        </div>

        <form method="GET" action="{{ route('admin.policy.bindings') }}" class="flex items-center gap-2">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="location_id" value="{{ $currentLocation?->id }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search MAC or comment..." class="input text-xs py-1.5 px-3 w-56">
            <button type="submit" class="btn-secondary text-xs py-1.5 px-3 font-semibold">Search</button>
        </form>
    </div>

    <!-- Policy Mechanism Explanation Alert Box -->
    @if($type === 'bypassed')
    <div class="p-4 rounded-xl bg-blue-50/80 border border-blue-200 text-xs text-blue-950 flex items-start gap-3">
        <svg class="w-5 h-5 text-brand flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <div class="font-extrabold text-brand mb-0.5">Captive Portal Whitelisting (Bypass Mode):</div>
            <p class="text-slate-600 leading-relaxed font-normal">
                Devices registered here bypass captive portal authentication automatically without prompting a captive portal screen. Strongly recommended for non-browser headless hardware: <strong>Smart TVs, IP CCTV Cameras, Thermal Receipt Printers, POS / EDC Terminals</strong>, and IoT sensors.
            </p>
        </div>
    </div>
    @else
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-950 flex items-start gap-3">
        <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <div class="font-extrabold text-rose-700 mb-0.5">Layer-2 MAC Blacklisting (Denial Mode):</div>
            <p class="text-slate-600 leading-relaxed font-normal">
                Devices listed here are blocked directly at the Layer-2 IP Binding table of the MikroTik edge router (`type=blocked`). The edge router immediately drops all packets and denies portal and WAN access to this hardware address.
            </p>
        </div>
    </div>
    @endif

    <!-- Table of Bindings -->
    <div class="card overflow-hidden bg-white border border-slate-200/80 shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50">
                        <th class="table-th">Category & Description</th>
                        <th class="table-th">MAC Address</th>
                        <th class="table-th">Fixed IP Assignment</th>
                        <th class="table-th">Policy Action</th>
                        <th class="table-th">RouterOS Sync</th>
                        <th class="table-th">Created At</th>
                        <th class="table-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($bindings as $b)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <!-- Category & Comment -->
                        <td class="p-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs {{ $b->type === 'bypassed' ? 'bg-blue-50 text-brand border border-blue-200' : 'bg-rose-50 text-rose-600 border border-rose-200' }}">
                                    @if($b->device_category === 'smart_tv') TV
                                    @elseif($b->device_category === 'cctv') CAM
                                    @elseif($b->device_category === 'printer') PRN
                                    @elseif($b->device_category === 'pos') POS
                                    @elseif($b->device_category === 'console') CON
                                    @else IOT
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $b->comment ?: 'No description' }}</div>
                                    <div class="text-2xs text-slate-500 capitalize">{{ $b->formatted_category }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- MAC Address -->
                        <td class="p-3.5">
                            <span class="font-mono text-slate-900 font-bold bg-slate-100 px-2 py-0.5 rounded border border-slate-200/60">{{ $b->mac_address }}</span>
                        </td>

                        <!-- Fixed IP -->
                        <td class="p-3.5 font-mono text-slate-600 font-medium">
                            {{ $b->address ?: 'Dynamic DHCP' }}
                        </td>

                        <!-- Policy Type -->
                        <td class="p-3.5">
                            @if($b->type === 'bypassed')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                ✓ Bypassed (Whitelist)
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                ✕ Blocked (Blacklist)
                            </span>
                            @endif
                        </td>

                        <!-- Router Sync Status -->
                        <td class="p-3.5">
                            @if($b->synced_to_router)
                            <span class="inline-flex items-center gap-1 text-2xs font-bold text-emerald-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Synced
                            </span>
                            @else
                            <span class="text-2xs text-slate-500 font-medium">Cloud Staged</span>
                            @endif
                        </td>

                        <!-- Created At -->
                        <td class="p-3.5 text-slate-500 text-2xs font-mono">
                            {{ $b->created_at?->format('d M Y H:i') }}
                        </td>

                        <!-- Action -->
                        <td class="p-3.5 text-right">
                            <form id="delete-binding-{{ $b->id }}" method="POST" action="{{ route('admin.policy.bindings.destroy', $b) }}">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="button" 
                                    @click="$dispatch('open-confirm', {
                                        title: 'Hapus Kebijakan MAC Layer-2',
                                        message: 'Apakah Anda yakin ingin menghapus aturan kebijakan MAC {{ $b->mac_address }}? Perangkat tidak lagi menerapkan bypass/blokir ini.',
                                        confirmText: 'Ya, Hapus Aturan',
                                        cancelText: 'Batal',
                                        danger: true,
                                        onConfirm: () => document.getElementById('delete-binding-{{ $b->id }}').submit()
                                    })"
                                    class="btn-danger text-xs py-1 px-2.5"
                                    aria-label="Hapus kebijakan MAC {{ $b->mac_address }}"
                                    title="Hapus Kebijakan"
                                >
                                    ✕
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-400">
                            Tidak ada aturan kebijakan Layer-2 yang ditemukan untuk kategori ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bindings->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $bindings->links() }}
        </div>
        @endif
    </div>

    <!-- ==================== MODAL: ADD BINDING ==================== -->
    <x-modal name="modalOpen" title="Tambah Kebijakan MAC Layer-2" max-width="lg">
        <form method="POST" action="{{ route('admin.policy.bindings.store') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="label text-slate-700 font-semibold">Target Site Lokasi <span class="text-rose-500">*</span></label>
                <select name="location_id" class="input bg-white" required>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ $currentLocation?->id === $loc->id ? 'selected' : '' }}>
                        {{ $loc->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label text-slate-700 font-semibold">Tipe Kebijakan <span class="text-rose-500">*</span></label>
                    <select name="type" class="input bg-white" required>
                        <option value="bypassed">Bypass (Whitelist IoT / Smart TV / CCTV)</option>
                        <option value="blocked">Blokir (Blacklist Layer-2)</option>
                    </select>
                </div>
                <div>
                    <label class="label text-slate-700 font-semibold">Kategori Perangkat</label>
                    <select name="device_category" class="input bg-white">
                        <option value="smart_tv">Smart TV / Layar Kamar</option>
                        <option value="cctv">IP CCTV Camera</option>
                        <option value="printer">Printer Thermal POS</option>
                        <option value="pos">Terminal EDC / POS Kasir</option>
                        <option value="console">Gaming Console</option>
                        <option value="iot">Sensor IoT Lainnya</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="label text-slate-700 font-semibold">MAC Address Perangkat <span class="text-rose-500">*</span></label>
                <input type="text" name="mac_address" required class="input font-mono uppercase font-bold" placeholder="AA:BB:CC:DD:EE:FF">
            </div>

            <div>
                <label class="label text-slate-700 font-semibold">Fixed Static IP Address (Opsional)</label>
                <input type="text" name="address" class="input font-mono" placeholder="192.168.88.50">
            </div>

            <div>
                <label class="label text-slate-700 font-semibold">Keterangan / Lokasi Unit</label>
                <input type="text" name="comment" class="input" placeholder="cth. Smart TV Kamar 302">
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" @click="modalOpen = false" class="btn-secondary text-xs py-2 px-4 font-semibold">Batal</button>
                <button type="submit" class="btn-primary text-xs py-2 px-5 font-semibold">Simpan Aturan</button>
            </div>
        </form>
    </x-modal>

</div>

@endsection

@section('scripts')
<script>
function policyManager() {
    return {
        modalOpen: false,
        openModal() {
            this.modalOpen = true;
        }
    };
}
</script>
@endsection
