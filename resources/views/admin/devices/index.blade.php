@extends('layouts.admin')
@section('title', 'Connected Devices (Live Client Sessions)')
@section('page-title', 'Connected Devices')
@section('page-subtitle', 'Active session monitoring, bandwidth throughput tracking, and client device access control')

@section('content')
<div x-data="deviceManager()" class="space-y-6">

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-5 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Active Online Sessions</div>
                <div class="text-2xl font-black text-emerald-600">{{ $totalActive }}</div>
            </div>
        </div>

        <div class="card p-5 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Total Visits Today</div>
                <div class="text-2xl font-black text-slate-900">{{ $totalToday }}</div>
            </div>
        </div>

        <div class="card p-5 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Blacklisted Devices</div>
                <div class="text-2xl font-black text-rose-600">{{ $totalBlacklisted }}</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card p-4 bg-white border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.devices.index') }}" class="flex flex-wrap gap-3 items-center justify-between">
            <div class="flex flex-wrap gap-2 items-center flex-1">
                <!-- Search -->
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search MAC, IP, or account..."
                    class="input text-xs py-2 px-3 w-full sm:w-60"
                >

                <!-- Site Filter -->
                <select name="location_id" class="input text-xs py-2 px-3 w-full sm:w-44" onchange="this.form.submit()">
                    <option value="">All Sites</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                        {{ $loc->name }}
                    </option>
                    @endforeach
                </select>

                <!-- Status Filter -->
                <select name="status" class="input text-xs py-2 px-3 w-full sm:w-36" onchange="this.form.submit()">
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Sessions</option>
                    <option value="disconnected" {{ $status === 'disconnected' ? 'selected' : '' }}>Disconnected</option>
                </select>

                <!-- Brand Filter -->
                <select name="brand" class="input text-xs py-2 px-3 w-full sm:w-36" onchange="this.form.submit()">
                    <option value="">All Brands</option>
                    @foreach($brands as $brand)
                    <option value="{{ $brand }}" {{ request('brand') === $brand ? 'selected' : '' }}>
                        {{ $brand }}
                    </option>
                    @endforeach
                </select>

                <button type="submit" class="btn-secondary text-xs py-2 px-3 font-semibold">Apply</button>
                @if(request()->hasAny(['search', 'location_id', 'brand', 'type']) || $status !== 'active')
                <a href="{{ route('admin.devices.index') }}" class="text-xs text-slate-500 hover:text-slate-900 underline py-2 font-medium">Reset</a>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.devices.monitoring') }}" class="btn-primary text-xs py-2 px-3 flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Open Traffic Analytics</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Active Devices Table -->
    <div class="card overflow-hidden bg-white border border-slate-200/80 shadow-xs">
        <div class="p-4 border-b border-slate-200/80 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Connected Client Sessions</span>
            </h3>
            <span class="text-xs font-semibold text-slate-500">Total: {{ $sessions->total() }} Sessions</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50">
                        <th class="table-th">Device & Hardware</th>
                        <th class="table-th">MAC & IP Address</th>
                        <th class="table-th">Site Location</th>
                        <th class="table-th">Auth Method</th>
                        <th class="table-th">Connected Since</th>
                        <th class="table-th">Throughput (Down / Up)</th>
                        <th class="table-th">Status</th>
                        <th class="table-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($sessions as $session)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <!-- Device Brand & OS -->
                        <td class="p-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0 text-slate-600 border border-slate-200/60">
                                    @if($session->device_type === 'desktop')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    @elseif($session->device_type === 'tablet')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    @else
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>{{ $session->device_brand ?: 'Generic Device' }}</span>
                                        @if($session->device_model && $session->device_model !== $session->device_brand)
                                        <span class="text-[10px] text-slate-500">({{ $session->device_model }})</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $session->device_os }} · {{ $session->browser ?: 'Browser' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- MAC & IP -->
                        <td class="p-3.5">
                            <div class="font-mono text-slate-900 font-semibold flex items-center gap-1.5">
                                <span>{{ $session->client_mac }}</span>
                                @if($session->is_randomized_mac)
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 font-sans font-medium border border-purple-200" title="Private MAC Address">Private</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-500 font-mono">
                                IP: {{ $session->client_ip }}
                            </div>
                        </td>

                        <!-- Site -->
                        <td class="p-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 border border-slate-200 text-slate-700">
                                {{ $session->location?->name ?? '—' }}
                            </span>
                        </td>

                        <!-- Login Method -->
                        <td class="p-3.5">
                            @if($session->method === 'voucher')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">Voucher</span>
                            @elseif($session->method === 'member')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 text-purple-800 border border-purple-200">Member</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-800 border border-blue-200">Survey</span>
                            @endif
                            <div class="text-[10px] text-slate-500 mt-0.5 font-mono truncate max-w-[110px]">
                                {{ $session->identifier }}
                            </div>
                        </td>

                        <!-- Login Time & Duration -->
                        <td class="p-3.5">
                            <div class="text-slate-900 font-medium">{{ $session->login_time?->format('H:i:s') ?? '—' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $session->login_time?->format('d M Y') }} ({{ $session->duration }})</div>
                        </td>

                        <!-- Traffic In / Out -->
                        <td class="p-3.5">
                            <div class="text-brand font-semibold font-mono">
                                ⬇ {{ $session->formatted_out }}
                            </div>
                            <div class="text-slate-500 font-mono text-2xs">
                                ⬆ {{ $session->formatted_in }}
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="p-3.5">
                            @if($session->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Online
                            </span>
                            @elseif($session->status === 'disconnected')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">Disconnected</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">Expired</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="p-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($session->status === 'active')
                                <form method="POST" action="{{ route('admin.devices.kick') }}" onsubmit="return confirm('Disconnect active session for this device?')">
                                    @csrf
                                    <input type="hidden" name="mac" value="{{ $session->client_mac }}">
                                    <input type="hidden" name="location_id" value="{{ $session->location_id }}">
                                    <button type="submit" class="text-xs px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 font-semibold transition-colors" title="Disconnect Session">
                                        Disconnect
                                    </button>
                                </form>
                                @endif

                                <button
                                    @click="openBlockModal('{{ $session->client_mac }}', '{{ $session->location_id }}')"
                                    class="text-xs px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 font-semibold transition-colors"
                                    title="Blacklist Device MAC"
                                >
                                    Block
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-slate-500">
                            No client device sessions match the selected query.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
        <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>

    <!-- Blacklist Section -->
    @if($blacklisted->count() > 0)
    <div class="card p-5 bg-white border border-slate-200/80 shadow-xs">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            <span>Currently Blacklisted Devices</span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($blacklisted as $item)
            <div class="p-3 rounded-xl bg-rose-50/50 border border-rose-200 flex items-center justify-between gap-3 text-xs">
                <div>
                    <div class="font-mono text-slate-900 font-bold">{{ $item->mac_address }}</div>
                    <div class="text-[11px] text-slate-500">
                        {{ $item->location?->name ?? 'Global (All Sites)' }} · {{ $item->reason ?: 'Blocked' }}
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.devices.unblock', $item) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:text-emerald-700 hover:border-emerald-300 font-semibold transition-colors shadow-xs">
                        Unblock
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- ==================== MODAL: BLOCK MAC ==================== -->
    <div
        x-show="blockModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
    >
        <div @click.outside="blockModalOpen = false" class="card max-w-md w-full p-6 bg-white border border-slate-200 shadow-2xl rounded-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="text-rose-600">🚫</span>
                    <span>Blacklist MAC Address</span>
                </h3>
                <button @click="blockModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.devices.block') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="label text-slate-700 font-semibold mb-1 block">Target MAC Address</label>
                    <input type="text" name="mac" x-model="blockForm.mac" readonly class="input font-mono text-slate-900 bg-slate-100 font-bold border-slate-200 rounded-lg w-full">
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold mb-1 block">Blacklist Policy Scope</label>
                    <select name="location_id" x-model="blockForm.location_id" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full">
                        <option value="">All Sites (Global Blacklist)</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold mb-1 block">Reason for Denial</label>
                    <input type="text" name="reason" x-model="blockForm.reason" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="e.g. Terms of Service violation / spam">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="blockModalOpen = false" class="btn-secondary text-xs py-2 px-4 font-semibold">Cancel</button>
                    <button type="submit" class="text-xs py-2 px-5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold transition-colors shadow-sm">
                        Block Device
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
function deviceManager() {
    return {
        blockModalOpen: false,
        blockForm: {
            mac: '',
            location_id: '',
            reason: '',
        },
        openBlockModal(mac, locationId = '') {
            this.blockForm.mac = mac;
            this.blockForm.location_id = locationId;
            this.blockForm.reason = 'Network policy violation';
            this.blockModalOpen = true;
        }
    };
}
</script>
@endsection
