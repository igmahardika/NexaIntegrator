@extends('layouts.admin')
@section('title', 'Customize Template — ' . $site->name)
@section('page-title', 'Visual Template Customizer')
@section('page-subtitle', 'Configure branding, authentication texts, QoS bandwidth profiles, and preview live on mobile & desktop for ' . $site->name)

@section('content')
<div x-data="templateCustomizer()" class="grid grid-cols-1 lg:grid-cols-12 gap-8">

    <!-- ==================== LEFT COLUMN: CUSTOMIZER FORM ==================== -->
    <div class="lg:col-span-7 space-y-6">

        <!-- Top Status Bar & Template Switcher -->
        <div class="card p-4 flex items-center justify-between gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-brand">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-medium">Site: <strong class="text-slate-900 font-bold">{{ $site->name }}</strong></div>
                    <div class="text-xs text-brand font-bold">Template: <span class="capitalize">{{ str_replace('-', ' ', $templateId ?? ($site->active_template ?: 'access-code')) }}</span></div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.sites.template.gallery', $site) }}" class="btn-secondary text-xs py-1.5 px-3 flex items-center gap-1 font-semibold">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Template Catalog</span>
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.sites.template.update', $site) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="template_id" value="{{ $templateId }}">

            <!-- 1. Brand Identity & Typography -->
            <div class="card p-5 space-y-4 bg-white border border-slate-200/80 shadow-xs">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2.5">
                    <span class="w-2 h-2 rounded-full bg-brand"></span>
                    <span>1. Brand Identity & Header Typography</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Brand / Business Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="brand_name" x-model="config.brand_name" required class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="nexa Hotspot">
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Short Tagline</label>
                        <input type="text" name="brand_tagline" x-model="config.brand_tagline" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="High-Speed Guest Connectivity">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Official Instagram Handle</label>
                        <div class="flex items-center">
                            <span class="px-3 py-2 bg-slate-100 border border-r-0 border-slate-200 text-slate-500 rounded-l-lg text-xs font-bold select-none">@</span>
                            <input type="text" name="instagram" x-model="config.instagram" class="input bg-white border-slate-200 text-slate-900 rounded-l-none rounded-r-lg w-full" placeholder="nexanet.id">
                        </div>
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Portal Screen Title</label>
                        <input type="text" name="topbar_title" x-model="config.topbar_title" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="Access Code">
                    </div>
                </div>

                <!-- Logo Uploads -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                    <div class="space-y-2">
                        <label class="label text-slate-700 font-semibold block">Brand Logo (Card Modal Color Logo)</label>
                        <input type="file" name="logo_file" accept="image/*" @change="handleFileUpload($event, 'logo_url')" class="text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand file:text-white hover:file:bg-brand-hover cursor-pointer">
                        <input type="text" name="logo_url" x-model="config.logo_url" class="input text-xs bg-white border-slate-200 text-slate-900 rounded-lg w-full mt-1" placeholder="/images/nexa/logo-hotspot-color.png">
                    </div>
                    <div class="space-y-2">
                        <label class="label text-slate-700 font-semibold block">Standby White Logo (Welcome Screen)</label>
                        <input type="file" name="logo_white_file" accept="image/*" @change="handleFileUpload($event, 'logo_white')" class="text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand file:text-white hover:file:bg-brand-hover cursor-pointer">
                        <input type="text" name="logo_white" x-model="config.logo_white" class="input text-xs bg-white border-slate-200 text-slate-900 rounded-lg w-full mt-1" placeholder="/images/nexa/logo-hotspot-white.png">
                    </div>
                </div>

                <!-- Headline, Subtitle, and Button Text -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Input Field Placeholder <span class="text-rose-500">*</span></label>
                        <input type="text" name="input_placeholder" x-model="config.input_placeholder" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="Input Access Code">
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Action Button Text <span class="text-rose-500">*</span></label>
                        <input type="text" name="button_text" x-model="config.button_text" required class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="Validasi Kode Akses">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Hero Headline</label>
                        <input type="text" name="hero_title" x-model="config.hero_title" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="Input Access Code">
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Hero Subtitle</label>
                        <input type="text" name="hero_subtitle" x-model="config.hero_subtitle" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="Masukkan kode voucher atau tiket akses internet Anda">
                    </div>
                </div>
            </div>

            <!-- 2. Color Palette & Background Wallpaper -->
            <div class="card p-5 space-y-4 bg-white border border-slate-200/80 shadow-xs">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2.5">
                    <span class="w-2 h-2 rounded-full bg-brand"></span>
                    <span>2. Color Palette & Background Styling</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Primary Accent / Button Color <span class="text-rose-500">*</span></label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="config.primary_color" class="w-10 h-10 rounded-lg cursor-pointer bg-transparent border border-slate-200">
                            <input type="text" name="primary_color" x-model="config.primary_color" class="input font-mono bg-white border-slate-200 text-slate-900 rounded-lg flex-1">
                        </div>
                    </div>

                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Accent / Glow Color <span class="text-rose-500">*</span></label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="config.topbar_color" class="w-10 h-10 rounded-lg cursor-pointer bg-transparent border border-slate-200">
                            <input type="text" name="topbar_color" x-model="config.topbar_color" class="input font-mono bg-white border-slate-200 text-slate-900 rounded-lg flex-1">
                            <input type="hidden" name="accent_color" :value="config.topbar_color || config.accent_color">
                        </div>
                    </div>
                </div>

                <!-- Wallpaper File Upload -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="label text-slate-700 font-semibold mb-1 block">Background Type</label>
                            <select name="bg_type" x-model="config.bg_type" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full">
                                <option value="image">Wallpaper Image</option>
                                <option value="gradient">CSS Gradient</option>
                                <option value="color">Solid Color</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="label text-slate-700 font-semibold mb-1 block">Background Path / URL / CSS</label>
                            <input type="text" name="bg_value" x-model="config.bg_value" required class="input font-mono text-xs bg-white border-slate-200 text-slate-900 rounded-lg w-full">
                        </div>
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Upload New Wallpaper (Optional)</label>
                        <input type="file" name="wallpaper_file" accept="image/*" @change="handleFileUpload($event, 'bg_value')" class="text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand file:text-white hover:file:bg-brand-hover cursor-pointer">
                        <p class="text-xs text-slate-500 mt-1">Direct upload previews immediately in the simulator and saves on submit.</p>
                    </div>
                </div>
            </div>

            <!-- 3. Promo Announcements & Sponsored Banner -->
            <div class="card p-5 space-y-4 bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-brand"></span>
                        <span>3. Promo Announcements & Promotional Banner Slider</span>
                    </h3>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="promo_enabled" value="1" x-model="config.promo_enabled" class="accent-brand w-4 h-4 rounded">
                        <span class="text-xs font-bold text-slate-700">Display Promo Slider</span>
                    </label>
                </div>

                <div x-show="config.promo_enabled" x-transition class="space-y-4 pt-2">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="label text-slate-700 font-semibold block text-xs">Slide 1 Image Banner</label>
                                <span class="text-3xs text-slate-400 font-mono">Default / File</span>
                            </div>
                            <div class="relative w-full aspect-square rounded-xl overflow-hidden border border-slate-200 bg-white shadow-xs flex items-center justify-center">
                                <img :src="config.promo_image ? (config.promo_image.startsWith('data:') ? config.promo_image : config.promo_image + '?v=' + Date.now()) : '/images/nexa/promo-slide-1.jpg'" alt="Slide 1" class="w-full h-full object-cover">
                            </div>
                            <input type="file" name="promo_file_1" accept="image/*" @change="handleFileUpload($event, 'promo_image')" class="text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand file:text-white hover:file:bg-brand-hover cursor-pointer w-full">
                            <input type="text" name="promo_image" x-model="config.promo_image" class="input text-xs bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="/images/nexa/promo-slide-1.jpg">
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="label text-slate-700 font-semibold block text-xs">Slide 2 Image Banner</label>
                                <span class="text-3xs text-slate-400 font-mono">Default / File</span>
                            </div>
                            <div class="relative w-full aspect-square rounded-xl overflow-hidden border border-slate-200 bg-white shadow-xs flex items-center justify-center">
                                <img :src="config.promo_image_2 ? (config.promo_image_2.startsWith('data:') ? config.promo_image_2 : config.promo_image_2 + '?v=' + Date.now()) : '/images/nexa/promo-slide-2.jpg'" alt="Slide 2" class="w-full h-full object-cover">
                            </div>
                            <input type="file" name="promo_file_2" accept="image/*" @change="handleFileUpload($event, 'promo_image_2')" class="text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand file:text-white hover:file:bg-brand-hover cursor-pointer w-full">
                            <input type="text" name="promo_image_2" x-model="config.promo_image_2" class="input text-xs bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="/images/nexa/promo-slide-2.jpg">
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="label text-slate-700 font-semibold block text-xs">Slide 3 Image Banner</label>
                                <span class="text-3xs text-slate-400 font-mono">Default / File</span>
                            </div>
                            <div class="relative w-full aspect-square rounded-xl overflow-hidden border border-slate-200 bg-white shadow-xs flex items-center justify-center">
                                <img :src="config.promo_image_3 ? (config.promo_image_3.startsWith('data:') ? config.promo_image_3 : config.promo_image_3 + '?v=' + Date.now()) : '/images/nexa/promo-slide-3.jpg'" alt="Slide 3" class="w-full h-full object-cover">
                            </div>
                            <input type="file" name="promo_file_3" accept="image/*" @change="handleFileUpload($event, 'promo_image_3')" class="text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand file:text-white hover:file:bg-brand-hover cursor-pointer w-full">
                            <input type="text" name="promo_image_3" x-model="config.promo_image_3" class="input text-xs bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="/images/nexa/promo-slide-3.jpg">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="label text-slate-700 font-semibold mb-1 block">Badge Label</label>
                            <input type="text" name="promo_badge" x-model="config.promo_badge" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="Internet Nexa">
                        </div>
                        <div class="md:col-span-2">
                            <label class="label text-slate-700 font-semibold mb-1 block">Promo Headline</label>
                            <input type="text" name="promo_title" x-model="config.promo_title" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="Koneksi Cepat & Handal">
                        </div>
                    </div>
                    <div>
                        <label class="label text-slate-700 font-semibold mb-1 block">Promo Message Body</label>
                        <textarea name="promo_text" x-model="config.promo_text" rows="2" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="Didukung jaringan fiber optik berkecepatan tinggi hingga 1 Gbps..."></textarea>
                    </div>
                </div>
            </div>

            <!-- 4. MikroTik Bandwidth & QoS Profile Assignment -->
            <div class="card p-5 space-y-4 bg-white border border-slate-200/80 shadow-xs">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2.5">
                    <span class="w-2 h-2 rounded-full bg-brand"></span>
                    <span>4. MikroTik Bandwidth & QoS Profile Assignment</span>
                </h3>

                <div class="p-3.5 rounded-xl bg-blue-50/50 border border-blue-100 space-y-3">
                    <div class="text-xs font-bold text-brand flex items-center gap-1.5">
                        <span>⚡ Hotspot Rate-Limit Profile Policy</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="label text-slate-700 font-semibold mb-1 block">Default QoS Profile for This Template</label>
                            <select name="survey_profile" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full">
                                <option value="">Use Default Hotspot Profile</option>
                                @foreach($hotspotProfiles as $prof)
                                <option value="{{ $prof->name }}" {{ ($config['survey_profile'] ?? '') === $prof->name ? 'selected' : '' }}>
                                    {{ $prof->display_name ?: $prof->name }} ({{ $prof->rate_limit ?: 'Unlimited' }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label text-slate-700 font-semibold mb-1 block">Fallback / Voucher Profile</label>
                            <select name="voucher_profile" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full">
                                <option value="">Match Voucher Source Profile</option>
                                @foreach($hotspotProfiles as $prof)
                                <option value="{{ $prof->name }}" {{ ($config['voucher_profile'] ?? '') === $prof->name ? 'selected' : '' }}>
                                    {{ $prof->display_name ?: $prof->name }} ({{ $prof->rate_limit ?: 'Unlimited' }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Terms of Service & Custom CSS -->
            <div class="card p-5 space-y-4 bg-white border border-slate-200/80 shadow-xs">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2.5">
                    <span class="w-2 h-2 rounded-full bg-brand"></span>
                    <span>5. Terms of Service & Custom CSS Overrides</span>
                </h3>

                <div>
                    <label class="label text-slate-700 font-semibold mb-1 block">Terms of Service (TOS) Notice</label>
                    <textarea name="tos_text" x-model="config.tos_text" rows="2" class="input bg-white border-slate-200 text-slate-900 rounded-lg w-full"></textarea>
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold mb-1 block">Custom CSS Overrides (Optional)</label>
                    <textarea name="custom_css" x-model="config.custom_css" rows="3" class="input font-mono text-xs bg-white border-slate-200 text-slate-900 rounded-lg w-full" placeholder="/* Injected directly into portal header if needed */"></textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.sites.template.gallery', $site) }}" class="btn-secondary text-xs py-2.5 px-5 font-semibold">
                    Cancel
                </a>
                <button type="submit" class="btn-primary text-xs py-2.5 px-6 flex items-center gap-2 font-bold shadow-md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Save Template Configuration</span>
                </button>
            </div>
        </form>
    </div>

    <!-- ==================== RIGHT COLUMN: REAL-TIME INTERACTIVE VIEWPORT SIMULATOR ==================== -->
    <div class="lg:col-span-5">
        <div class="sticky top-6 space-y-3">

            <!-- Simulator Control Toolbar -->
            <div class="p-3 bg-white border border-slate-200/80 rounded-2xl shadow-xs flex flex-wrap items-center justify-between gap-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse" aria-hidden="true"></span>
                    <span class="text-xs font-extrabold text-slate-800">Live Simulator</span>
                </div>

                <!-- Viewport Mode Buttons -->
                <div class="flex items-center bg-slate-100 p-1 rounded-xl gap-1" role="tablist" aria-label="Simulator Viewport Switcher">
                    <button
                        type="button"
                        role="tab"
                        id="tab-viewport-mobile"
                        aria-controls="panel-viewport-mobile"
                        :aria-selected="viewportMode === 'mobile'"
                        @click="viewportMode = 'mobile'"
                        :class="viewportMode === 'mobile' ? 'bg-white text-brand shadow-xs font-bold' : 'text-slate-600 font-medium hover:text-slate-900'"
                        class="px-2.5 py-1 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer"
                        title="Tampilan Smartphone Mobile (375px)"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span>Phone</span>
                    </button>
                    <button
                        type="button"
                        role="tab"
                        id="tab-viewport-desktop"
                        aria-controls="panel-viewport-desktop"
                        :aria-selected="viewportMode === 'desktop'"
                        @click="viewportMode = 'desktop'"
                        :class="viewportMode === 'desktop' ? 'bg-white text-brand shadow-xs font-bold' : 'text-slate-600 font-medium hover:text-slate-900'"
                        class="px-2.5 py-1 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer"
                        title="Tampilan Luas Desktop / Tablet (Responsive 2-Kolom)"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Desktop</span>
                    </button>
                </div>

                <!-- Simulator Action Buttons -->
                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        @click="toggleModalView()"
                        class="text-xs bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 px-2 py-1 rounded-lg font-bold transition-all flex items-center gap-1 cursor-pointer"
                        title="Buka / Tutup Dialog Login untuk melihat Standby Screen vs Modal Popup"
                        aria-label="Toggle Modal View"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>Modal</span>
                    </button>

                    <button
                        type="button"
                        @click="reloadSimulator()"
                        class="text-xs bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 p-1.5 rounded-lg transition-all cursor-pointer"
                        title="Muat Ulang Simulator"
                        aria-label="Reload Simulator"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>

                    <a
                        href="{{ route('portal', ['loc' => $site->slug, 'preview_template' => $templateId, 'preview' => 1]) }}"
                        target="_blank"
                        class="text-xs bg-blue-50 hover:bg-blue-100 text-brand border border-blue-200 px-2 py-1 rounded-lg font-bold transition-all flex items-center gap-1"
                        title="Buka Halaman Portal Nyata di Tab Baru Fullscreen"
                        aria-label="Open Fullscreen Live Portal"
                    >
                        <span>Fullscreen</span>
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- VIEWPORT CHASSIS CONTAINER -->
            <div class="transition-all duration-300 flex justify-center">

                <!-- 1. MOBILE PHONE VIEWPORT (365px Modern iPhone Bezel) -->
                <div
                    x-show="viewportMode === 'mobile'"
                    x-transition
                    id="panel-viewport-mobile"
                    role="tabpanel"
                    aria-labelledby="tab-viewport-mobile"
                    class="relative w-[365px] rounded-[48px] bg-slate-900 p-3 shadow-2xl border-4 border-slate-800 ring-1 ring-white/10 select-none"
                >
                    <!-- Top Dynamic Island / Speaker Notch -->
                    <div class="absolute top-4 left-1/2 -translate-x-1/2 z-30 w-24 h-5 rounded-full bg-slate-950 flex items-center justify-end px-2.5 gap-1.5 pointer-events-none shadow-sm">
                        <div class="w-2.5 h-2.5 rounded-full bg-[#0a0f1d] border border-slate-800/80"></div>
                    </div>

                    <!-- Embedded Real Portal Iframe -->
                    <div class="overflow-hidden rounded-[38px] w-full h-[620px] bg-slate-950 relative">
                        <iframe
                            x-ref="mobileIframe"
                            id="nexa-mobile-iframe"
                            src="{{ route('portal', ['loc' => $site->slug, 'preview_template' => $templateId, 'preview' => 1, 'embed' => 1]) }}"
                            class="w-full h-full border-0 block bg-slate-950"
                            title="Mobile Portal Preview"
                            @load="onIframeLoaded('mobile')"
                        ></iframe>
                    </div>

                    <!-- Bottom Home Bar Indicator -->
                    <div class="w-32 h-1 rounded-full bg-slate-600/70 mx-auto mt-2 pointer-events-none"></div>
                </div>

                <!-- 2. DESKTOP / TABLET EXPANDED VIEWPORT -->
                <div
                    x-show="viewportMode === 'desktop'"
                    x-transition
                    id="panel-viewport-desktop"
                    role="tabpanel"
                    aria-labelledby="tab-viewport-desktop"
                    class="w-full rounded-2xl bg-slate-900 shadow-2xl border border-slate-800 ring-1 ring-white/10 overflow-hidden"
                >
                    <!-- Mock Browser Top Navigation Bar -->
                    <div class="bg-slate-800/90 px-4 py-2.5 flex items-center justify-between border-b border-slate-700/60">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                        </div>
                        <div class="bg-slate-950/70 px-3 py-1 rounded-md text-2xs text-slate-400 font-mono flex items-center gap-1 max-w-[240px] truncate">
                            <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>hotspot.nexa.net/portal?loc={{ $site->slug }}</span>
                        </div>
                        <div class="text-2xs text-slate-500 font-bold">2-Col Modal</div>
                    </div>

                    <!-- Desktop Frame Iframe -->
                    <div class="w-full h-[620px] bg-slate-950 overflow-hidden">
                        <iframe
                            x-ref="desktopIframe"
                            id="nexa-desktop-iframe"
                            src="{{ route('portal', ['loc' => $site->slug, 'preview_template' => $templateId, 'preview' => 1, 'embed' => 1]) }}"
                            class="w-full h-full border-0 block bg-slate-950"
                            title="Desktop Portal Preview"
                            @load="onIframeLoaded('desktop')"
                        ></iframe>
                    </div>
                </div>

            </div>

            <!-- Live Status Hint -->
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-center">
                <p class="text-xs text-slate-600 font-medium">
                    ⚡ <strong>Live Reactive Preview</strong>: Setiap perubahan teks, warna, atau upload foto langsung tersinkronisasi ke tampilan portal di atas secara instan.
                </p>
            </div>

        </div>
    </div>

</div>

<script>
function templateCustomizer() {
    return {
        config: @json($config),
        viewportMode: 'mobile',
        mobileReady: false,
        desktopReady: false,

        init() {
            // Watch config changes deeply and push to iframe live
            this.$watch('config', (newVal) => {
                this.syncToIframe(newVal);
            });

            // Listen for iframe readiness notification
            window.addEventListener('message', (event) => {
                if (event.data && event.data.type === 'NEXA_PORTAL_READY') {
                    this.syncToIframe(this.config);
                }
            });
        },

        onIframeLoaded(target) {
            if (target === 'mobile') this.mobileReady = true;
            if (target === 'desktop') this.desktopReady = true;
            setTimeout(() => {
                this.syncToIframe(this.config);
            }, 100);
        },

        syncToIframe(cfg) {
            try {
                // Safely convert Alpine reactive Proxy to plain JSON object
                const plainConfig = JSON.parse(JSON.stringify(cfg || this.config));
                const payload = {
                    type: 'NEXA_LIVE_UPDATE',
                    config: plainConfig
                };

                // Post to mobile iframe
                const mFrame = this.$refs.mobileIframe;
                if (mFrame && mFrame.contentWindow) {
                    mFrame.contentWindow.postMessage(payload, '*');
                }

                // Post to desktop iframe
                const dFrame = this.$refs.desktopIframe;
                if (dFrame && dFrame.contentWindow) {
                    dFrame.contentWindow.postMessage(payload, '*');
                }
            } catch (err) {
                console.warn('Iframe sync serialization notice:', err);
            }
        },

        toggleModalView() {
            const payload = { type: 'TOGGLE_MODAL' };
            if (this.$refs.mobileIframe && this.$refs.mobileIframe.contentWindow) {
                this.$refs.mobileIframe.contentWindow.postMessage(payload, '*');
            }
            if (this.$refs.desktopIframe && this.$refs.desktopIframe.contentWindow) {
                this.$refs.desktopIframe.contentWindow.postMessage(payload, '*');
            }
        },

        reloadSimulator() {
            if (this.$refs.mobileIframe) {
                this.$refs.mobileIframe.src = this.$refs.mobileIframe.src;
            }
            if (this.$refs.desktopIframe) {
                this.$refs.desktopIframe.src = this.$refs.desktopIframe.src;
            }
        },

        handleFileUpload(event, targetKey) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                this.config[targetKey] = e.target.result;
                this.syncToIframe(this.config);
            };
            reader.readAsDataURL(file);
        }
    };
}
</script>
@endsection
