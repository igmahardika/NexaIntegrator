@extends('layouts.admin')
@section('title', 'Add New Site & Gateway')
@section('page-title', 'Site Provisioning Wizard')
@section('page-subtitle', 'Daftarkan site baru, tentukan profil pelanggan, dan konfigurasi integrasi gateway MikroTik secara otomatis')

@section('content')
<div x-data="siteWizard()" class="max-w-5xl mx-auto">

    <!-- Top Stepper Navigation -->
    <div class="card p-4 sm:p-6 mb-6 bg-white border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between relative">
            <!-- Connecting Line -->
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-slate-100 w-full z-0 rounded-full"></div>
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-brand transition-all duration-300 z-0 rounded-full"
                :style="`width: ${((currentStep - 1) / 2) * 100}%`"></div>

            <!-- Step 1 Button -->
            <button type="button" @click="goToStep(1)" class="relative z-10 flex flex-col items-center group cursor-pointer focus:outline-hidden">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-200 border-2"
                    :class="currentStep === 1 
                        ? 'bg-brand text-white border-brand ring-4 ring-brand/20 shadow-sm' 
                        : (currentStep > 1 ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-500 border-slate-300')">
                    <template x-if="currentStep > 1">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <template x-if="currentStep <= 1">
                        <span>1</span>
                    </template>
                </div>
                <span class="text-xs font-semibold mt-2 transition-colors"
                    :class="currentStep === 1 ? 'text-brand font-bold' : 'text-slate-600'">
                    Identitas Site
                </span>
            </button>

            <!-- Step 2 Button -->
            <button type="button" @click="goToStep(2)" class="relative z-10 flex flex-col items-center group cursor-pointer focus:outline-hidden">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-200 border-2"
                    :class="currentStep === 2 
                        ? 'bg-brand text-white border-brand ring-4 ring-brand/20 shadow-sm' 
                        : (currentStep > 2 ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-500 border-slate-300')">
                    <template x-if="currentStep > 2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <template x-if="currentStep <= 2">
                        <span>2</span>
                    </template>
                </div>
                <span class="text-xs font-semibold mt-2 transition-colors"
                    :class="currentStep === 2 ? 'text-brand font-bold' : 'text-slate-600'">
                    Arsitektur Gateway
                </span>
            </button>

            <!-- Step 3 Button -->
            <button type="button" @click="goToStep(3)" class="relative z-10 flex flex-col items-center group cursor-pointer focus:outline-hidden">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-200 border-2"
                    :class="currentStep === 3 
                        ? 'bg-brand text-white border-brand ring-4 ring-brand/20 shadow-sm' 
                        : 'bg-white text-slate-500 border-slate-300'">
                    <span>3</span>
                </div>
                <span class="text-xs font-semibold mt-2 transition-colors"
                    :class="currentStep === 3 ? 'text-brand font-bold' : 'text-slate-600'">
                    Review & Deploy
                </span>
            </button>
        </div>
    </div>

    <!-- Error Alert Banner -->
    @if ($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-3">
        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <div class="font-bold mb-1">Periksa kembali data yang dimasukkan:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Main Wizard Form -->
    <form method="POST" action="{{ route('admin.sites.store') }}" id="siteWizardForm" @submit="onFormSubmit">
        @csrf

        <!-- ========================================================= -->
        <!-- STEP 1: IDENTITAS SITE & PELANGGAN                        -->
        <!-- ========================================================= -->
        <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
            
            <div class="card p-6 bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-brand">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Informasi Cabang & Pelanggan</h2>
                        <p class="text-xs text-slate-500">Tentukan nama lokasi, profil klien, dan alamat fisik penempatan router</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Site Name -->
                    <div class="md:col-span-2">
                        <label class="label">Nama Site / Lokasi <span class="text-rose-500 font-bold">*</span></label>
                        <input
                            type="text"
                            name="name"
                            x-model="form.name"
                            required
                            placeholder="Contoh: Kopi Kenangan Mall Bali Galeria"
                            class="input w-full font-medium"
                            :class="errors.name ? 'border-rose-400 focus:ring-rose-200' : ''"
                        >
                        <p class="text-2xs text-slate-500 mt-1">Nama ini akan menjadi pengenal utama lokasi pada dashboard dan URL captive portal.</p>
                        <p x-show="errors.name" class="text-2xs text-rose-600 font-semibold mt-1" x-text="errors.name"></p>
                    </div>

                    <!-- Customer / Company Name -->
                    <div>
                        <label class="label">Nama Pelanggan / Entitas Bisnis</label>
                        <input
                            type="text"
                            name="customer_name"
                            x-model="form.customer_name"
                            placeholder="Contoh: PT Kenangan Abadi Nusantara"
                            class="input w-full"
                        >
                        <p class="text-2xs text-slate-500 mt-1">Nama perusahaan atau pemilik venue untuk keperluan laporan.</p>
                    </div>

                    <!-- Business Type Selector -->
                    <div>
                        <label class="label">Kategori Venue / Usaha <span class="text-rose-500 font-bold">*</span></label>
                        <select name="business_type" x-model="form.business_type" class="input w-full font-medium" required>
                            <option value="cafe">Cafe / Restoran / F&B</option>
                            <option value="hotel">Hotel / Villa / Resort</option>
                            <option value="retail">Retail / Minimarket / Store</option>
                            <option value="coworking">Co-Working Space</option>
                            <option value="office">Corporate Office / Enterprise</option>
                            <option value="other">Lainnya / Venue Publik</option>
                        </select>
                        <p class="text-2xs text-slate-500 mt-1">Menentukan rekomendasi QoS dan template captive portal awal.</p>
                    </div>

                    <!-- Contact Email -->
                    <div>
                        <label class="label">Email PIC / Admin Venue</label>
                        <input
                            type="email"
                            name="contact_email"
                            x-model="form.contact_email"
                            placeholder="admin@venue.com"
                            class="input w-full"
                        >
                    </div>

                    <!-- Contact Phone -->
                    <div>
                        <label class="label">Nomor WhatsApp / Kontak</label>
                        <input
                            type="text"
                            name="contact_phone"
                            x-model="form.contact_phone"
                            placeholder="081234567890"
                            class="input w-full"
                        >
                    </div>

                    <!-- Physical Address -->
                    <div class="md:col-span-2">
                        <label class="label">Alamat Fisik Lokasi</label>
                        <textarea
                            name="address"
                            x-model="form.address"
                            rows="2"
                            placeholder="Jl. Bypass Ngurah Rai No. 123, Kuta, Bali"
                            class="input w-full text-xs"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Portal Login Method Template -->
            <div class="card p-6 bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Template Captive Portal Awal</h2>
                        <p class="text-xs text-slate-500">Pilih tata letak login yang akan pertama kali tampil pada smartphone pengunjung</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($templates as $tpl)
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all duration-200"
                        :class="form.active_template === '{{ $tpl['id'] }}' 
                            ? 'border-brand bg-brand/5 ring-2 ring-brand/20 shadow-xs' 
                            : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <input
                            type="radio"
                            name="active_template"
                            value="{{ $tpl['id'] }}"
                            x-model="form.active_template"
                            class="sr-only"
                        >
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-xs text-slate-900 capitalize">{{ $tpl['name'] }}</span>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                :class="form.active_template === '{{ $tpl['id'] }}' ? 'border-brand bg-brand' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="form.active_template === '{{ $tpl['id'] }}'"></div>
                            </div>
                        </div>
                        <p class="text-2xs text-slate-500 line-clamp-2">{{ $tpl['description'] ?? 'Template interaktif WiFiPads' }}</p>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Step 1 Footer -->
            <div class="flex justify-between items-center pt-2">
                <a href="{{ route('admin.sites.index') }}" class="btn-secondary text-xs font-semibold">
                    Batal & Kembali
                </a>
                <button type="button" @click="proceedToStep(2)" class="btn-primary text-xs font-semibold flex items-center gap-2">
                    <span>Lanjut ke Arsitektur Gateway</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- STEP 2: ARSITEKTUR GATEWAY & ROUTER INTEGRATION          -->
        <!-- ========================================================= -->
        <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
            
            <div class="card p-6 bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Pilih Mode Integrasi Gateway</h2>
                        <p class="text-xs text-slate-500">Sesuaikan dengan topologi jaringan ISP venue (apakah di balik CGNAT atau memiliki IP Publik)</p>
                    </div>
                </div>

                <!-- 3 Gateway Architecture Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    
                    <!-- Card 1: Zero-Tunnel (Recommended) -->
                    <label class="relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition-all duration-200"
                        :class="form.gateway_mode === 'zero_tunnel' 
                            ? 'border-emerald-500 bg-emerald-50/40 ring-2 ring-emerald-500/20 shadow-xs' 
                            : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <input type="radio" name="gateway_mode" value="zero_tunnel" x-model="form.gateway_mode" class="sr-only">
                        
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Sangat Direkomendasikan
                            </span>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                :class="form.gateway_mode === 'zero_tunnel' ? 'border-emerald-600 bg-emerald-600' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="form.gateway_mode === 'zero_tunnel'"></div>
                            </div>
                        </div>

                        <h3 class="font-bold text-sm text-slate-900 mb-1">Zero-Tunnel / Reverse Polling</h3>
                        <p class="text-2xs text-slate-600 mb-4 flex-1">
                            Tanpa perlu IP Publik, tanpa VPN, tanpa port forward. Sangat aman di balik CGNAT modem Indihome, Biznet, MyRepublic, atau Starlink.
                        </p>

                        <div class="pt-3 border-t border-slate-200/60 text-2xs text-slate-500 space-y-1">
                            <div class="flex items-center gap-1.5 text-emerald-700 font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>MikroTik Scheduler Auto-Sync</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-emerald-700 font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Toleran Mati Listrik & Ganti IP</span>
                            </div>
                        </div>
                    </label>

                    <!-- Card 2: Direct RouterOS API -->
                    <label class="relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition-all duration-200"
                        :class="form.gateway_mode === 'direct_api' 
                            ? 'border-brand bg-brand/5 ring-2 ring-brand/20 shadow-xs' 
                            : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <input type="radio" name="gateway_mode" value="direct_api" x-model="form.gateway_mode" class="sr-only">
                        
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-bold bg-blue-100 text-brand border border-blue-200">
                                Port 8728 Direct
                            </span>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                :class="form.gateway_mode === 'direct_api' ? 'border-brand bg-brand' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="form.gateway_mode === 'direct_api'"></div>
                            </div>
                        </div>

                        <h3 class="font-bold text-sm text-slate-900 mb-1">Direct RouterOS API</h3>
                        <p class="text-2xs text-slate-600 mb-4 flex-1">
                            Koneksi real-time dua arah. Membutuhkan IP Publik Statis, port forward 8728, atau koneksi VPN Dedicated (WireGuard/ZeroTier).
                        </p>

                        <div class="pt-3 border-t border-slate-200/60 text-2xs text-slate-500 space-y-1">
                            <div class="flex items-center gap-1.5 text-brand font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Live CPU & Traffic Telemetry</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-brand font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Instant Kick & Disconnect</span>
                            </div>
                        </div>
                    </label>

                    <!-- Card 3: True RADIUS AAA -->
                    <label class="relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition-all duration-200"
                        :class="form.gateway_mode === 'radius' 
                            ? 'border-purple-500 bg-purple-50/40 ring-2 ring-purple-500/20 shadow-xs' 
                            : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <input type="radio" name="gateway_mode" value="radius" x-model="form.gateway_mode" class="sr-only">
                        
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                Enterprise RFC 2865
                            </span>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                :class="form.gateway_mode === 'radius' ? 'border-purple-600 bg-purple-600' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="form.gateway_mode === 'radius'"></div>
                            </div>
                        </div>

                        <h3 class="font-bold text-sm text-slate-900 mb-1">True Cloud RADIUS AAA</h3>
                        <p class="text-2xs text-slate-600 mb-4 flex-1">
                            Otentikasi standar RFC 2865, Accounting UDP 1813, dan CoA Disconnect UDP 3799. Cocok untuk MikroTik besar atau AP UniFi/Ruckus.
                        </p>

                        <div class="pt-3 border-t border-slate-200/60 text-2xs text-slate-500 space-y-1">
                            <div class="flex items-center gap-1.5 text-purple-700 font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Multi-Vendor Hardware Support</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-purple-700 font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Standard RADIUS Accounting</span>
                            </div>
                        </div>
                    </label>

                </div>

                <!-- Conditional Configuration Forms -->
                
                <!-- 1. Zero-Tunnel Fields -->
                <div x-show="form.gateway_mode === 'zero_tunnel'" class="p-5 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-700 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs text-emerald-900">Konfigurasi Zero-Tunnel Otomatis</h4>
                            <p class="text-2xs text-emerald-800 leading-relaxed">
                                Sistem secara otomatis akan menghasilkan <strong>Secret Token 32-karakter</strong> dan <strong>Webhook Polling URL</strong>. Anda tidak perlu repot menyetel port forward di router!
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-emerald-200/60">
                        <div>
                            <label class="label text-emerald-900">Hotspot DNS Name</label>
                            <input
                                type="text"
                                name="dns_name"
                                x-model="form.dns_name"
                                placeholder="wifi.login"
                                class="input w-full bg-white font-mono text-xs"
                            >
                            <p class="text-2xs text-emerald-700 mt-1">Domain lokal DNS MikroTik Hotspot Server (default: wifi.login)</p>
                        </div>

                        <div>
                            <label class="label text-emerald-900">Maksimum Perangkat Bersamaan</label>
                            <input
                                type="number"
                                name="max_active_devices"
                                x-model="form.max_active_devices"
                                class="input w-full bg-white text-xs"
                                min="10"
                                max="2000"
                            >
                            <p class="text-2xs text-emerald-700 mt-1">Batas aman kapasitas perangkat aktif</p>
                        </div>
                    </div>
                </div>

                <!-- 2. Direct RouterOS API Fields -->
                <div x-show="form.gateway_mode === 'direct_api'" class="p-5 rounded-2xl bg-blue-50/60 border border-blue-200/80 space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-brand shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-xs text-slate-900">Kredensial API RouterOS (Port 8728)</h4>
                                <p class="text-2xs text-slate-600 leading-relaxed">
                                    Pastikan service API MikroTik telah aktif di menu <code>/ip service</code>. Anda dapat mengetes koneksi sebelum menyimpan.
                                </p>
                            </div>
                        </div>

                        <!-- Live Test Button -->
                        <button
                            type="button"
                            @click="testDirectApiConnection"
                            :disabled="testingApi"
                            class="btn-secondary text-xs font-semibold shrink-0 flex items-center gap-1.5 shadow-xs"
                        >
                            <template x-if="testingApi">
                                <svg class="animate-spin w-3.5 h-3.5 text-brand" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <template x-if="!testingApi">
                                <svg class="w-3.5 h-3.5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </template>
                            <span x-text="testingApi ? 'Menguji...' : 'Tes Koneksi Port 8728'"></span>
                        </button>
                    </div>

                    <!-- Live Test Feedback Box -->
                    <div x-show="apiTestResult" class="p-3 rounded-xl text-xs flex items-center gap-2.5 transition-all"
                        :class="apiTestResult?.connected ? 'bg-emerald-100/80 text-emerald-800 border border-emerald-300' : 'bg-rose-100/80 text-rose-800 border border-rose-300'">
                        <template x-if="apiTestResult?.connected">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <template x-if="!apiTestResult?.connected">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </template>
                        <div class="font-medium" x-text="apiTestResult?.message"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 pt-2 border-t border-blue-200/60">
                        <div class="sm:col-span-2">
                            <label class="label">IP Publik / Host Router <span class="text-rose-500 font-bold">*</span></label>
                            <input
                                type="text"
                                name="router_ip"
                                x-model="form.router_ip"
                                placeholder="192.168.88.1 atau vpn.hostname.com"
                                class="input w-full bg-white font-mono text-xs"
                            >
                        </div>
                        <div>
                            <label class="label">Port API</label>
                            <input
                                type="number"
                                name="router_port"
                                x-model="form.router_port"
                                class="input w-full bg-white font-mono text-xs"
                            >
                        </div>
                        <div>
                            <label class="label">Hotspot DNS</label>
                            <input
                                type="text"
                                name="dns_name"
                                x-model="form.dns_name"
                                placeholder="wifi.login"
                                class="input w-full bg-white font-mono text-xs"
                            >
                        </div>

                        <div class="sm:col-span-2">
                            <label class="label">User API MikroTik <span class="text-rose-500 font-bold">*</span></label>
                            <input
                                type="text"
                                name="router_user"
                                x-model="form.router_user"
                                placeholder="admin / wifipads"
                                class="input w-full bg-white text-xs"
                            >
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label">Password API MikroTik</label>
                            <input
                                type="password"
                                name="router_password"
                                x-model="form.router_password"
                                placeholder="••••••••"
                                class="input w-full bg-white text-xs"
                            >
                        </div>
                    </div>
                </div>

                <!-- 3. True RADIUS AAA Fields -->
                <div x-show="form.gateway_mode === 'radius'" class="p-5 rounded-2xl bg-purple-50/60 border border-purple-200/80 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center text-purple-700 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs text-purple-900">Parameter Otentikasi RADIUS RFC 2865</h4>
                            <p class="text-2xs text-purple-800 leading-relaxed">
                                Masukkan Host server RADIUS dan kunci rahasia bersama (Shared Secret) untuk verifikasi paket Access-Request dan Accounting.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-purple-200/60">
                        <div class="sm:col-span-2">
                            <label class="label text-purple-900">RADIUS Server IP / Hostname</label>
                            <input
                                type="text"
                                name="radius_server_ip"
                                x-model="form.radius_server_ip"
                                class="input w-full bg-white font-mono text-xs"
                            >
                        </div>

                        <div>
                            <label class="label text-purple-900">Shared Secret Key</label>
                            <input
                                type="text"
                                name="radius_secret"
                                x-model="form.radius_secret"
                                class="input w-full bg-white font-mono text-xs"
                            >
                        </div>

                        <div>
                            <label class="label text-purple-900">Auth Port (UDP)</label>
                            <input
                                type="number"
                                name="radius_auth_port"
                                x-model="form.radius_auth_port"
                                class="input w-full bg-white font-mono text-xs"
                            >
                        </div>

                        <div>
                            <label class="label text-purple-900">Accounting Port (UDP)</label>
                            <input
                                type="number"
                                name="radius_acct_port"
                                x-model="form.radius_acct_port"
                                class="input w-full bg-white font-mono text-xs"
                            >
                        </div>

                        <div>
                            <label class="label text-purple-900">CoA Port (RFC 3576)</label>
                            <input
                                type="number"
                                name="radius_coa_port"
                                x-model="form.radius_coa_port"
                                class="input w-full bg-white font-mono text-xs"
                            >
                        </div>
                    </div>
                </div>

            </div>

            <!-- Step 2 Footer -->
            <div class="flex justify-between items-center pt-2">
                <button type="button" @click="currentStep = 1" class="btn-secondary text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Identitas</span>
                </button>
                <button type="button" @click="proceedToStep(3)" class="btn-primary text-xs font-semibold flex items-center gap-2">
                    <span>Lanjut ke Review & Deploy</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- STEP 3: REVIEW & AUTOMATED DEPLOYMENT CONFIRMATION        -->
        <!-- ========================================================= -->
        <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
            
            <div class="card p-6 bg-white border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Ringkasan & Otomatisasi Provisioning</h2>
                        <p class="text-xs text-slate-500">Periksa seluruh konfigurasi sebelum sistem mengeksekusi automasi deployment</p>
                    </div>
                </div>

                <!-- Review Information Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70">
                        <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Site & Pelanggan</div>
                        <div class="font-bold text-slate-900 text-sm" x-text="form.name || '—'"></div>
                        <div class="text-xs text-brand font-medium mt-0.5" x-text="form.customer_name ? 'Klien: ' + form.customer_name : 'Internal Deployment'"></div>
                        <div class="text-2xs text-slate-500 mt-2 truncate" x-text="form.address || 'Alamat fisik belum diisi'"></div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70">
                        <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider mb-1">Mode Gateway & Jaringan</div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 text-sm capitalize" x-text="getGatewayModeLabel()"></span>
                            <span class="px-2 py-0.5 rounded text-2xs font-bold uppercase"
                                :class="form.gateway_mode === 'zero_tunnel' ? 'bg-emerald-100 text-emerald-800' : (form.gateway_mode === 'direct_api' ? 'bg-blue-100 text-brand' : 'bg-purple-100 text-purple-800')">
                                Ready
                            </span>
                        </div>
                        <div class="text-2xs text-slate-600 mt-1.5 font-mono">
                            <span x-show="form.gateway_mode === 'zero_tunnel'">Architecture: Reverse Polling (No-Tunnel)</span>
                            <span x-show="form.gateway_mode === 'direct_api'" x-text="`Host: ${form.router_ip || '192.168.88.1'}:${form.router_port || 8728}`"></span>
                            <span x-show="form.gateway_mode === 'radius'" x-text="`RADIUS: ${form.radius_server_ip}:${form.radius_auth_port}`"></span>
                        </div>
                    </div>
                </div>

                <!-- Automation Tasks Checklist -->
                <div class="space-y-3 p-5 rounded-2xl bg-slate-50/80 border border-slate-200/80 mb-6">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">Tindakan yang Akan Dieksekusi Otomatis oleh Sistem:</h3>
                    
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="auto_init_tenant" value="1" x-model="form.auto_init_tenant" checked class="checkbox mt-0.5">
                        <div>
                            <div class="text-xs font-bold text-slate-900">Inisialisasi Database Tenant Terisolasi</div>
                            <div class="text-2xs text-slate-600">
                                Membuat file <code>database/tenants/site_{slug}.sqlite</code> secara mandiri dan menjalankan migrasi tabel (vouchers, members, sessions) agar data venue terpisah total.
                            </div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="auto_seed_profiles" value="1" x-model="form.auto_seed_profiles" checked class="checkbox mt-0.5">
                        <div>
                            <div class="text-xs font-bold text-slate-900">Deploy 3 Default QoS Hotspot Profiles</div>
                            <div class="text-2xs text-slate-600">
                                Otomatis mendaftarkan profil <code>survey-user</code> (2M/5M), <code>voucher-user</code> (5M/10M), dan <code>member-user</code> (10M/20M).
                            </div>
                        </div>
                    </label>

                    <div class="flex items-start gap-3 text-slate-700 pt-1">
                        <div class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Generate 1-Click RouterOS WinBox Script & login.html</div>
                            <div class="text-2xs text-slate-600">
                                Script terminal siap copy dan file <code>login.html</code> (&lt;1 KB) yang langsung mengarah ke captive portal site ini akan disajikan di layar berikutnya.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Site Active Switch -->
                <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
                    <input type="checkbox" id="is_active" name="is_active" value="1" x-model="form.is_active" checked class="checkbox">
                    <label for="is_active" class="text-xs font-semibold text-slate-800 cursor-pointer">
                        Langsung aktifkan status Site (Active)
                    </label>
                </div>
            </div>

            <!-- Step 3 Footer -->
            <div class="flex justify-between items-center pt-2">
                <button type="button" @click="currentStep = 2" class="btn-secondary text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Arsitektur</span>
                </button>

                <button type="submit" :disabled="submitting" class="btn-primary text-xs font-semibold flex items-center gap-2 shadow-md">
                    <template x-if="submitting">
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                    <span x-text="submitting ? 'Menyiapkan Site & Database...' : 'Deploy Site & Generate MikroTik Script'"></span>
                    <template x-if="!submitting">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@section('scripts')
<script>
function siteWizard() {
    return {
        currentStep: 1,
        submitting: false,
        testingApi: false,
        apiTestResult: null,
        errors: {},

        form: {
            name: '{{ old('name', '') }}',
            customer_name: '{{ old('customer_name', '') }}',
            business_type: '{{ old('business_type', 'cafe') }}',
            contact_email: '{{ old('contact_email', '') }}',
            contact_phone: '{{ old('contact_phone', '') }}',
            address: '{{ old('address', '') }}',
            active_template: '{{ old('active_template', 'modern-glass') }}',
            gateway_mode: '{{ old('gateway_mode', 'zero_tunnel') }}',
            dns_name: '{{ old('dns_name', 'wifi.login') }}',
            max_active_devices: {{ old('max_active_devices', 100) }},
            router_ip: '{{ old('router_ip', '192.168.88.1') }}',
            router_port: {{ old('router_port', 8728) }},
            router_user: '{{ old('router_user', 'admin') }}',
            router_password: '{{ old('router_password', '') }}',
            radius_server_ip: '{{ old('radius_server_ip', $serverHost) }}',
            radius_secret: '{{ old('radius_secret', '') }}',
            radius_auth_port: {{ old('radius_auth_port', 1812) }},
            radius_acct_port: {{ old('radius_acct_port', 1813) }},
            radius_coa_port: {{ old('radius_coa_port', 3799) }},
            auto_init_tenant: true,
            auto_seed_profiles: true,
            is_active: true,
        },

        goToStep(step) {
            if (step > this.currentStep) {
                this.proceedToStep(step);
            } else {
                this.currentStep = step;
            }
        },

        proceedToStep(step) {
            this.errors = {};
            if (this.currentStep === 1) {
                if (!this.form.name.trim()) {
                    this.errors.name = 'Nama Site wajib diisi.';
                    return;
                }
            }
            if (this.currentStep === 2 && step === 3) {
                if (this.form.gateway_mode === 'direct_api' && !this.form.router_ip.trim()) {
                    alert('Harap masukkan IP Router atau hostname untuk mode Direct API.');
                    return;
                }
            }
            this.currentStep = step;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        getGatewayModeLabel() {
            if (this.form.gateway_mode === 'zero_tunnel') return 'Zero-Tunnel (Reverse Polling)';
            if (this.form.gateway_mode === 'direct_api') return 'Direct RouterOS API';
            if (this.form.gateway_mode === 'radius') return 'True RADIUS AAA';
            return this.form.gateway_mode;
        },

        async testDirectApiConnection() {
            if (!this.form.router_ip) {
                alert('Masukkan IP Router terlebih dahulu.');
                return;
            }
            this.testingApi = true;
            this.apiTestResult = null;

            try {
                const response = await fetch('{{ route('admin.sites.test-draft') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        router_ip: this.form.router_ip,
                        router_port: this.form.router_port,
                        router_user: this.form.router_user,
                        router_password: this.form.router_password
                    })
                });

                const data = await response.json();
                if (data.connected) {
                    this.apiTestResult = {
                        connected: true,
                        message: `Sukses terhubung! Latency: ${data.latency_ms ?? 0} ms`
                    };
                } else {
                    this.apiTestResult = {
                        connected: false,
                        message: `Gagal: ${data.error ?? 'Koneksi ke port 8728 ditolak atau timeout.'}`
                    };
                }
            } catch (err) {
                this.apiTestResult = {
                    connected: false,
                    message: 'Terjadi kesalahan jaringan saat menguji koneksi.'
                };
            } finally {
                this.testingApi = false;
            }
        },

        onFormSubmit() {
            this.submitting = true;
        }
    };
}
</script>
@endsection
