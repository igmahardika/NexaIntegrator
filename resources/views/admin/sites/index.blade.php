@extends('layouts.admin')
@section('title', 'Sites & Customers')
@section('page-title', 'Sites & Customer Management')
@section('page-subtitle', 'Manage branches, customer profiles, active templates, and edge router connections across locations')

@section('content')
<div x-data="sitesManager()">

    <!-- Top Action & Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-brand">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Total Sites</div>
                <div class="text-2xl font-black text-slate-900">{{ $sites->count() }}</div>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Active Sites</div>
                <div class="text-2xl font-black text-emerald-600">{{ $sites->where('is_active', true)->count() }}</div>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Connected Clients</div>
                <div class="text-2xl font-black text-cyan-600">{{ $sites->sum('active_sessions_count') }}</div>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/>
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Login Methods</div>
                <div class="text-2xl font-black text-purple-600">{{ count($templates) }} Methods</div>
            </div>
        </div>
    </div>

    <!-- Filter & Add Button Bar -->
    <div class="card p-4 mb-6 flex flex-col sm:flex-row gap-3 items-center justify-between bg-white border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.sites.index') }}" class="flex flex-wrap gap-2 items-center w-full sm:w-auto">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search site, slug, or customer..."
                class="input input-sm w-full sm:w-64"
            >
            <select name="business_type" class="input input-sm w-full sm:w-40" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="cafe" {{ request('business_type') === 'cafe' ? 'selected' : '' }}>Cafe / Restaurant</option>
                <option value="hotel" {{ request('business_type') === 'hotel' ? 'selected' : '' }}>Hotel / Resort</option>
                <option value="retail" {{ request('business_type') === 'retail' ? 'selected' : '' }}>Retail / Store</option>
                <option value="coworking" {{ request('business_type') === 'coworking' ? 'selected' : '' }}>Co-Working</option>
                <option value="office" {{ request('business_type') === 'office' ? 'selected' : '' }}>Corporate Office</option>
                <option value="other" {{ request('business_type') === 'other' ? 'selected' : '' }}>Other</option>
            </select>
            <button type="submit" class="btn-secondary text-xs font-semibold">Filter</button>
            @if(request()->hasAny(['search', 'business_type']))
            <a href="{{ route('admin.sites.index') }}" class="text-xs text-slate-500 hover:text-slate-900 underline py-2 font-medium">Reset</a>
            @endif
        </form>

        <a href="{{ route('admin.sites.create') }}" class="btn-primary flex items-center gap-2 w-full sm:w-auto justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Add New Site</span>
        </a>
    </div>

    <!-- Sites Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($sites as $site)
        <div class="card p-5 flex flex-col justify-between bg-white border border-slate-200/80 shadow-xs hover:border-brand/40 hover:shadow-md transition-all duration-200">
            <div>
                <!-- Header: Name & Status -->
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap mb-1">
                            <h3 class="font-bold text-slate-900 text-base leading-tight">{{ $site->name }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-semibold {{ $site->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                {{ $site->is_active ? 'Active' : 'Disabled' }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-semibold {{ $site->gateway_mode === 'zero_tunnel' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($site->gateway_mode === 'direct_api' ? 'bg-blue-50 text-brand border border-blue-200' : 'bg-purple-50 text-purple-800 border border-purple-200') }}">
                                {{ $site->gateway_mode_label }}
                            </span>
                        </div>
                        <p class="text-xs text-brand font-medium flex items-center gap-1">
                            <span class="text-slate-500 font-normal">Customer:</span>
                            <span class="text-slate-800 font-semibold">{{ $site->customer_name ?: 'Internal Deployment' }}</span>
                        </p>
                    </div>

                    <!-- Business Type Badge -->
                    <span class="text-2xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-slate-100 border border-slate-200 text-slate-700">
                        {{ $site->business_type }}
                    </span>
                </div>

                <!-- Address & Portal URL -->
                <div class="text-xs text-slate-600 mb-4 space-y-1 bg-slate-50 p-3 rounded-xl border border-slate-200/60">
                    <div class="truncate text-slate-700">📍 {{ $site->address ?: 'Physical address not specified' }}</div>
                    <div class="flex items-center gap-2 pt-1">
                        <span class="text-slate-500 font-medium">Portal Endpoint:</span>
                        <a href="{{ route('portal', ['loc' => $site->slug]) }}" target="_blank" class="text-brand hover:text-brand-hover font-mono truncate hover:underline font-semibold">
                            /portal?loc={{ $site->slug }}
                        </a>
                    </div>
                </div>

                <!-- Template & Network Highlights -->
                <div class="grid grid-cols-2 gap-2 text-xs mb-4">
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <div class="text-2xs text-slate-500 uppercase tracking-wider mb-0.5 font-bold">Active Template</div>
                        <div class="font-bold text-slate-900 capitalize flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-brand"></span>
                            {{ str_replace('-', ' ', $site->active_template ?? 'username-password') }}
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <div class="text-2xs text-slate-500 uppercase tracking-wider mb-0.5 font-bold">Router Gateway</div>
                        <div class="font-bold text-slate-900 truncate font-mono">
                            {{ $site->router_ip ?: ($site->radius_server_ip ?: 'Not configured') }}
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-3 gap-2 py-3 border-t border-b border-slate-100 text-center text-xs mb-4 bg-slate-50/60 rounded-xl">
                    <div>
                        <div class="text-slate-500 text-2xs font-bold uppercase tracking-wider">Online</div>
                        <div class="font-black text-brand text-sm mt-0.5">{{ $site->active_sessions_count }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-2xs font-bold uppercase tracking-wider">Vouchers</div>
                        <div class="font-black text-slate-900 text-sm mt-0.5">{{ $site->vouchers_count }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-2xs font-bold uppercase tracking-wider">Total Sessions</div>
                        <div class="font-black text-slate-900 text-sm mt-0.5">{{ $site->sessions_count }}</div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Footer -->
            <div class="space-y-2 pt-2">
                <!-- Management Hub Links -->
                <div class="grid grid-cols-4 gap-1.5">
                    <a href="{{ route('admin.sites.template.gallery', $site) }}" class="btn-secondary text-2xs py-2 px-1 text-center justify-center flex items-center gap-1 hover:border-brand/40 hover:text-brand font-semibold">
                        <svg class="w-3.5 h-3.5 text-brand shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                        <span class="truncate">Template</span>
                    </a>
                    <a href="{{ route('admin.sites.provision', $site) }}" class="btn-secondary text-2xs py-2 px-1 text-center justify-center flex items-center gap-1 hover:border-emerald-300 hover:text-emerald-700 font-semibold" title="Lihat Script WinBox & Unduh login.html">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="truncate">Script</span>
                    </a>
                    <a href="{{ route('admin.sites.radius.show', $site) }}" class="btn-secondary text-2xs py-2 px-1 text-center justify-center flex items-center gap-1 hover:border-purple-300 hover:text-purple-700 font-semibold">
                        <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="truncate">RADIUS</span>
                    </a>
                    <a href="{{ route('admin.devices.index', ['location_id' => $site->id]) }}" class="btn-secondary text-2xs py-2 px-1 text-center justify-center flex items-center gap-1 hover:border-cyan-300 hover:text-cyan-700 font-semibold">
                        <svg class="w-3.5 h-3.5 text-cyan-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span class="truncate">Devices</span>
                    </a>
                </div>

                <!-- Operational Controls -->
                <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                    <form method="POST" action="{{ route('admin.sites.toggle', $site) }}">
                        @csrf
                        <button type="submit" class="text-xs text-slate-600 hover:text-slate-900 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 font-semibold transition-colors">
                            {{ $site->is_active ? 'Disable' : 'Enable' }}
                        </button>
                    </form>

                    <div class="flex items-center gap-2">
                        <button @click="openModal('edit', {{ $site->toJson() }})" class="text-xs text-brand hover:text-brand-hover px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 font-semibold transition-colors">
                            Edit
                        </button>

                        <form method="POST" action="{{ route('admin.sites.destroy', $site) }}" onsubmit="return confirm('Permanently delete this site and associated device sessions?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-600 hover:text-rose-700 px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 font-semibold transition-colors">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card p-12 text-center col-span-full bg-white border border-slate-200/80 shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-4 text-brand">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h4 class="text-lg font-bold text-slate-900 mb-1">No Sites or Customer Deployments Found</h4>
            <p class="text-xs text-slate-500 mb-6">Begin by deploying your first site branch or hotspot customer location.</p>
            <button @click="openModal('create')" class="btn-primary text-xs mx-auto font-semibold">
                + Add First Site
            </button>
        </div>
        @endforelse
    </div>

    <!-- ==================== MODAL: ADD / EDIT SITE ==================== -->
    <div
        x-show="modalOpen"
        x-cloak
        role="dialog"
        aria-modal="true"
        aria-labelledby="site-modal-title"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        @keydown.escape.window="modalOpen = false"
    >
        <div
            @click.outside="modalOpen = false"
            class="card max-w-2xl w-full p-6 bg-white border border-slate-200 shadow-2xl rounded-2xl max-h-[90vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                <h3 id="site-modal-title" class="text-base font-bold text-slate-900" x-text="isEdit ? 'Edit Site & Customer Profile' : 'Add New Site / Customer'"></h3>
                <button type="button" @click="modalOpen = false" aria-label="Tutup dialog" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="isEdit ? `/admin/sites/${currentSite.id}` : '{{ route('admin.sites.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label text-slate-700 font-semibold">Site / Branch Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="form.name" required class="input" placeholder="e.g. Downtown Cafe & Roastery">
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold">Customer / Company Name</label>
                        <input type="text" name="customer_name" x-model="form.customer_name" class="input" placeholder="e.g. PT Kopi Nusantara">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="label text-slate-700 font-semibold">Business Category <span class="text-rose-500">*</span></label>
                        <select name="business_type" x-model="form.business_type" class="input" required>
                            <option value="cafe">Cafe / Restaurant</option>
                            <option value="hotel">Hotel / Resort</option>
                            <option value="retail">Retail / Store</option>
                            <option value="coworking">Co-Working Space</option>
                            <option value="office">Corporate Office</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold">PIC Email</label>
                        <input type="email" name="contact_email" x-model="form.contact_email" class="input" placeholder="manager@site.com">
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold">Phone / WhatsApp</label>
                        <input type="text" name="contact_phone" x-model="form.contact_phone" class="input" placeholder="+62 812-3456-7890">
                    </div>
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold">Full Physical Address</label>
                    <textarea name="address" x-model="form.address" rows="2" class="input" placeholder="e.g. Jl. Senopati No. 10, Kebayoran Baru..."></textarea>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="text-xs font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                        </svg>
                        <span>Edge Router & Gateway Settings</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="label text-slate-700 font-semibold mb-1 block">Router IP</label>
                            <input type="text" name="router_ip" x-model="form.router_ip" class="input input-sm font-mono" placeholder="192.168.88.1">
                        </div>
                        <div>
                            <label class="label text-slate-700 font-semibold mb-1 block">API Port</label>
                            <input type="number" name="router_port" x-model="form.router_port" class="input input-sm font-mono" placeholder="8728">
                        </div>
                        <div>
                            <label class="label text-slate-700 font-semibold mb-1 block">Hotspot DNS Hostname</label>
                            <input type="text" name="dns_name" x-model="form.dns_name" class="input input-sm font-mono" placeholder="wifi.login">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="label text-slate-700 font-semibold mb-1 block">API User</label>
                            <input type="text" name="router_user" x-model="form.router_user" class="input input-sm font-mono" placeholder="admin">
                        </div>
                        <div>
                            <label class="label text-slate-700 font-semibold mb-1 block">API Password</label>
                            <input type="password" name="router_password" x-model="form.router_password" class="input input-sm font-mono" :placeholder="isEdit ? '(Leave empty to keep existing)' : '••••••••'">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Default Login Method</label>
                        <select name="active_template" x-model="form.active_template" class="input input-sm">
                            <option value="username-password">Username & Password</option>
                            <option value="access-code">Access Code (Voucher)</option>
                            <option value="whatsapp-login">WhatsApp Login</option>
                            <option value="question">Question & Rating Survey</option>
                            <option value="button">1-Click Free Access Button</option>
                            <option value="email">Email Lead Capture</option>
                        </select>
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Device Capacity Limit</label>
                        <input type="number" name="max_active_devices" x-model="form.max_active_devices" class="input input-sm bg-white border-slate-200 text-slate-900 w-full" placeholder="100">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" value="1" id="is_active_cb" x-model="form.is_active" class="accent-brand w-4 h-4 rounded">
                    <label for="is_active_cb" class="text-xs text-slate-700 font-medium">Activate site to accept guest device logins</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="btn-secondary text-xs font-semibold">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-semibold">
                        <span x-text="isEdit ? 'Save Changes' : 'Create Site'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
function sitesManager() {
    return {
        modalOpen: false,
        isEdit: false,
        currentSite: {},
        form: {
            name: '',
            customer_name: '',
            business_type: 'cafe',
            contact_email: '',
            contact_phone: '',
            address: '',
            router_ip: '192.168.88.1',
            router_port: 8728,
            router_user: 'admin',
            router_password: '',
            dns_name: 'wifi.login',
            active_template: 'username-password',
            max_active_devices: 100,
            is_active: true,
        },
        openModal(mode, site = null) {
            this.isEdit = (mode === 'edit');
            if (this.isEdit && site) {
                this.currentSite = site;
                this.form = {
                    name: site.name || '',
                    customer_name: site.customer_name || '',
                    business_type: site.business_type || 'cafe',
                    contact_email: site.contact_email || '',
                    contact_phone: site.contact_phone || '',
                    address: site.address || '',
                    router_ip: site.router_ip || '192.168.88.1',
                    router_port: site.router_port || 8728,
                    router_user: site.router_user || 'admin',
                    router_password: '',
                    dns_name: site.dns_name || 'wifi.login',
                    active_template: site.active_template || 'username-password',
                    max_active_devices: site.max_active_devices || 100,
                    is_active: Boolean(site.is_active),
                };
            } else {
                this.currentSite = {};
                this.form = {
                    name: '',
                    customer_name: '',
                    business_type: 'cafe',
                    contact_email: '',
                    contact_phone: '',
                    address: '',
                    router_ip: '192.168.88.1',
                    router_port: 8728,
                    router_user: 'admin',
                    router_password: '',
                    dns_name: 'wifi.login',
                    active_template: 'username-password',
                    max_active_devices: 100,
                    is_active: true,
                };
            }
            this.modalOpen = true;
        }
    };
}
</script>
@endsection
