@extends('layouts.admin')
@section('title', 'Template Studio — ' . $site->name)
@section('page-title', 'Captive Portal Template Studio')
@section('page-subtitle', 'Select, customize content, and test live multi-device preview for ' . $site->name)

@section('content')
<div x-data="templateGalleryManager()" class="space-y-6">

    <!-- Toast Notification -->
    <div
        x-show="notification"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        x-cloak
        class="fixed top-5 right-5 z-[9999] flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-600 text-white text-xs font-semibold shadow-xl"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span x-text="notification"></span>
    </div>

    <!-- Header Card: Current Site Status & Actions -->
    <div class="card p-6 bg-white border border-slate-200/80 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Site / Location:</span>
                    <h2 class="text-xl font-extrabold text-slate-900">{{ $site->name }}</h2>
                    <span class="badge bg-blue-50 text-brand border border-blue-100">
                        {{ $site->customer_name ?: 'Internal Tenant' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500">
                    Active login method:
                    <span class="font-bold text-brand capitalize" x-text="formatTemplateName(activeTemplate)">
                        {{ str_replace('-', ' ', $activeTemplateId) }}
                    </span>
                    · Available login methods: <span class="font-semibold text-slate-700">{{ count($templates) }} Templates</span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.sites.index') }}" class="btn-secondary text-xs py-2 px-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Back to Sites</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Templates Grid (The 6 Login Method Templates) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($templates as $key => $tmpl)
        <div
            class="card p-6 flex flex-col justify-between bg-white border transition-all duration-200 shadow-xs"
            :class="activeTemplate === '{{ $key }}' ? 'border-brand ring-2 ring-brand/20 shadow-md' : 'border-slate-200/80 hover:border-slate-300 hover:shadow-sm'"
        >
            <div>
                <!-- Template Card Header -->
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-base font-bold text-slate-900">{{ $tmpl['name'] }}</h3>
                            <span
                                class="text-2xs font-bold px-2 py-0.5 rounded-full transition-colors"
                                :class="activeTemplate === '{{ $key }}' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600'"
                                x-text="activeTemplate === '{{ $key }}' ? '✓ Currently Active' : '{{ $tmpl['preview_badge'] }}'"
                            >
                                {{ $key === $activeTemplateId ? '✓ Currently Active' : $tmpl['preview_badge'] }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">{{ $tmpl['category'] }}</p>
                    </div>

                    <div class="w-7 h-7 rounded-full border-2 border-white flex-shrink-0 shadow-sm" style="background-color: {{ $tmpl['accent'] }};"></div>
                </div>

                <!-- Visual Mockup Card (Dedicated 2-Column Nexa Hotspot Structure) -->
                @php
                    $bgVal = trim($tmpl['default_config']['bg_value'] ?? '');
                    $bgIsImg = str_starts_with($bgVal, '/') || str_starts_with($bgVal, 'http') || str_starts_with($bgVal, 'data:') || (!str_contains($bgVal, 'gradient') && !str_starts_with($bgVal, '#') && !str_starts_with($bgVal, 'rgb'));
                    $bgCardStyle = $bgIsImg ? "background: #0f172a url('{$bgVal}') center/cover no-repeat;" : "background: {$bgVal};";
                @endphp
                <div class="p-3 sm:p-4 rounded-xl border border-slate-200/80 mb-4 text-center overflow-hidden relative shadow-inner flex items-center justify-center" style="{{ $bgCardStyle }} min-height: 200px;">
                    <!-- 2-Column Nexa Card Architecture (Brand & Input Left + Promo Carousel Right) -->
                    <div class="w-full max-w-[340px] rounded-xl border border-white/25 shadow-2xl overflow-hidden grid grid-cols-12 text-left bg-white" style="box-shadow: 0 10px 25px -5px rgba(0,0,0,0.35);">
                        <!-- Left Column: Brand Logo + Method Form (7 cols) -->
                        <div class="col-span-7 p-3 flex flex-col justify-between bg-white border-r border-slate-100">
                            <!-- Mini Brand Bar -->
                            <div class="flex items-center gap-1.5 mb-2 pb-1.5 border-b border-slate-100">
                                <div class="w-5 h-5 rounded-lg flex items-center justify-center text-xs text-white font-bold flex-shrink-0" style="background: {{ $tmpl['default_config']['primary_color'] }};">
                                    W
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-extrabold text-slate-900 truncate leading-tight">{{ $tmpl['default_config']['brand_name'] }}</div>
                                    <div class="text-2xs text-slate-500 truncate">{{ $tmpl['default_config']['brand_tagline'] }}</div>
                                </div>
                            </div>

                            <!-- Method-Specific Simulated Pill Form -->
                            @if($key === 'username-password')
                            <!-- 1. Username & Password Mini Pill Form -->
                            <div class="space-y-1 mb-2">
                                <div class="bg-slate-50 border border-slate-200 rounded-full px-2 py-0.5 text-2xs text-slate-600 flex items-center gap-1 truncate">
                                    <svg class="w-2.5 h-2.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="truncate">username</span>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 rounded-full px-2 py-0.5 text-2xs text-slate-600 flex items-center gap-1 truncate">
                                    <svg class="w-2.5 h-2.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span class="truncate">••••••••</span>
                                </div>
                            </div>
                            <div class="w-full py-1 rounded-full text-center text-2xs font-bold text-white shadow-xs" style="background: {{ $tmpl['default_config']['primary_color'] }};">
                                Connect &rarr;
                            </div>

                            @elseif($key === 'access-code')
                            <!-- 2. Access Code / Voucher Mini Pill Form -->
                            <div class="space-y-1 mb-2">
                                <div class="bg-slate-50 border border-dashed border-sky-300 rounded-full py-1 px-2 text-center text-xs font-mono font-extrabold text-sky-700 tracking-wider">
                                    VC-8921
                                </div>
                            </div>
                            <div class="w-full py-1 rounded-full text-center text-2xs font-bold text-white shadow-xs" style="background: {{ $tmpl['default_config']['primary_color'] }};">
                                Connect &rarr;
                            </div>

                            @elseif($key === 'whatsapp-login')
                            <!-- 3. WhatsApp Login Mini Pill Form -->
                            <div class="space-y-1 mb-2">
                                <div class="bg-slate-50 border border-slate-200 rounded-full px-2 py-0.5 text-2xs text-slate-600 flex items-center gap-1">
                                    <span class="text-emerald-600 font-bold">+62</span>
                                    <span class="truncate">812-3456...</span>
                                </div>
                            </div>
                            <div class="w-full py-1 rounded-full text-center text-2xs font-bold text-white shadow-xs flex items-center justify-center gap-1" style="background: #16a34a;">
                                <span>WhatsApp &rarr;</span>
                            </div>

                            @elseif($key === 'question')
                            <!-- 4. Question & Survey Mini Pill Form -->
                            <div class="space-y-1 mb-2">
                                <div class="text-2xs text-slate-700 font-semibold truncate">Rate Service</div>
                                <div class="flex gap-1 justify-center">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-2xs text-slate-600 font-bold">1</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-2xs text-slate-600 font-bold">3</span>
                                    <span class="px-1.5 py-0.5 rounded text-2xs text-white font-bold" style="background: {{ $tmpl['default_config']['primary_color'] }};">5</span>
                                </div>
                            </div>
                            <div class="w-full py-1 rounded-full text-center text-2xs font-bold text-white shadow-xs" style="background: {{ $tmpl['default_config']['primary_color'] }};">
                                Submit &rarr;
                            </div>

                            @elseif($key === 'button')
                            <!-- 5. 1-Click Button Mini Form -->
                            <div class="text-center py-1 mb-1">
                                <div class="inline-flex p-1 rounded-full bg-blue-50 text-brand mb-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div class="text-2xs text-slate-500 font-medium">Free Access</div>
                            </div>
                            <div class="w-full py-1 rounded-full text-center text-2xs font-bold text-white shadow-xs" style="background: {{ $tmpl['default_config']['primary_color'] }};">
                                Connect Now &rarr;
                            </div>

                            @elseif($key === 'email')
                            <!-- 6. Email Login Mini Form -->
                            <div class="space-y-1 mb-2">
                                <div class="bg-slate-50 border border-slate-200 rounded-full px-2 py-0.5 text-2xs text-slate-600 truncate flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="truncate">Guest Name</span>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 rounded-full px-2 py-0.5 text-2xs text-slate-600 truncate flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span class="truncate">Email Address</span>
                                </div>
                            </div>
                            <div class="w-full py-1 rounded-full text-center text-2xs font-bold text-white shadow-xs" style="background: {{ $tmpl['default_config']['primary_color'] }};">
                                Connect &rarr;
                            </div>

                            @elseif($key === 'hotel-pms')
                            <!-- 7. Hotel PMS (Room & Last Name) Mini Form -->
                            <div class="space-y-1 mb-2">
                                <div class="bg-slate-50 border border-slate-200 rounded-full px-2 py-0.5 text-2xs text-slate-600 truncate flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span class="truncate">Room 301</span>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 rounded-full px-2 py-0.5 text-2xs text-slate-600 truncate flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="truncate">Last Name</span>
                                </div>
                            </div>
                            <div class="w-full py-1 rounded-full text-center text-2xs font-bold text-white shadow-xs" style="background: {{ $tmpl['default_config']['primary_color'] }};">
                                Verify &rarr;
                            </div>
                            @endif
                        </div>

                        <!-- Right Column: Promo Slider Carousel (5 cols) -->
                        <div class="col-span-5 p-2.5 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white flex flex-col justify-between relative overflow-hidden">
                            <!-- Subtle highlight banner -->
                            <div class="text-2xs font-extrabold uppercase tracking-wider text-amber-300 truncate">Nexa WiFi</div>
                            <div class="my-auto py-1">
                                <div class="text-xs font-black text-white leading-tight">High-Speed Hotspot</div>
                                <div class="text-2xs text-slate-300 mt-0.5">Unlimited Speed</div>
                            </div>
                            <!-- Mini Carousel Dots -->
                            <div class="flex items-center gap-1 mt-1 pt-1 border-t border-white/10">
                                <span class="w-2.5 h-1 rounded-full" style="background: {{ $tmpl['default_config']['primary_color'] }};"></span>
                                <span class="w-1 h-1 rounded-full bg-white/40"></span>
                                <span class="w-1 h-1 rounded-full bg-white/40"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed mb-4">
                    {{ $tmpl['description'] }}
                </p>

                <!-- Color Palette Highlights -->
                <div class="flex items-center gap-3 text-xs text-slate-600 mb-6 bg-slate-50 p-3 rounded-lg border border-slate-200/60">
                    <span class="text-xs font-semibold text-slate-700">Color Palette:</span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3.5 h-3.5 rounded-full border border-slate-300" style="background: {{ $tmpl['default_config']['primary_color'] }};" title="Primary Brand Color"></span>
                        <span class="text-xs font-mono font-medium text-slate-800">{{ $tmpl['default_config']['primary_color'] }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3.5 h-3.5 rounded-full border border-slate-300" style="background: {{ $tmpl['default_config']['accent_color'] }};" title="Accent Color"></span>
                        <span class="text-xs font-mono font-medium text-slate-800">{{ $tmpl['default_config']['accent_color'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons: Side-by-Side Edit, Preview, and Activate -->
            <div class="space-y-2 pt-3 border-t border-slate-100">
                <div class="grid grid-cols-2 gap-2">
                    <!-- Tombol Customize Content -->
                    <a
                        href="{{ route('admin.sites.template.customizer', $site) }}?template={{ $key }}"
                        class="btn-secondary text-xs py-2 px-3 flex items-center justify-center gap-1.5 font-semibold hover:border-brand/40 hover:text-brand"
                        title="Customize branding, headline, buttons, and QoS profiles"
                    >
                        <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Customize Content</span>
                    </a>

                    <!-- Tombol Live Preview (Desktop & Smartphone Popup) -->
                    <button
                        type="button"
                        @click="openPreview('{{ $key }}', '{{ addslashes($tmpl['name']) }}', '{{ addslashes($tmpl['category']) }}')"
                        class="btn-secondary text-xs py-2 px-3 flex items-center justify-center gap-1.5 font-semibold hover:border-brand/40 hover:text-brand"
                        title="Open interactive multi-device desktop and smartphone simulator"
                    >
                        <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>Live Preview</span>
                    </button>
                </div>

                <!-- Tombol Pilih & Aktifkan Template (In-Place 1-Active Rule) -->
                <div>
                    <template x-if="activeTemplate === '{{ $key }}'">
                        <div class="w-full py-2 px-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold text-center flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Active Login Method</span>
                        </div>
                    </template>
                    <template x-if="activeTemplate !== '{{ $key }}'">
                        <button
                            type="button"
                            @click="activateTemplate('{{ $key }}', '{{ addslashes($tmpl['name']) }}')"
                            :disabled="activating"
                            class="btn-primary w-full text-xs py-2 px-3 text-center justify-center flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Activate Template</span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- ======================================================== -->
    <!-- INTERACTIVE MULTI-DEVICE PREVIEW POPUP MODAL            -->
    <!-- Menampilkan Live Iframe dengan Switcher Desktop & Ponsel-->
    <!-- ======================================================== -->
    <div
        x-show="previewModalOpen"
        x-cloak
        class="fixed inset-0 z-[999] flex items-center justify-center p-3 sm:p-6 bg-slate-950/80 backdrop-blur-sm"
        @keydown.escape.window="closePreview()"
    >
        <div
            @click.outside="closePreview()"
            class="bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-5xl h-[92vh] flex flex-col overflow-hidden"
        >
            <!-- Modal Header: Title, Device Switcher, Quick Activate, and Close -->
            <div class="px-5 py-3.5 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-3 bg-white">
                <!-- Info Template -->
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-brand">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-extrabold text-slate-900 text-sm" x-text="previewTemplateName"></h3>
                            <span class="badge bg-slate-100 text-slate-700 text-2xs font-semibold" x-text="previewTemplateCategory"></span>
                        </div>
                        <p class="text-xs text-slate-500">Interactive guest authentication preview simulation</p>
                    </div>
                </div>

                <!-- Device Viewport Switcher Toolbar (Desktop vs Smartphone) -->
                <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200/80" role="tablist" aria-label="Device Viewport Mode">
                    <button
                        type="button"
                        role="tab"
                        id="tab-preview-desktop"
                        aria-controls="panel-preview-desktop"
                        :aria-selected="previewDevice === 'desktop'"
                        @click="previewDevice = 'desktop'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                        :class="previewDevice === 'desktop' ? 'bg-white text-brand shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Desktop (1024px)</span>
                    </button>

                    <button
                        type="button"
                        role="tab"
                        id="tab-preview-smartphone"
                        aria-controls="panel-preview-smartphone"
                        :aria-selected="previewDevice === 'smartphone'"
                        @click="previewDevice = 'smartphone'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                        :class="previewDevice === 'smartphone' ? 'bg-white text-brand shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span>Smartphone (390px)</span>
                    </button>
                </div>

                <!-- Actions: Activate Template & Close Modal -->
                <div class="flex items-center gap-2">
                    <template x-if="activeTemplate !== previewTemplateId">
                        <button
                            type="button"
                            @click="activateTemplate(previewTemplateId, previewTemplateName)"
                            :disabled="activating"
                            class="btn-primary text-xs py-2 px-3.5 flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Activate This Template</span>
                        </button>
                    </template>
                    <template x-if="activeTemplate === previewTemplateId">
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg">
                            ✓ Currently Active
                        </span>
                    </template>

                    <button
                        type="button"
                        @click="closePreview()"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 font-bold text-xl transition-colors"
                        title="Close Preview"
                        aria-label="Close Preview Modal"
                    >
                        &times;
                    </button>
                </div>
            </div>

            <!-- Modal Body: Frame Display Canvas -->
            <div class="flex-1 bg-slate-100/80 p-4 sm:p-6 overflow-y-auto flex items-center justify-center">

                <!-- 1. DESKTOP VIEWPORT FRAME -->
                <div
                    x-show="previewDevice === 'desktop'"
                    x-cloak
                    id="panel-preview-desktop"
                    role="tabpanel"
                    aria-labelledby="tab-preview-desktop"
                    class="w-full h-full max-w-5xl bg-white rounded-xl shadow-lg border border-slate-300/80 flex flex-col overflow-hidden"
                >
                    <!-- Browser Window Header Bar -->
                    <div class="px-4 py-2.5 bg-slate-200/60 border-b border-slate-300/80 flex items-center gap-3">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-rose-400"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                        </div>
                        <div class="flex-1 max-w-md bg-white px-3 py-1 rounded-md text-xs text-slate-500 font-mono truncate border border-slate-200">
                            🔒 https://portal.wifipads.local/?loc={{ $site->slug }}&template=<span x-text="previewTemplateId"></span>
                        </div>
                    </div>
                    <!-- Live Iframe -->
                    <div class="flex-1 w-full h-full bg-slate-900 relative">
                        <iframe
                            :src="previewUrl"
                            class="w-full h-full border-0"
                            title="Desktop Portal Preview"
                        ></iframe>
                    </div>
                </div>

                <!-- 2. SMARTPHONE VIEWPORT FRAME (Realistic Curved Bezel & Speaker Pill) -->
                <div
                    x-show="previewDevice === 'smartphone'"
                    x-cloak
                    id="panel-preview-smartphone"
                    role="tabpanel"
                    aria-labelledby="tab-preview-smartphone"
                    class="relative my-auto"
                >
                    <div class="w-[375px] h-[670px] bg-slate-950 rounded-[48px] p-3 shadow-2xl border-4 border-slate-800 flex flex-col justify-between relative overflow-hidden">
                        <!-- Speaker & Camera Dynamic Notch -->
                        <div class="absolute top-4 left-1/2 -translate-x-1/2 z-20 flex items-center justify-center">
                            <div class="w-24 h-4 bg-slate-900 rounded-full border border-slate-800 flex items-center justify-end px-3">
                                <span class="w-2 h-2 rounded-full bg-slate-800 border border-slate-700"></span>
                            </div>
                        </div>

                        <!-- Inner Screen with Iframe -->
                        <div class="w-full h-full rounded-[38px] overflow-hidden bg-white relative">
                            <iframe
                                :src="previewUrl"
                                class="w-full h-full border-0"
                                title="Smartphone Portal Preview"
                            ></iframe>
                        </div>

                        <!-- Home Bar Pill -->
                        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 w-32 h-1 bg-white/40 rounded-full z-20"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<script>
function templateGalleryManager() {
    return {
        activeTemplate: '{{ $activeTemplateId }}',
        previewModalOpen: false,
        previewDevice: 'smartphone', // default smartphone
        previewTemplateId: '{{ $activeTemplateId }}',
        previewTemplateName: '{{ addslashes($templates[$activeTemplateId]['name'] ?? 'Modern Glass') }}',
        previewTemplateCategory: '{{ addslashes($templates[$activeTemplateId]['category'] ?? 'Default') }}',
        previewUrl: '',
        activating: false,
        notification: null,

        formatTemplateName(str) {
            if (!str) return '';
            return str.replace(/-/g, ' ');
        },

        openPreview(templateId, name, category) {
            this.previewTemplateId = templateId;
            this.previewTemplateName = name;
            this.previewTemplateCategory = category;
            this.previewUrl = `{{ route('portal') }}?loc={{ $site->slug }}&preview_template=${templateId}&preview=1`;
            this.previewModalOpen = true;
        },

        closePreview() {
            this.previewModalOpen = false;
        },

        activateTemplate(templateId, name) {
            this.activating = true;
            fetch(`{{ url('/admin/sites/' . $site->id . '/template/select') }}/${templateId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.activating = false;
                if (data.success) {
                    this.activeTemplate = templateId;
                    this.showToast(`Template "${name}" successfully activated for {{ $site->name }}!`);
                } else {
                    alert(data.message || 'Failed to activate template.');
                }
            })
            .catch(err => {
                this.activating = false;
                // Fallback standard submit if json failed
                window.location.reload();
            });
        },

        showToast(msg) {
            this.notification = msg;
            setTimeout(() => {
                this.notification = null;
            }, 3500);
        }
    };
}
</script>
@endsection
