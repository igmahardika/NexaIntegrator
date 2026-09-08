@extends('layouts.admin')

@section('title', 'Bandwidth & QoS Profiles — Hotspot')
@section('page-title', 'Bandwidth & QoS Profiles')
@section('page-subtitle', 'Control rate-limits (Rx/Tx queues), client concurrency (shared-users), and edge router session timeouts')

@section('content')
<div x-data="{
    showAddModal: false,
    showEditModal: false,
    editData: {
        id: '',
        name: '',
        display_name: '',
        rate_limit: '',
        shared_users: 1,
        session_timeout: 120,
        idle_timeout: 15,
        keepalive_timeout: 5
    },
    openEdit(p) {
        this.editData = { ...p };
        this.showEditModal = true;
    },
    setPreset(name, limit, users, sess, idle) {
        document.getElementById('new_name').value = name;
        document.getElementById('new_display_name').value = name;
        document.getElementById('new_rate_limit').value = limit;
        document.getElementById('new_shared_users').value = users;
        document.getElementById('new_session_timeout').value = sess;
        document.getElementById('new_idle_timeout').value = idle;
    }
}" class="space-y-5">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-50 border border-blue-100 rounded-xl text-[#22449E]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Bandwidth & QoS Profiles</h1>
                    <p class="text-slate-500 text-xs mt-0.5">Control rate-limits (Rx/Tx queues), client concurrency (shared-users), and edge router session timeouts</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <!-- Site Context Selector -->
            @if($locations->count() > 1)
            <form method="GET" action="{{ route('admin.profiles.index') }}" class="flex items-center gap-2">
                <select name="location_id" onchange="this.form.submit()" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 font-semibold focus:outline-none focus:border-[#22449E]">
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ $currentLocation && $currentLocation->id === $loc->id ? 'selected' : '' }}>
                        📍 {{ $loc->name }}
                    </option>
                    @endforeach
                </select>
            </form>
            @endif

            <button @click="showAddModal = true" class="btn-primary flex items-center gap-2 text-xs py-2 px-4 shadow-sm font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add QoS Profile</span>
            </button>
        </div>
    </div>

    <!-- Active Site Banner -->
    @if($currentLocation)
    <div class="card p-4 bg-white border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-3 h-3 rounded-full {{ $currentLocation->router_ip ? 'bg-emerald-500 animate-pulse' : 'bg-amber-400' }}"></div>
            <div>
                <div class="text-xs font-bold text-slate-700">Active Site: <span class="text-slate-900 font-extrabold">{{ $currentLocation->name }}</span></div>
                <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                    Router Gateway: {{ $currentLocation->router_ip ?? 'Not configured' }} • API Port: {{ $currentLocation->router_port ?? 8728 }} • Hotspot Profile Sync: <span class="text-emerald-700 font-bold">Active</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-[#22449E] border border-blue-200">
                {{ $profiles->count() }} Configured Profiles
            </span>
        </div>
    </div>
    @endif

    <!-- Preset Badges Quick Info -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
        <div class="card p-3.5 border border-slate-200/80 bg-white flex items-center gap-3 shadow-xs">
            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#22449E] font-black text-xs">
                2M
            </div>
            <div>
                <div class="text-xs font-bold text-slate-900">Standard Guest</div>
                <div class="text-[11px] text-slate-500 font-mono">2M Rx / 2M Tx</div>
            </div>
        </div>
        <div class="card p-3.5 border border-slate-200/80 bg-white flex items-center gap-3 shadow-xs">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 font-black text-xs">
                5M
            </div>
            <div>
                <div class="text-xs font-bold text-slate-900">Cafe & Roastery</div>
                <div class="text-[11px] text-slate-500 font-mono">5M Rx / 5M Tx</div>
            </div>
        </div>
        <div class="card p-3.5 border border-slate-200/80 bg-white flex items-center gap-3 shadow-xs">
            <div class="w-9 h-9 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 font-black text-xs">
                10M
            </div>
            <div>
                <div class="text-xs font-bold text-slate-900">VIP / Premium</div>
                <div class="text-[11px] text-slate-500 font-mono">10M Rx / 10M Tx</div>
            </div>
        </div>
        <div class="card p-3.5 border border-slate-200/80 bg-white flex items-center gap-3 shadow-xs">
            <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 font-black text-xs">
                POS
            </div>
            <div>
                <div class="text-xs font-bold text-slate-900">Staff / POS Terminal</div>
                <div class="text-[11px] text-slate-500 font-mono">Priority Latency</div>
            </div>
        </div>
    </div>

    <!-- Profiles Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($profiles as $profile)
        <div class="card p-5 border border-slate-200/80 hover:border-slate-300 hover:shadow-md transition-all group flex flex-col justify-between relative overflow-hidden bg-white shadow-xs">
            <!-- Top Gradient Accent -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#22449E] via-blue-500 to-indigo-600"></div>

            <div>
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-extrabold text-slate-900">{{ $profile->name }}</h3>
                            @if($profile->synced_to_router)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Synchronized with MikroTik RouterOS">
                                ✓ Synced
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200" title="Pending Sync">
                                ⏳ Pending
                            </span>
                            @endif
                        </div>
                        <p class="text-slate-500 text-xs mt-0.5">{{ $profile->display_name ?: 'Hotspot QoS Profile' }}</p>
                    </div>

                    <div class="px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-100 text-[#22449E] font-mono font-extrabold text-xs tracking-wider">
                        ⚡ {{ $profile->rate_limit }}
                    </div>
                </div>

                <!-- Specs Grid -->
                <div class="grid grid-cols-2 gap-2.5 py-3 border-y border-slate-100 text-xs">
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <div class="text-slate-400 text-[10px] uppercase font-bold tracking-wider">Bandwidth Speed</div>
                        <div class="text-slate-900 font-mono font-bold mt-0.5">{{ $profile->rate_limit }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Rx (Up) / Tx (Down)</div>
                    </div>
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <div class="text-slate-400 text-[10px] uppercase font-bold tracking-wider">Shared Users</div>
                        <div class="text-slate-900 font-bold mt-0.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-[#22449E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            {{ $profile->shared_users }} {{ $profile->shared_users > 1 ? 'Devices' : 'Device (Unique)' }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Concurrency Tier</div>
                    </div>
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <div class="text-slate-400 text-[10px] uppercase font-bold tracking-wider">Session Timeout</div>
                        <div class="text-slate-900 font-bold mt-0.5 font-mono">
                            {{ $profile->session_timeout ? $profile->session_timeout . ' min' : 'Unlimited (0)' }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Maximum session duration</div>
                    </div>
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <div class="text-slate-400 text-[10px] uppercase font-bold tracking-wider">Idle Timeout</div>
                        <div class="text-slate-900 font-bold mt-0.5 font-mono">
                            {{ $profile->idle_timeout ? $profile->idle_timeout . ' min' : 'Unlimited (0)' }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Auto-kick on inactivity</div>
                    </div>
                </div>

                @if($profile->keepalive_timeout)
                <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-500 px-1 font-mono">
                    <span>Keepalive Timeout:</span>
                    <span class="text-slate-800 font-bold">{{ $profile->keepalive_timeout }} min</span>
                </div>
                @endif
            </div>

            <!-- Footer Action Buttons -->
            <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                <button @click="openEdit({{ json_encode($profile) }})" class="btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 font-semibold">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span>Edit Profile</span>
                </button>

                <form method="POST" action="{{ route('admin.profiles.destroy', $profile) }}" onsubmit="return confirm('Permanently delete hotspot QoS profile \'{{ $profile->name }}\'?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger text-xs py-1.5 px-3">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full card p-12 text-center bg-white border border-slate-200/80 shadow-xs">
            <div class="w-14 h-14 bg-blue-50 border border-blue-100 text-[#22449E] rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h3 class="text-base font-extrabold text-slate-900">No Bandwidth Profiles Configured</h3>
            <p class="text-slate-500 text-xs mt-1 max-w-md mx-auto">
                Create your first QoS profile to regulate speeds, concurrency, and sessions for guests, staff, or VIP visitors.
            </p>
            <button @click="showAddModal = true" class="btn-primary text-xs mt-4 shadow-sm font-semibold">
                + Create QoS Profile Now
            </button>
        </div>
        @endforelse
    </div>

    <!-- Modal Tambah Profil QoS -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="card w-full max-w-lg p-6 bg-white border border-slate-200 shadow-2xl rounded-2xl relative" @click.outside="showAddModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-blue-50 text-[#22449E] rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Add Bandwidth Profile (QoS)</h3>
                        <p class="text-slate-500 text-xs">Automated sync to MikroTik /ip hotspot user profile</p>
                    </div>
                </div>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold">&times;</button>
            </div>

            <!-- Quick Template Buttons -->
            <div class="mb-4">
                <div class="text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Apply Quick Presets:</div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="setPreset('Guest-2M', '2M/2M', 1, 120, 15)" class="px-2.5 py-1 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs border border-slate-200 font-semibold">
                        ⚡ 2M/2M (Guest)
                    </button>
                    <button type="button" @click="setPreset('Cafe-5M', '5M/5M', 2, 180, 20)" class="px-2.5 py-1 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs border border-slate-200 font-semibold">
                        ⚡ 5M/5M (Cafe)
                    </button>
                    <button type="button" @click="setPreset('VIP-10M', '10M/10M', 3, 360, 30)" class="px-2.5 py-1 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs border border-slate-200 font-semibold">
                        ⚡ 10M/10M (VIP)
                    </button>
                    <button type="button" @click="setPreset('Staff-Unlimited', '0/0', 5, 0, 0)" class="px-2.5 py-1 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs border border-slate-200 font-semibold">
                        ⚡ Uncapped (Staff)
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profiles.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="location_id" value="{{ $currentLocation ? $currentLocation->id : '' }}">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label text-slate-700 font-semibold">MikroTik Profile Identifier <span class="text-rose-500">*</span></label>
                        <input type="text" id="new_name" name="name" required placeholder="e.g. Guest-2M" class="input text-xs font-mono" pattern="[a-zA-Z0-9_\-]+" title="Alphanumeric, dash, and underscore only">
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold">Display Label</label>
                        <input type="text" id="new_display_name" name="display_name" placeholder="e.g. Regular Guest Tier" class="input text-xs">
                    </div>
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold">Speed Limit (Rate-Limit Rx/Tx) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="text" id="new_rate_limit" name="rate_limit" required placeholder="e.g. 2M/2M or 512k/1M" class="input font-mono text-xs">
                        <div class="absolute right-3 top-2.5 text-xs text-[#22449E] font-mono font-bold">Rx / Tx</div>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">Format: [Rx]/[Tx] e.g. <strong>2M/2M</strong> or <strong>10M/20M</strong>. Use <strong>0/0</strong> for uncapped bandwidth.</span>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="label text-slate-700 font-semibold">Shared Users <span class="text-rose-500">*</span></label>
                        <input type="number" id="new_shared_users" name="shared_users" value="1" min="1" max="500" required class="input text-xs">
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold">Session Timeout</label>
                        <input type="number" id="new_session_timeout" name="session_timeout" value="120" min="0" required class="input text-xs font-mono">
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold">Idle Timeout</label>
                        <input type="number" id="new_idle_timeout" name="idle_timeout" value="15" min="0" required class="input text-xs font-mono">
                    </div>
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold">Keepalive Timeout (Minutes)</label>
                    <input type="number" name="keepalive_timeout" value="5" min="0" class="input text-xs font-mono">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showAddModal = false" class="btn-secondary text-xs font-semibold">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-semibold shadow-sm">Save & Apply QoS</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Profil QoS -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="card w-full max-w-lg p-6 bg-white border border-slate-200 shadow-2xl rounded-2xl relative" @click.outside="showEditModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-blue-50 text-[#22449E] rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Edit Profile: <span x-text="editData.name" class="text-[#22449E] font-mono"></span></h3>
                        <p class="text-slate-500 text-xs">Updated parameters synchronize immediately with the edge router</p>
                    </div>
                </div>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold">&times;</button>
            </div>

            <form :action="'{{ url('/admin/profiles') }}/' + editData.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="label text-slate-700 font-semibold">Display Label</label>
                    <input type="text" name="display_name" x-model="editData.display_name" class="input text-xs">
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold">Speed Limit (Rate-Limit Rx/Tx) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="text" name="rate_limit" required x-model="editData.rate_limit" class="input font-mono text-xs">
                        <div class="absolute right-3 top-2.5 text-xs text-[#22449E] font-mono font-bold">Rx / Tx</div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="label text-slate-700 font-semibold">Shared Users <span class="text-rose-500">*</span></label>
                        <input type="number" name="shared_users" x-model="editData.shared_users" min="1" max="500" required class="input text-xs font-mono">
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold">Session Timeout</label>
                        <input type="number" name="session_timeout" x-model="editData.session_timeout" min="0" required class="input text-xs font-mono">
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold">Idle Timeout</label>
                        <input type="number" name="idle_timeout" x-model="editData.idle_timeout" min="0" required class="input text-xs font-mono">
                    </div>
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold">Keepalive Timeout (Minutes)</label>
                    <input type="number" name="keepalive_timeout" x-model="editData.keepalive_timeout" min="0" class="input text-xs font-mono">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showEditModal = false" class="btn-secondary text-xs font-semibold">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-semibold shadow-sm">Update Profile</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
