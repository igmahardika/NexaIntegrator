@extends('layouts.admin')

@section('title', 'Hotspot Users - ' . ($currentSite->name ?? 'Site'))

@section('content')
<div class="space-y-6" x-data="{ accessCodeModal: false, generateModal: false, memberModal: false, printModal: false }">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <span>{{ $currentSite->name ?? 'Site' }}</span>
                <span>/</span>
                <span class="text-brand">Hotspot Users</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen User Hotspot & Access Code</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola Access Code untuk tamu kantor, voucher massal, akun member, dan data leads login.</p>
        </div>

        <!-- Adaptive Actions -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <button @click="accessCodeModal = true" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                + Tambah Access Code
            </button>

            @if(in_array('voucher', $enabledMethods))
            <button @click="generateModal = true" class="btn-secondary">
                <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Generate Voucher Massal
            </button>
            @endif

            @if(in_array('member', $enabledMethods))
            <button @click="memberModal = true" class="btn-secondary">
                <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Buat Akun Member
            </button>
            @endif

            @if($batches->isNotEmpty())
            <button @click="printModal = true" class="btn-secondary">
                <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Voucher
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
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
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
                <p class="text-2xs font-bold uppercase tracking-wider text-slate-500">Vouchers</p>
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
                <p class="text-2xs font-bold uppercase tracking-wider text-slate-500">Guest Leads</p>
                <p class="text-xl font-extrabold text-emerald-600 mt-1">{{ number_format($stats['leads']) }}</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="kpi-card">
            <div>
                <p class="text-2xs font-bold uppercase tracking-wider text-slate-500">Ready / Siap</p>
                <p class="text-xl font-extrabold text-amber-600 mt-1">{{ number_format($stats['ready']) }}</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
            
            <!-- Segmented Tabs -->
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl w-full md:w-auto overflow-x-auto">
                <a href="{{ route('admin.hotspot-users.index', ['tab' => 'all']) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $tab === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua User ({{ $stats['total'] }})
                </a>
                <a href="{{ route('admin.hotspot-users.index', ['tab' => 'voucher']) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $tab === 'voucher' ? 'bg-white text-brand shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Access Code / Vouchers ({{ $stats['vouchers'] }})
                </a>
                <a href="{{ route('admin.hotspot-users.index', ['tab' => 'member']) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $tab === 'member' ? 'bg-white text-cyan-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Members ({{ $stats['members'] }})
                </a>
                <a href="{{ route('admin.hotspot-users.index', ['tab' => 'leads']) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $tab === 'leads' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Guest Leads ({{ $stats['leads'] }})
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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode/user/nama..."
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

        <!-- Users Table -->
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="table-th">Identitas / Kode</th>
                        <th class="table-th">Metode Login</th>
                        <th class="table-th">Profil QoS</th>
                        <th class="table-th">Status</th>
                        <th class="table-th">Waktu / Kuota</th>
                        <th class="table-th">Info Tamu</th>
                        <th class="table-th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($users as $user)
                    <tr class="table-row">
                        <!-- Identitas -->
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-slate-900 select-all">{{ $user->identifier }}</span>
                                <button type="button" @click="navigator.clipboard.writeText('{{ $user->identifier }}'); alert('Tersalin: {{ $user->identifier }}')"
                                        class="text-slate-400 hover:text-brand" title="Salin Kode">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                            </div>
                            @if($user->batch_name)
                            <span class="text-2xs text-slate-500 block mt-0.5">Batch: {{ $user->batch_name }}</span>
                            @endif
                        </td>

                        <!-- Metode Login -->
                        <td class="py-3 px-3">
                            @if($user->auth_method === 'voucher')
                                @if(($user->guest_metadata['type'] ?? '') === 'custom_access_code' || $user->batch_name === 'Access Code')
                                    <span class="badge bg-amber-50 text-amber-800 border border-amber-200 font-bold">Access Code</span>
                                @else
                                    <span class="badge bg-indigo-50 text-indigo-700 border border-indigo-200">Voucher</span>
                                @endif
                            @elseif($user->auth_method === 'member')
                                <span class="badge bg-cyan-50 text-cyan-700 border border-cyan-200">Member</span>
                            @elseif($user->auth_method === 'whatsapp')
                                <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200">WhatsApp</span>
                            @elseif($user->auth_method === 'survey')
                                <span class="badge bg-purple-50 text-purple-700 border border-purple-200">Survei</span>
                            @elseif($user->auth_method === 'quick_click')
                                <span class="badge bg-amber-50 text-amber-700 border border-amber-200">1-Click</span>
                            @else
                                <span class="badge bg-slate-100 text-slate-700">{{ $user->auth_method }}</span>
                            @endif
                        </td>

                        <!-- Profil QoS -->
                        <td class="py-3 px-3">
                            @if($user->profile)
                            <div class="font-semibold text-slate-800">{{ $user->profile->name }}</div>
                            <div class="text-2xs text-slate-500 font-mono">{{ $user->profile->rate_limit }}</div>
                            @else
                            <span class="text-slate-400 italic">Default</span>
                            @endif
                        </td>

                        <!-- Status -->
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

                        <!-- Waktu / Kuota -->
                        <td class="py-3 px-3 text-slate-700">
                            <div>
                                <span class="font-semibold">{{ gmdate('H:i:s', $user->used_uptime) }}</span>
                                @if($user->uptime_limit)
                                <span class="text-slate-500 text-2xs">/ {{ gmdate('H:i:s', $user->uptime_limit) }}</span>
                                @else
                                <span class="text-slate-400 text-2xs">/ Unlimited</span>
                                @endif
                            </div>
                            <div class="text-2xs text-slate-500">
                                {{ round(($user->bytes_in + $user->bytes_out) / 1048576, 1) }} MB
                            </div>
                        </td>

                        <!-- Info Tamu / Metadata -->
                        <td class="py-3 px-3">
                            @if(!empty($user->guest_metadata))
                                @if(isset($user->guest_metadata['notes']))
                                <div class="font-semibold text-slate-800 text-xs">{{ $user->guest_metadata['notes'] }}</div>
                                @endif
                                @if(isset($user->guest_metadata['full_name']))
                                <div class="font-semibold text-slate-800">{{ $user->guest_metadata['full_name'] }}</div>
                                @endif
                                @if(isset($user->guest_metadata['phone']))
                                <div class="text-2xs text-slate-500 font-mono">{{ $user->guest_metadata['phone'] }}</div>
                                @endif
                                @if(isset($user->guest_metadata['created_by']))
                                <div class="text-2xs text-slate-500">Oleh: {{ $user->guest_metadata['created_by'] }}</div>
                                @endif
                            @else
                            <span class="text-slate-500 italic text-2xs">-</span>
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
                            <p class="font-bold text-slate-700 text-sm">Belum ada user hotspot</p>
                            <p class="text-xs text-slate-500 mt-0.5">Generate voucher atau buat akun member pertama untuk site ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>

    <!-- ==================== MODAL TAMBAH ACCESS CODE ==================== -->
    <div x-show="accessCodeModal" x-cloak @keydown.escape.window="accessCodeModal = false" role="dialog" aria-modal="true" aria-labelledby="access-code-modal-title" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="accessCodeModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-brand/10 text-brand flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 id="access-code-modal-title" class="text-base font-extrabold text-slate-900">Tambah Access Code</h3>
                        <p class="text-2xs text-slate-500">Kode akses khusus login tamu / kantor</p>
                    </div>
                </div>
                <button type="button" @click="accessCodeModal = false" aria-label="Tutup dialog" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.hotspot-users.access-code') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="label">Kode Akses (Access Code)</label>
                    <input type="text" name="code" placeholder="Contoh: KANTOR2026 atau TAMU-VIP" required autofocus
                           class="input uppercase font-mono tracking-wider font-bold"
                           oninput="this.value = this.value.toUpperCase()">
                    <p class="text-2xs text-slate-500 mt-1">Kode yang akan diinputkan pengguna pada captive portal (Template Access Code).</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Batas Waktu (Durasi)</label>
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
                        <label class="label">Maksimal Perangkat</label>
                        <select name="simultaneous_use" class="input">
                            <option value="1" selected>1 Perangkat (Personal)</option>
                            <option value="2">2 Perangkat</option>
                            <option value="5">5 Perangkat</option>
                            <option value="10">10 Perangkat (Rapat)</option>
                            <option value="25">25 Perangkat (Workshop)</option>
                            <option value="50">50 Perangkat (Event/Kantor)</option>
                            <option value="100">100 Perangkat</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="label">Profil Bandwidth (Speed Limit)</label>
                    <select name="profile_id" class="input">
                        <option value="">Default Site Profile</option>
                        @foreach($profiles as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->rate_limit }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label">Catatan / Keterangan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Contoh: Tamu Ruang Rapat / Acara Seminar" class="input text-xs">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="accessCodeModal = false" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Access Code</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL GENERATE VOUCHERS ==================== -->
    <div x-show="generateModal" x-cloak @keydown.escape.window="generateModal = false" role="dialog" aria-modal="true" aria-labelledby="generate-modal-title" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="generateModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 id="generate-modal-title" class="text-base font-extrabold text-slate-900">Generate Voucher Massal</h3>
                <button type="button" @click="generateModal = false" aria-label="Tutup dialog" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.hotspot-users.generate') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="label">Jumlah Voucher</label>
                    <input type="number" name="quantity" value="20" min="1" max="500" required class="input">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Panjang Kode</label>
                        <input type="number" name="code_length" value="6" min="4" max="10" required class="input">
                    </div>
                    <div>
                        <label class="label">Prefix (Opsional)</label>
                        <input type="text" name="prefix" placeholder="misal: VIP" class="input uppercase">
                    </div>
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

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Batas Waktu (Jam)</label>
                        <input type="number" step="0.5" name="uptime_limit_hrs" placeholder="2.0" class="input">
                    </div>
                    <div>
                        <label class="label">Kuota Data (MB)</label>
                        <input type="number" name="data_limit_mb" placeholder="misal: 1000" class="input">
                    </div>
                </div>

                <div>
                    <label class="label">Nama Batch</label>
                    <input type="text" name="batch_name" value="Batch-{{ date('Ymd-Hi') }}" class="input">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="generateModal = false" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary">Generate Sekarang</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL BUAT AKUN MEMBER ==================== -->
    <div x-show="memberModal" x-cloak @keydown.escape.window="memberModal = false" role="dialog" aria-modal="true" aria-labelledby="member-modal-title" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="memberModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 id="member-modal-title" class="text-base font-extrabold text-slate-900">Buat Akun Member / Staff</h3>
                <button type="button" @click="memberModal = false" aria-label="Tutup dialog" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.hotspot-users.member') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="label">Nama Lengkap Tamu/Karyawan</label>
                    <input type="text" name="name" placeholder="misal: Budi Santoso" class="input">
                </div>

                <div>
                    <label class="label">Username Login</label>
                    <input type="text" name="username" required placeholder="misal: budi_hotel" class="input">
                </div>

                <div>
                    <label class="label">Password</label>
                    <input type="password" name="password" required placeholder="Minimal 4 karakter" class="input">
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

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Maksimal Perangkat</label>
                        <input type="number" name="simultaneous_use" value="1" min="1" max="10" class="input">
                    </div>
                    <div>
                        <label class="label">Berlaku Sampai (Opsional)</label>
                        <input type="date" name="expires_at" class="input">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="memberModal = false" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL PILIH BATCH CETAK ==================== -->
    <div x-show="printModal" x-cloak @keydown.escape.window="printModal = false" role="dialog" aria-modal="true" aria-labelledby="print-modal-title" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="printModal = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 id="print-modal-title" class="text-base font-extrabold text-slate-900">Pilih Batch Voucher untuk Dicetak</h3>
                <button type="button" @click="printModal = false" aria-label="Tutup dialog" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none transition-colors">
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
