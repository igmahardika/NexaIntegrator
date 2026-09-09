@extends('layouts.admin')

@section('title', 'Hotspot Users - ' . ($currentSite->name ?? 'Site'))

@section('content')
<div class="space-y-6" x-data="{
    createModal: false,
    activeMethod: 'access_code',
    printModal: false
}">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <span>{{ $currentSite->name ?? 'Site' }}</span>
                <span>/</span>
                <span class="text-brand">Hotspot Users</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen User Hotspot & Access Code</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola Access Code tamu kantor, voucher massal, akun member, tamu WhatsApp, dan MAC whitelist.</p>
        </div>

        <!-- Adaptive Actions -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <button @click="createModal = true; activeMethod = 'access_code'" class="btn-primary flex items-center gap-2 font-bold shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Buat User Hotspot</span>
            </button>

            <button @click="createModal = true; activeMethod = 'voucher_batch'" class="btn-secondary flex items-center gap-2 font-semibold">
                <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span>Generate Massal</span>
            </button>

            @if($batches->isNotEmpty())
            <button @click="printModal = true" class="btn-secondary flex items-center gap-2 font-semibold">
                <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Voucher</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Site Quick Switcher Bar (When in All Sites / Multi-Site Mode) -->
    @if(isset($availableSites) && $availableSites->count() > 1)
    <div class="flex items-center gap-2 bg-white p-2.5 rounded-xl border border-slate-200/80 shadow-xs overflow-x-auto">
        <span class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider px-2 shrink-0">Kelola Site:</span>
        @foreach($availableSites as $s)
        <a href="{{ route('admin.hotspot-users.index', ['site_id' => $s->id, 'tab' => $tab]) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-bold transition shrink-0 flex items-center gap-1.5 {{ $currentSite && $currentSite->id === $s->id ? 'bg-brand text-white shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            <svg class="w-3.5 h-3.5 {{ $currentSite && $currentSite->id === $s->id ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>{{ $s->name }}</span>
            @if($currentSite && $currentSite->id === $s->id)
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            @endif
        </a>
        @endforeach
    </div>
    @endif

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold shadow-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <div class="kpi-card">
            <div>
                <p class="text-2xs font-bold uppercase tracking-wider text-slate-500">Total User</p>
                <p class="text-xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
        </div>

        <div class="kpi-card">
            <div>
                <p class="text-2xs font-bold uppercase tracking-wider text-slate-500">Voucher / Code</p>
                <p class="text-xl font-extrabold text-brand mt-1">{{ number_format($stats['vouchers']) }}</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            </div>
        </div>

        <div class="kpi-card">
            <div>
                <p class="text-2xs font-bold uppercase tracking-wider text-slate-500">Members</p>
                <p class="text-xl font-extrabold text-cyan-600 mt-1">{{ number_format($stats['members']) }}</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
        </div>

        <div class="kpi-card">
            <div>
                <p class="text-2xs font-bold uppercase tracking-wider text-slate-500">WhatsApp</p>
                <p class="text-xl font-extrabold text-emerald-600 mt-1">{{ number_format($stats['whatsapp']) }}</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
        </div>

        <div class="kpi-card">
            <div>
                <p class="text-2xs font-bold uppercase tracking-wider text-slate-500">MAC Whitelist</p>
                <p class="text-xl font-extrabold text-amber-600 mt-1">{{ number_format($stats['mac']) }}</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
            </div>
        </div>

        <div class="kpi-card">
            <div>
                <p class="text-2xs font-bold uppercase tracking-wider text-slate-500">Aktif Online</p>
                <p class="text-xl font-extrabold text-emerald-600 mt-1">{{ number_format($stats['active']) }}</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Segmented Tab Bar & Filters -->
    <div class="card p-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            
            <!-- Segmented Tabs for all Login Methods -->
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl w-full md:w-auto overflow-x-auto">
                <a href="{{ route('admin.hotspot-users.index', ['tab' => 'all']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $tab === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua ({{ $stats['total'] }})
                </a>
                <a href="{{ route('admin.hotspot-users.index', ['tab' => 'voucher']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $tab === 'voucher' ? 'bg-white text-brand shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Voucher / Code ({{ $stats['vouchers'] }})
                </a>
                <a href="{{ route('admin.hotspot-users.index', ['tab' => 'member']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $tab === 'member' ? 'bg-white text-cyan-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Member ({{ $stats['members'] }})
                </a>
                <a href="{{ route('admin.hotspot-users.index', ['tab' => 'whatsapp']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $tab === 'whatsapp' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    WhatsApp ({{ $stats['whatsapp'] }})
                </a>
                <a href="{{ route('admin.hotspot-users.index', ['tab' => 'mac']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $tab === 'mac' ? 'bg-white text-amber-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    MAC Bypass ({{ $stats['mac'] }})
                </a>
                <a href="{{ route('admin.hotspot-users.index', ['tab' => 'hotel']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $tab === 'hotel' ? 'bg-white text-rose-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Hotel Room ({{ $stats['hotel'] }})
                </a>
                <a href="{{ route('admin.hotspot-users.index', ['tab' => 'leads']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $tab === 'leads' ? 'bg-white text-purple-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Leads / Free ({{ $stats['leads'] }})
                </a>
            </div>

            <!-- Search & Filters Form -->
            <form action="{{ route('admin.hotspot-users.index') }}" method="GET" class="flex items-center gap-2 flex-wrap">
                <input type="hidden" name="tab" value="{{ $tab }}">

                @if($batches->isNotEmpty())
                <select name="batch" onchange="this.form.submit()" class="input py-1.5 px-3 text-xs w-36">
                    <option value="">Semua Batch</option>
                    @foreach($batches as $b)
                    <option value="{{ $b }}" {{ request('batch') === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
                @endif

                <select name="status" onchange="this.form.submit()" class="input py-1.5 px-3 text-xs w-32">
                    <option value="all">Semua Status</option>
                    <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Ready</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="depleted" {{ request('status') === 'depleted' ? 'selected' : '' }}>Depleted</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>Disabled</option>
                </select>

                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari user/MAC/nama..."
                           class="input py-1.5 pl-8 pr-3 text-xs w-48">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                @if(request()->anyFilled(['search', 'status', 'batch']))
                <a href="{{ route('admin.hotspot-users.index', ['tab' => $tab]) }}" class="text-xs text-slate-500 hover:text-slate-800 font-bold px-1.5 py-1">
                    Reset
                </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Hotspot Users Data Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-600 uppercase text-2xs font-extrabold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">User / Identifier</th>
                        <th class="py-3.5 px-3">Metode Login</th>
                        <th class="py-3.5 px-3">Profil QoS</th>
                        <th class="py-3.5 px-3">Status & Perangkat</th>
                        <th class="py-3.5 px-3">Durasi & Kuota (MikroTik)</th>
                        <th class="py-3.5 px-3">Info Tamu / Catatan</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/60 transition">
                        
                        <!-- Identifier -->
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-slate-900 text-xs">{{ $user->identifier }}</span>
                                @if($user->batch_name)
                                <span class="text-2xs px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold">
                                    {{ $user->batch_name }}
                                </span>
                                @endif
                            </div>
                            @if($user->expires_at)
                            <div class="text-2xs text-slate-400 mt-0.5">
                                Exp: {{ $user->expires_at->format('d/m/Y H:i') }}
                            </div>
                            @endif
                        </td>

                        <!-- Metode Login Badge -->
                        <td class="py-3 px-3">
                            @if($user->auth_method === 'voucher')
                                @if($user->batch_name === 'Access Code')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-extrabold bg-blue-50 text-brand border border-blue-200">
                                    Access Code
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    Voucher
                                </span>
                                @endif
                            @elseif($user->auth_method === 'member')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-extrabold bg-cyan-50 text-cyan-700 border border-cyan-200">
                                    Member
                                </span>
                            @elseif($user->auth_method === 'whatsapp')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    WhatsApp
                                </span>
                            @elseif($user->auth_method === 'mac_bypass')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                    MAC Bypass
                                </span>
                            @elseif($user->auth_method === 'pms')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                    Hotel Room
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-extrabold bg-purple-50 text-purple-700 border border-purple-200">
                                    {{ ucfirst($user->auth_method) }}
                                </span>
                            @endif
                        </td>

                        <!-- Profil QoS -->
                        <td class="py-3 px-3">
                            @if($user->profile)
                            <div class="font-bold text-slate-800">{{ $user->profile->name }}</div>
                            <div class="text-2xs text-slate-500 font-mono">{{ $user->profile->rate_limit }}</div>
                            @else
                            <span class="text-slate-400 italic">Default</span>
                            @endif
                        </td>

                        <!-- Status & Perangkat -->
                        <td class="py-3 px-3">
                            @if($user->status === 'active')
                                <span class="badge bg-emerald-100 text-emerald-800 animate-pulse">Online</span>
                            @elseif($user->status === 'ready')
                                <span class="badge bg-blue-50 text-blue-700">Ready</span>
                            @elseif($user->status === 'depleted')
                                <span class="badge bg-amber-50 text-amber-800">Habis Kuota</span>
                            @elseif($user->status === 'expired')
                                <span class="badge bg-slate-100 text-slate-600">Expired</span>
                            @elseif($user->status === 'disabled')
                                <span class="badge bg-rose-50 text-rose-700">Disabled</span>
                            @endif

                            @if(($user->simultaneous_use ?? 1) > 1)
                            <div class="text-2xs text-purple-700 font-semibold mt-0.5 flex items-center gap-1">
                                <svg class="w-3 h-3 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>Shared ({{ $user->simultaneous_use }} device)</span>
                            </div>
                            @elseif($user->bound_mac)
                            <div class="text-2xs text-slate-500 font-mono mt-0.5">MAC: {{ $user->bound_mac }}</div>
                            @endif
                        </td>

                        <!-- Durasi & Kuota (MikroTik) -->
                        <td class="py-3 px-3 text-slate-700">
                            <div>
                                <span class="font-bold">{{ gmdate('H:i:s', $user->used_uptime) }}</span>
                                @if($user->uptime_limit)
                                <span class="text-slate-500 text-2xs">/ {{ gmdate('H:i:s', $user->uptime_limit) }}</span>
                                @else
                                <span class="text-slate-400 text-2xs">/ Unlimited</span>
                                @endif
                            </div>
                            <div class="text-2xs text-slate-500">
                                {{ round(($user->bytes_in + $user->bytes_out) / 1048576, 1) }} MB
                                @if($user->data_limit_bytes)
                                / {{ round($user->data_limit_bytes / 1048576) }} MB
                                @endif
                            </div>
                        </td>

                        <!-- Info Tamu / Metadata -->
                        <td class="py-3 px-3">
                            @if(!empty($user->guest_metadata))
                                @if(isset($user->guest_metadata['device_name']))
                                <div class="font-bold text-slate-900 text-xs">{{ $user->guest_metadata['device_name'] }}</div>
                                @endif
                                @if(isset($user->guest_metadata['full_name']))
                                <div class="font-bold text-slate-900 text-xs">{{ $user->guest_metadata['full_name'] }}</div>
                                @endif
                                @if(isset($user->guest_metadata['guest_last_name']))
                                <div class="font-bold text-slate-900 text-xs">Tamu: {{ $user->guest_metadata['guest_last_name'] }}</div>
                                @endif
                                @if(isset($user->guest_metadata['phone']))
                                <div class="text-2xs text-slate-500 font-mono">{{ $user->guest_metadata['phone'] }}</div>
                                @endif
                                @if(isset($user->guest_metadata['notes']))
                                <div class="text-2xs text-slate-600">{{ $user->guest_metadata['notes'] }}</div>
                                @endif
                            @else
                            <span class="text-slate-400 italic text-2xs">-</span>
                            @endif
                        </td>

                        <!-- Aksi -->
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <form action="{{ route('admin.hotspot-users.toggle', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1 rounded-md text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none transition-colors"
                                            aria-label="{{ $user->status === 'disabled' ? 'Aktifkan user ' . $user->identifier : 'Nonaktifkan user ' . $user->identifier }}"
                                            title="{{ $user->status === 'disabled' ? 'Aktifkan' : 'Nonaktifkan' }}">
                                        @if($user->status === 'disabled')
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @else
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        @endif
                                    </button>
                                </form>

                                <form action="{{ route('admin.hotspot-users.destroy', $user) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Hapus user {{ $user->identifier }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded-md text-rose-500 hover:text-rose-700 hover:bg-rose-50 focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:outline-none transition-colors"
                                            aria-label="Hapus user {{ $user->identifier }}"
                                            title="Hapus User">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500">
                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                            <p class="font-bold text-slate-700 text-sm">Belum ada user hotspot pada filter ini</p>
                            <p class="text-xs text-slate-500 mt-0.5">Klik tombol "+ Buat User Hotspot" di atas untuk membuat user baru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>

    <!-- ==================== UNIFIED MULTI-METHOD CREATE MODAL ==================== -->
    <div x-show="createModal" x-cloak @keydown.escape.window="createModal = false" role="dialog" aria-modal="true" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="createModal = false" class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">Buat User Hotspot Baru</h3>
                        <p class="text-xs text-slate-500">Pilih metode login sesuai skenario autentikasi dan variabel MikroTik.</p>
                    </div>
                </div>
                <button type="button" @click="createModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Login Method Selector Tabs -->
            <div class="mt-4">
                <label class="label">Pilih Metode Login:</label>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 mt-1.5">
                    
                    <!-- Access Code -->
                    <button type="button" @click="activeMethod = 'access_code'"
                            :class="activeMethod === 'access_code' ? 'border-brand bg-blue-50 text-brand ring-2 ring-brand/15' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                            class="p-2.5 rounded-xl border text-center transition flex flex-col items-center gap-1.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <span class="text-2xs font-extrabold leading-tight">Access Code</span>
                    </button>

                    <!-- Voucher Massal -->
                    <button type="button" @click="activeMethod = 'voucher_batch'"
                            :class="activeMethod === 'voucher_batch' ? 'border-brand bg-blue-50 text-brand ring-2 ring-brand/15' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                            class="p-2.5 rounded-xl border text-center transition flex flex-col items-center gap-1.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span class="text-2xs font-extrabold leading-tight">Voucher Batch</span>
                    </button>

                    <!-- Member / Staff -->
                    <button type="button" @click="activeMethod = 'member'"
                            :class="activeMethod === 'member' ? 'border-brand bg-blue-50 text-brand ring-2 ring-brand/15' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                            class="p-2.5 rounded-xl border text-center transition flex flex-col items-center gap-1.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-2xs font-extrabold leading-tight">Member / Staff</span>
                    </button>

                    <!-- WhatsApp Guest -->
                    <button type="button" @click="activeMethod = 'whatsapp'"
                            :class="activeMethod === 'whatsapp' ? 'border-brand bg-blue-50 text-brand ring-2 ring-brand/15' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                            class="p-2.5 rounded-xl border text-center transition flex flex-col items-center gap-1.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span class="text-2xs font-extrabold leading-tight">WhatsApp</span>
                    </button>

                    <!-- MAC Whitelist -->
                    <button type="button" @click="activeMethod = 'mac_bypass'"
                            :class="activeMethod === 'mac_bypass' ? 'border-brand bg-blue-50 text-brand ring-2 ring-brand/15' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                            class="p-2.5 rounded-xl border text-center transition flex flex-col items-center gap-1.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                        <span class="text-2xs font-extrabold leading-tight">MAC Bypass</span>
                    </button>

                    <!-- Hotel Room -->
                    <button type="button" @click="activeMethod = 'hotel_room'"
                            :class="activeMethod === 'hotel_room' ? 'border-brand bg-blue-50 text-brand ring-2 ring-brand/15' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                            class="p-2.5 rounded-xl border text-center transition flex flex-col items-center gap-1.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="text-2xs font-extrabold leading-tight">Hotel Room</span>
                    </button>

                </div>
            </div>

            <div class="mt-5 border-t border-slate-100 pt-4">

                <!-- 1. FORM: ACCESS CODE -->
                <form x-show="activeMethod === 'access_code'" action="{{ route('admin.hotspot-users.access-code') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="label">Kode Akses (name MikroTik)</label>
                        <input type="text" name="code" placeholder="Contoh: KANTOR2026 atau TAMU-VIP" required
                               class="input uppercase font-mono tracking-wider font-bold"
                               oninput="this.value = this.value.toUpperCase()">
                        <p class="text-2xs text-slate-500 mt-1">Kode yang diinputkan pengguna pada portal login.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Password / PIN (Opsional)</label>
                            <input type="text" name="password" placeholder="Kosongkan untuk auto-token" class="input font-mono">
                        </div>
                        <div>
                            <label class="label">Profil Bandwidth (QoS)</label>
                            <select name="profile_id" class="input">
                                <option value="">Default Site Profile</option>
                                @foreach($profiles as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->rate_limit }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Batas Waktu (limit-uptime)</label>
                            <select name="uptime_limit_hrs" class="input">
                                <option value="">Unlimited (Tanpa Batas)</option>
                                <option value="1">1 Jam</option>
                                <option value="2" selected>2 Jam</option>
                                <option value="4">4 Jam</option>
                                <option value="8">8 Jam (1 Hari Kerja)</option>
                                <option value="24">24 Jam (1 Hari)</option>
                                <option value="168">7 Hari</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Maksimal Perangkat (shared-users)</label>
                            <select name="simultaneous_use" class="input">
                                <option value="1" selected>1 Perangkat (Personal)</option>
                                <option value="2">2 Perangkat</option>
                                <option value="5">5 Perangkat</option>
                                <option value="10">10 Perangkat (Rapat)</option>
                                <option value="25">25 Perangkat (Workshop)</option>
                                <option value="50">50 Perangkat (Event)</option>
                                <option value="100">100 Perangkat</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Batas Kuota Data (MB)</label>
                            <input type="number" name="data_limit_mb" placeholder="misal: 1000 (Kosongkan = Unlimited)" class="input">
                        </div>
                        <div>
                            <label class="label">Kunci MAC Address (Opsional)</label>
                            <input type="text" name="bound_mac" placeholder="XX:XX:XX:XX:XX:XX" class="input uppercase font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="label">Catatan / Keterangan (comment)</label>
                        <input type="text" name="notes" placeholder="misal: Tamu Ruang Rapat Lt. 2 / Event Seminar" class="input">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary font-bold">Simpan Access Code</button>
                    </div>
                </form>

                <!-- 2. FORM: VOUCHER BATCH -->
                <form x-show="activeMethod === 'voucher_batch'" action="{{ route('admin.hotspot-users.generate') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Jumlah Voucher</label>
                            <input type="number" name="quantity" value="20" min="1" max="500" required class="input">
                        </div>
                        <div>
                            <label class="label">Panjang Kode</label>
                            <input type="number" name="code_length" value="6" min="4" max="10" required class="input">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Prefix Kode (Opsional)</label>
                            <input type="text" name="prefix" placeholder="misal: VIP" class="input uppercase">
                        </div>
                        <div>
                            <label class="label">Profil Bandwidth (QoS)</label>
                            <select name="profile_id" class="input">
                                <option value="">Default Site Profile</option>
                                @foreach($profiles as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->rate_limit }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Batas Waktu / Jam (limit-uptime)</label>
                            <input type="number" step="0.5" name="uptime_limit_hrs" placeholder="2.0" class="input">
                        </div>
                        <div>
                            <label class="label">Batas Kuota / MB (limit-bytes)</label>
                            <input type="number" name="data_limit_mb" placeholder="1000" class="input">
                        </div>
                    </div>

                    <div>
                        <label class="label">Nama Batch (comment grouping)</label>
                        <input type="text" name="batch_name" value="Batch-{{ date('Ymd-Hi') }}" class="input font-mono">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary font-bold">Generate Sekarang</button>
                    </div>
                </form>

                <!-- 3. FORM: MEMBER / STAFF -->
                <form x-show="activeMethod === 'member'" action="{{ route('admin.hotspot-users.member') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Username Login (name MikroTik)</label>
                            <input type="text" name="username" required placeholder="misal: budi_kantor" class="input font-mono">
                        </div>
                        <div>
                            <label class="label">Password Login</label>
                            <input type="password" name="password" required placeholder="Minimal 4 karakter" class="input">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Nama Lengkap Karyawan/Member</label>
                            <input type="text" name="name" placeholder="Budi Santoso" class="input">
                        </div>
                        <div>
                            <label class="label">Profil Bandwidth (QoS)</label>
                            <select name="profile_id" class="input">
                                <option value="">Default Site Profile</option>
                                @foreach($profiles as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->rate_limit }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Maksimal Perangkat (shared-users)</label>
                            <input type="number" name="simultaneous_use" value="1" min="1" max="10" class="input">
                        </div>
                        <div>
                            <label class="label">Kunci MAC Address (Opsional)</label>
                            <input type="text" name="bound_mac" placeholder="XX:XX:XX:XX:XX:XX" class="input uppercase font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Berlaku Sampai (Opsional)</label>
                            <input type="date" name="expires_at" class="input">
                        </div>
                        <div>
                            <label class="label">Catatan / Divisi</label>
                            <input type="text" name="notes" placeholder="Staff IT / Tamu VIP" class="input">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary font-bold">Simpan Akun Member</button>
                    </div>
                </form>

                <!-- 4. FORM: WHATSAPP GUEST -->
                <form x-show="activeMethod === 'whatsapp'" action="{{ route('admin.hotspot-users.whatsapp') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Nomor WhatsApp (name MikroTik)</label>
                            <input type="text" name="phone" required placeholder="081234567890" class="input font-mono">
                            <p class="text-2xs text-slate-500 mt-1">Nomor HP yang akan digunakan saat login portal.</p>
                        </div>
                        <div>
                            <label class="label">Nama Lengkap Tamu</label>
                            <input type="text" name="name" required placeholder="Andi Wijaya" class="input">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Profil Bandwidth (QoS)</label>
                            <select name="profile_id" class="input">
                                <option value="">Default Site Profile</option>
                                @foreach($profiles as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->rate_limit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">PIN Verifikasi (Opsional)</label>
                            <input type="text" name="password" placeholder="Kosongkan untuk PIN otomatis" class="input font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Batas Waktu (limit-uptime)</label>
                            <select name="uptime_limit_hrs" class="input">
                                <option value="1">1 Jam</option>
                                <option value="2" selected>2 Jam</option>
                                <option value="4">4 Jam</option>
                                <option value="8">8 Jam</option>
                                <option value="24">24 Jam</option>
                                <option value="">Unlimited</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Kuota Data (MB)</label>
                            <input type="number" name="data_limit_mb" placeholder="1000" class="input">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Maksimal Perangkat</label>
                            <input type="number" name="simultaneous_use" value="1" min="1" max="10" class="input">
                        </div>
                        <div>
                            <label class="label">Catatan Tamu</label>
                            <input type="text" name="notes" placeholder="Tamu PT ABC / Meja 12" class="input">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary font-bold">Daftarkan Tamu WhatsApp</button>
                    </div>
                </form>

                <!-- 5. FORM: MAC WHITELIST / BYPASS -->
                <form x-show="activeMethod === 'mac_bypass'" action="{{ route('admin.hotspot-users.mac-bypass') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Hardware MAC Address (name & mac)</label>
                            <input type="text" name="mac_address" required placeholder="AA:BB:CC:DD:EE:FF" class="input uppercase font-mono font-bold">
                            <p class="text-2xs text-slate-500 mt-1">Perangkat langsung online tanpa halaman login.</p>
                        </div>
                        <div>
                            <label class="label">Nama Perangkat / Lokasi</label>
                            <input type="text" name="device_name" required placeholder="Smart TV Lobby / POS Kasir" class="input">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Kategori Perangkat</label>
                            <select name="device_category" class="input">
                                <option value="smart_tv">Smart TV / Android Box</option>
                                <option value="pos_cashier">Mesin Kasir POS / EDC</option>
                                <option value="printer">Printer Jaringan</option>
                                <option value="cctv">Kamera CCTV / NVR</option>
                                <option value="iot">Perangkat IoT / Sensor</option>
                                <option value="game_console">Konsol Game / PS5</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Profil Bandwidth (QoS)</label>
                            <select name="profile_id" class="input">
                                <option value="">Default Site Profile</option>
                                @foreach($profiles as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->rate_limit }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Durasi Aktif (Jam, Opsional)</label>
                            <input type="number" name="uptime_limit_hrs" placeholder="Kosongkan = Selamanya" class="input">
                        </div>
                        <div>
                            <label class="label">Catatan Tambahan</label>
                            <input type="text" name="notes" placeholder="IP Static 192.168.88.50" class="input">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary font-bold">Simpan MAC Whitelist</button>
                    </div>
                </form>

                <!-- 6. FORM: HOTEL ROOM -->
                <form x-show="activeMethod === 'hotel_room'" action="{{ route('admin.hotspot-users.hotel-room') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Nomor Kamar (name MikroTik)</label>
                            <input type="text" name="room_number" required placeholder="misal: 301 atau Deluxe-02" class="input font-mono font-bold">
                        </div>
                        <div>
                            <label class="label">Nama Belakang Tamu (password)</label>
                            <input type="text" name="last_name" required placeholder="misal: Santoso atau Smith" class="input uppercase font-bold">
                            <p class="text-2xs text-slate-500 mt-1">Digunakan tamu sebagai password login portal hotel.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Nama Lengkap Tamu (Opsional)</label>
                            <input type="text" name="full_name" placeholder="Budi Santoso" class="input">
                        </div>
                        <div>
                            <label class="label">Profil Bandwidth (QoS)</label>
                            <select name="profile_id" class="input">
                                <option value="">Default Site Profile</option>
                                @foreach($profiles as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->rate_limit }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Maksimal Perangkat Kamar</label>
                            <input type="number" name="simultaneous_use" value="4" min="1" max="10" class="input">
                        </div>
                        <div>
                            <label class="label">Tanggal Check-out</label>
                            <input type="date" name="checkout_date" class="input">
                        </div>
                    </div>

                    <div>
                        <label class="label">Catatan Reservasi</label>
                        <input type="text" name="notes" placeholder="Booking via Agoda / Extra Bed" class="input">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary font-bold">Simpan Akun Kamar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- ==================== MODAL PILIH BATCH CETAK ==================== -->
    <div x-show="printModal" x-cloak @keydown.escape.window="printModal = false" role="dialog" aria-modal="true" aria-labelledby="print-modal-title" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="printModal = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 id="print-modal-title" class="text-base font-extrabold text-slate-900">Pilih Batch Voucher untuk Dicetak</h3>
                <button type="button" @click="printModal = false" aria-label="Tutup dialog" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-4 space-y-2 max-h-60 overflow-y-auto">
                @foreach($batches as $b)
                <a href="{{ route('admin.hotspot-users.print', $b) }}" target="_blank"
                   class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-brand hover:bg-blue-50/50 transition">
                    <span class="font-bold text-slate-800 text-xs">{{ $b }}</span>
                    <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
