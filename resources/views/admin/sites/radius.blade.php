@extends('layouts.admin')
@section('title', 'Edge Router Provisioning & Management — ' . $site->name)
@section('page-title', 'Edge Router Provisioning & Management')
@section('page-subtitle', 'Standar Integrasi Tunggal: Direct RouterOS API (Port ' . ($site->router_port ?: 8728) . ') — Aktivasi Sesi Real-time & 0% Flash Wear untuk ' . $site->name)

@section('content')
<div x-data="routerIntegrationManager()" class="space-y-6">

    <!-- Top Site Hero Banner -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-center text-brand shrink-0 shadow-xs">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2.5 mb-1.5">
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $site->name }}</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-brand border border-blue-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand mr-1.5"></span>
                        Direct RouterOS API Standard
                    </span>
                    <template x-if="apiResult && apiResult.connected">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                            API Online (<span x-text="apiResult.latency_ms"></span> ms)
                        </span>
                    </template>
                    <template x-if="apiResult && !apiResult.connected">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                            API Unreachable
                        </span>
                    </template>
                    <template x-if="!apiResult && testingApi">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5 animate-ping"></span>
                            Checking API...
                        </span>
                    </template>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 font-medium">
                    <span>Site Slug: <strong class="text-slate-800 font-mono">{{ $site->slug }}</strong></span>
                    <span>•</span>
                    <span>Router Endpoint: <strong class="text-slate-800 font-mono">{{ $site->router_ip ?: 'Belum dikonfigurasi' }}:{{ $site->router_port ?: 8728 }}</strong></span>
                    <span>•</span>
                    <span>API Operator: <strong class="text-slate-800 font-mono">{{ $site->router_user ?: 'wifipads' }}</strong></span>
                    <span>•</span>
                    <span>Hotspot DNS: <strong class="text-brand font-mono">{{ $site->dns_name ?: 'wifi.nexa.id' }}</strong></span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('admin.sites.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                &larr; Back to Sites
            </a>
            <a href="{{ route('admin.radius.download-login-html', $site) }}" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>login.html</span>
            </a>
            <a href="{{ route('admin.sites.template.gallery', $site) }}" class="px-4 py-2 text-xs font-semibold text-white bg-brand hover:bg-brand-hover rounded-xl shadow-xs transition">
                Template Studio &rarr;
            </a>
        </div>
    </div>

    <!-- Hardware & API Telemetry Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Metric 1: Connection & Latency -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Direct API Connection</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-brand flex items-center justify-center font-bold text-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </span>
            </div>
            <div>
                <div class="text-xl font-black font-mono text-slate-900" x-text="apiResult ? (apiResult.connected ? 'Connected' : 'Offline') : 'Checking...'">...</div>
                <p class="text-2xs text-slate-500 mt-1 font-mono">
                    Endpoint: {{ $site->router_ip ?: 'None' }}:{{ $site->router_port ?: 8728 }}
                </p>
            </div>
            <div class="pt-3 mt-2 border-t border-slate-100">
                <button @click="testApi()" :disabled="testingApi" class="w-full py-1.5 px-3 text-2xs font-bold text-brand bg-blue-50 hover:bg-blue-100 rounded-lg transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <span x-show="!testingApi">⚡ Test Ulang Koneksi API</span>
                    <span x-show="testingApi" x-cloak>Menghubungi MikroTik...</span>
                </button>
            </div>
        </div>

        <!-- Metric 2: Live Hardware Diagnostics -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Hardware Telemetry</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                </span>
            </div>
            <template x-if="apiResult && apiResult.connected">
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between"><span class="text-slate-500">Board Model:</span> <strong class="text-slate-900 font-mono" x-text="apiResult.board_name"></strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">RouterOS:</span> <strong class="text-slate-900 font-mono" x-text="apiResult.version"></strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">CPU Load:</span> <strong class="text-brand font-mono" x-text="apiResult.cpu_load"></strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Free RAM:</span> <strong class="text-emerald-700 font-mono" x-text="apiResult.free_memory"></strong></div>
                </div>
            </template>
            <template x-if="!apiResult || !apiResult.connected">
                <div class="text-xs text-slate-400 py-2 italic leading-relaxed">
                    Telemetri hardware akan ditampilkan otomatis saat router terhubung via API port {{ $site->router_port ?: 8728 }}.
                </div>
            </template>
        </div>

        <!-- Metric 3: Architecture Standard Benefits -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Karakteristik Arsitektur</span>
                <span class="px-2 py-0.5 rounded text-2xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                    RAM-Only (Safe)
                </span>
            </div>
            <div class="space-y-2 text-2xs text-slate-600">
                <div class="flex items-center gap-1.5 text-emerald-700 font-semibold">
                    <span>✓</span> <span>0% Flash Wear (Nol penulisan disk pada router)</span>
                </div>
                <div class="flex items-center gap-1.5 text-brand font-semibold">
                    <span>✓</span> <span>Aktivasi Sesi Real-Time (/ip/hotspot/active/login)</span>
                </div>
                <div class="flex items-center gap-1.5 text-purple-700 font-semibold">
                    <span>✓</span> <span>Bebas Timeout RADIUS & Mixed Content Free</span>
                </div>
            </div>
            <div class="pt-2 text-2xs text-slate-400 border-t border-slate-100">
                Metode integrasi resmi & tunggal untuk seluruh cabang venue Nexa.
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (Single Unified Architecture) -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-2">
        <button 
            @click="activeTab = 'script'" 
            :class="activeTab === 'script' ? 'bg-brand text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
            class="px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span>RouterOS API Provisioning Script</span>
            <span class="px-1.5 py-0.5 rounded text-2xs font-bold" :class="activeTab === 'script' ? 'bg-white/20 text-white' : 'bg-blue-100 text-brand'">Standar Tunggal</span>
        </button>

        <button 
            @click="activeTab = 'loginhtml'" 
            :class="activeTab === 'loginhtml' ? 'bg-brand text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
            class="px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
            </svg>
            <span>Redirect Template (login.html)</span>
            <span class="px-1.5 py-0.5 rounded text-2xs font-bold" :class="activeTab === 'loginhtml' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'">&lt; 1 KB</span>
        </button>

        <button 
            @click="activeTab = 'traffic'; startTrafficMonitoring()" 
            :class="activeTab === 'traffic' ? 'bg-brand text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
            class="px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Live Traffic & Telemetry (MRTG)</span>
            <span class="px-1.5 py-0.5 rounded text-2xs font-bold" :class="activeTab === 'traffic' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-700'">Real-time</span>
        </button>

        <button 
            @click="activeTab = 'settings'" 
            :class="activeTab === 'settings' ? 'bg-brand text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
            class="px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Router Connection Settings</span>
        </button>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 1: ROUTEROS API PROVISIONING SCRIPT (STANDAR TUNGGAL) -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'script'" class="space-y-6" x-transition>

        <!-- Architecture Banner -->
        <div class="bg-gradient-to-br from-brand-hover via-brand to-slate-900 rounded-2xl p-6 text-white shadow-md">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-blue-200 text-xs font-semibold backdrop-blur-xs">
                        <span>Standar Integrasi Tunggal: Direct RouterOS API (Port {{ $site->router_port ?: 8728 }})</span>
                    </div>
                    <h3 class="text-lg font-bold tracking-tight">Bagaimana Cara Kerja Direct RouterOS API?</h3>
                    <p class="text-xs text-blue-100/90 leading-relaxed">
                        Saat pengunjung memvalidasi voucher, whatsapp, email, atau metode login lainnya di captive portal, backend cloud langsung mengeksekusi otorisasi aktif melalui <strong>RouterOS API Port 8728</strong> secara instan. HP tamu langsung terhubung ke internet tanpa perlu submit form HTTP lokal, menghindari Mixed Content blocking, dan tanpa membebani flash memory router sama sekali!
                    </p>
                </div>

                <div class="bg-white/10 p-4 rounded-xl backdrop-blur-xs border border-white/10 text-xs space-y-2 shrink-0 lg:w-80">
                    <div class="font-bold text-white mb-1">Keunggulan Standar Tunggal:</div>
                    <div class="flex items-center gap-2 text-blue-100">
                        <span class="text-emerald-400 font-bold">✓</span> <strong>0% Flash Wear:</strong> Tanpa polling script .rsc berulang
                    </div>
                    <div class="flex items-center gap-2 text-blue-100">
                        <span class="text-emerald-400 font-bold">✓</span> <strong>Instant Login:</strong> Tanpa Mixed Content freeze di ponsel
                    </div>
                    <div class="flex items-center gap-2 text-blue-100">
                        <span class="text-emerald-400 font-bold">✓</span> <strong>Bebas RADIUS:</strong> Tanpa timeout UDP 1812/1813
                    </div>
                    <div class="flex items-center gap-2 text-blue-100">
                        <span class="text-emerald-400 font-bold">✓</span> <strong>Instant Kick:</strong> Putuskan pengguna seketika dari cloud
                    </div>
                </div>
            </div>
        </div>

        <!-- 1-Click MikroTik WinBox Provisioning Script -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-mono text-xs font-bold">1</span>
                        <span>1-Click RouterOS WinBox Provisioning Script (.rsc)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Salin skrip di bawah ini dan paste ke <strong>New Terminal</strong> di WinBox MikroTik Anda:
                    </p>
                </div>

                <button @click="copyDirectApiScript()" class="px-4 py-2 text-xs font-bold text-white bg-brand hover:bg-brand-hover rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <template x-if="!copiedScript">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span>Salin Skrip WinBox</span>
                        </span>
                    </template>
                    <template x-if="copiedScript">
                        <span class="text-white font-bold flex items-center gap-1">
                            ✓ Berhasil Disalin ke Clipboard!
                        </span>
                    </template>
                </button>
            </div>

            <!-- Terminal Code Box -->
            <div class="relative rounded-xl bg-slate-900 p-5 border border-slate-800 font-mono text-xs text-emerald-400 overflow-x-auto shadow-inner max-h-[460px]">
                <pre class="leading-relaxed select-all" id="directApiScriptBox">{{ $directApiScript }}</pre>
            </div>

            <!-- Visual 4-Step Guide -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-3">
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div class="text-xs font-bold text-slate-900 mb-1">1. Buka WinBox</div>
                    <div class="text-2xs text-slate-500">Hubungkan ke router MikroTik venue melalui IP atau MAC Address.</div>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div class="text-xs font-bold text-slate-900 mb-1">2. Buka New Terminal</div>
                    <div class="text-2xs text-slate-500">Klik menu <strong>New Terminal</strong> pada menu samping WinBox.</div>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div class="text-xs font-bold text-slate-900 mb-1">3. Paste & Tekan Enter</div>
                    <div class="text-2xs text-slate-500">Klik kanan lalu Paste skrip di atas, lalu tekan <strong>Enter</strong>.</div>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div class="text-xs font-bold text-emerald-700 mb-1">4. Upload login.html</div>
                    <div class="text-2xs text-slate-500">Download file <code>login.html</code> dari tab samping dan drag ke folder <code>hotspot/</code>.</div>
                </div>
            </div>
        </div>

    </div>

    <!-- ================================================================= -->
    <!-- TAB 2: MINIMALIST LOGIN.HTML (< 1 KB) -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'loginhtml'" class="space-y-6" x-transition x-cloak>
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <span>Minimalist login.html Redirector (&lt; 1 KB)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        File redirect ultra-ringan tanpa aset gambar atau JS berat. Langsung mengarahkan captive portal ke Cloud Nexa.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="downloadLoginHtml()" class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Download login.html</span>
                    </button>
                    <button @click="copyLoginHtml()" class="px-3.5 py-2 text-xs font-bold text-white bg-brand hover:bg-brand-hover rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <template x-if="!copiedLoginHtml">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span>Copy Code</span>
                            </span>
                        </template>
                        <template x-if="copiedLoginHtml">
                            <span>✓ Tersalin!</span>
                        </template>
                    </button>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-7">
                    <div class="rounded-xl bg-slate-900 p-4 font-mono text-xs text-emerald-400 overflow-x-auto shadow-inner border border-slate-800">
                        <pre id="loginHtmlBox" class="select-all leading-relaxed">{{ $minimalLoginHtml }}</pre>
                    </div>
                </div>

                <div class="lg:col-span-5 space-y-3">
                    <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-100 text-xs text-blue-900">
                        <strong class="font-bold block mb-1">💡 Mengapa Ini Penting?</strong>
                        Router MikroTik memiliki kapasitas flash storage terbatas (16MB). Menyimpan aset portal gambar dan script di flash router dapat merusak chip memori. File redirect ini hanya berukuran <strong>750 bytes</strong> dan langsung mengarahkan perangkat tamu ke Cloud Captive Portal secara instan.
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 space-y-2">
                        <strong class="font-bold block text-slate-900">Cara Memasang di MikroTik:</strong>
                        <ol class="list-decimal pl-4 space-y-1 text-slate-600">
                            <li>Buka <strong>WinBox</strong> dan hubungkan ke router.</li>
                            <li>Buka menu <strong>Files</strong> di sidebar WinBox.</li>
                            <li>Buka folder <strong>hotspot</strong>.</li>
                            <li>Seret (drag & drop) file <code>login.html</code> ke folder <strong>hotspot</strong> untuk menimpa file lama.</li>
                            <li>Selesai! Captive portal langsung aktif.</li>
                        </ol>
                    </div>

                    <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1.5">
                        <div class="font-bold flex items-center gap-1.5 text-amber-800">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>PENTING: Walled Garden Domain</span>
                        </div>
                        <p class="text-2xs leading-relaxed text-amber-800">
                            Skrip di Tab 1 sudah otomatis menambahkan domain <code>{{ $serverHost }}</code> ke Walled Garden MikroTik. Pengunjung dapat membuka portal secara mulus sebelum login.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 3: REAL-TIME TRAFFIC & MRTG -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'traffic'" class="space-y-6" x-transition x-cloak>
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Real-Time WAN Traffic Monitoring (RouterOS API)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Membaca throughput interface WAN (<code class="font-mono bg-slate-100 px-1 rounded" x-text="trafficData.interface">ether1</code>) langsung dari router setiap 2 detik.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 text-xs font-mono">
                        <span class="text-emerald-600 font-bold">RX: <span x-text="trafficData.rx_human || '0 bps'"></span></span>
                        <span class="text-slate-300">|</span>
                        <span class="text-purple-600 font-bold">TX: <span x-text="trafficData.tx_human || '0 bps'"></span></span>
                    </div>

                    <button @click="toggleTrafficPolling()" class="px-3 py-1.5 text-xs font-bold rounded-xl transition flex items-center gap-1.5 cursor-pointer"
                        :class="trafficPolling ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200'">
                        <span class="w-2 h-2 rounded-full" :class="trafficPolling ? 'bg-rose-500 animate-ping' : 'bg-emerald-500'"></span>
                        <span x-text="trafficPolling ? 'Pause Polling' : 'Start Polling'"></span>
                    </button>
                </div>
            </div>

            <!-- Chart Canvas Container -->
            <div class="relative h-72 w-full">
                <canvas id="wanTrafficChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 4: ROUTER CONNECTION SETTINGS -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'settings'" class="space-y-6" x-transition x-cloak>
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs max-w-3xl">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Pengaturan Koneksi RouterOS API Venue</span>
            </h3>
            <p class="text-xs text-slate-500 mb-5">
                Pastikan IP dan Port API dapat dijangkau oleh server WiFiPads (melalui IP Publik Statis, Port Forwarding, atau Dedicated VPN).
            </p>

            <form method="POST" action="{{ route('admin.sites.radius.update', $site) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="gateway_mode" value="direct_api">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Router IP / Hostname <span class="text-rose-500">*</span></label>
                        <input type="text" name="router_ip" value="{{ old('router_ip', $site->router_ip) }}" placeholder="e.g. 111.68.27.10 atau vpn.nexa.id" class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">API Port RouterOS <span class="text-rose-500">*</span></label>
                        <input type="number" name="router_port" value="{{ old('router_port', $site->router_port ?: 8728) }}" required class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">API Username Operator <span class="text-rose-500">*</span></label>
                        <input type="text" name="router_user" value="{{ old('router_user', $site->router_user ?: 'wifipads') }}" required class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">API Password Operator</label>
                        <input type="password" name="router_password" placeholder="(Kosongkan jika tidak diubah)" class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hotspot DNS Hostname</label>
                        <input type="text" name="dns_name" value="{{ old('dns_name', $site->dns_name ?: 'wifi.nexa.id') }}" placeholder="wifi.nexa.id" class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Default Rate Limit</label>
                        <input type="text" name="default_rate_limit" value="{{ old('default_rate_limit', $site->default_rate_limit ?: '5M/10M') }}" placeholder="5M/10M" class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" @click="testApi()" class="btn-secondary text-xs font-semibold">
                        ⚡ Uji Sambungan API Sekarang
                    </button>
                    <button type="submit" class="btn-primary text-xs font-semibold">
                        Simpan Pengaturan Router
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
function routerIntegrationManager() {
    return {
        activeTab: 'script',
        copiedScript: false,
        copiedLoginHtml: false,
        testingApi: false,
        apiResult: null,
        trafficPolling: false,
        trafficTimer: null,
        trafficChart: null,
        trafficData: {
            interface: 'ether1',
            rx_bps: 0,
            tx_bps: 0,
            rx_human: '0 bps',
            tx_human: '0 bps'
        },

        init() {
            // Otomatis verifikasi API saat halaman dimuat
            this.testApi();
        },

        copyDirectApiScript() {
            const scriptText = document.getElementById('directApiScriptBox').innerText;
            navigator.clipboard.writeText(scriptText).then(() => {
                this.copiedScript = true;
                setTimeout(() => this.copiedScript = false, 3000);
            });
        },

        copyLoginHtml() {
            const code = document.getElementById('loginHtmlBox').innerText;
            navigator.clipboard.writeText(code).then(() => {
                this.copiedLoginHtml = true;
                setTimeout(() => this.copiedLoginHtml = false, 3000);
            });
        },

        downloadLoginHtml() {
            const code = document.getElementById('loginHtmlBox').innerText;
            const blob = new Blob([code], { type: 'text/html' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'login.html';
            a.click();
        },

        testApi() {
            this.testingApi = true;
            this.apiResult = null;

            fetch('{{ route('admin.radius.test-api', $site) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                this.apiResult = data;
                this.testingApi = false;
            })
            .catch(err => {
                this.apiResult = { connected: false, error: err.message };
                this.testingApi = false;
            });
        },

        startTrafficMonitoring() {
            if (!this.trafficChart) {
                this.$nextTick(() => {
                    this.initTrafficChart();
                });
            }
            if (!this.trafficPolling) {
                this.toggleTrafficPolling();
            }
        },

        toggleTrafficPolling() {
            this.trafficPolling = !this.trafficPolling;
            if (this.trafficPolling) {
                this.fetchTrafficData();
                this.trafficTimer = setInterval(() => {
                    this.fetchTrafficData();
                }, 2000);
            } else {
                if (this.trafficTimer) {
                    clearInterval(this.trafficTimer);
                    this.trafficTimer = null;
                }
            }
        },

        fetchTrafficData() {
            fetch('{{ route('admin.radius.traffic', $site) }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
            .then(res => res.json())
            .then(data => {
                this.trafficData = data;
                if (this.trafficChart && data.online) {
                    const timeLabel = data.timestamp || new Date().toLocaleTimeString();
                    const rxMbps = (data.rx_bps / 1000000).toFixed(2);
                    const txMbps = (data.tx_bps / 1000000).toFixed(2);

                    const chart = this.trafficChart;
                    chart.data.labels.push(timeLabel);
                    chart.data.datasets[0].data.push(rxMbps);
                    chart.data.datasets[1].data.push(txMbps);

                    if (chart.data.labels.length > 20) {
                        chart.data.labels.shift();
                        chart.data.datasets[0].data.shift();
                        chart.data.datasets[1].data.shift();
                    }
                    chart.update('none');
                }
            })
            .catch(() => {});
        },

        initTrafficChart() {
            const ctx = document.getElementById('wanTrafficChart');
            if (!ctx) return;

            this.trafficChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Download (RX Mbps)',
                            data: [],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            tension: 0.35,
                            fill: true,
                        },
                        {
                            label: 'Upload (TX Mbps)',
                            data: [],
                            borderColor: '#8b5cf6',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            borderWidth: 2,
                            tension: 0.35,
                            fill: true,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { boxWidth: 12, font: { size: 11, weight: 'bold' } }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Throughput (Mbps)', font: { size: 10 } },
                            grid: { color: 'rgba(226, 232, 240, 0.6)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    };
}
</script>
@endsection
