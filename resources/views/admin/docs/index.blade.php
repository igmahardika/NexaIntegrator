@extends('layouts.admin')
@section('title', 'Integration Docs & API Specification')
@section('page-title', 'Developer & Integration Hub')
@section('page-subtitle', 'Comprehensive integration guides for Hotel PMS, MikroTik RouterOS gateways, and step-by-step provisioning')

@section('header-actions')
<div class="flex items-center gap-2">
    <a :href="'{{ url('/admin/docs/print') }}/' + activeTab" target="_blank" class="btn-secondary text-xs py-2 px-3 flex items-center gap-1.5 font-semibold">
        <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        <span>Print View</span>
    </a>
    <button @click="downloadPdf()" :disabled="generatingPdf" class="btn-primary text-xs py-2 px-3.5 flex items-center gap-1.5 font-semibold shadow-sm">
        <svg x-show="!generatingPdf" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        <svg x-show="generatingPdf" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        <span x-text="generatingPdf ? 'Generating PDF...' : 'Download PDF'"></span>
    </button>
</div>
@endsection

@section('content')
<div x-data="docsManager()" class="space-y-6">

    <!-- Top Navigation Tabs Bar -->
    <div class="card p-2 bg-white border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-1.5 w-full sm:w-auto">
            <button
                @click="switchTab('pms')"
                :class="activeTab === 'pms' ? 'bg-brand text-white shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200/60'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>1. PMS API Integration</span>
                <span class="px-1.5 py-0.5 rounded text-2xs font-mono font-bold" :class="activeTab === 'pms' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">REST</span>
            </button>

            <button
                @click="switchTab('mikrotik')"
                :class="activeTab === 'mikrotik' ? 'bg-brand text-white shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200/60'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>2. MikroTik Router Integration</span>
                <span class="px-1.5 py-0.5 rounded text-2xs font-mono font-bold" :class="activeTab === 'mikrotik' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">RouterOS</span>
            </button>

            <button
                @click="switchTab('step-by-step')"
                :class="activeTab === 'step-by-step' ? 'bg-brand text-white shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200/60'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>3. Step-by-Step Provisioning Guide</span>
                <span class="px-1.5 py-0.5 rounded text-2xs font-mono font-bold" :class="activeTab === 'step-by-step' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'">Tutorial</span>
            </button>
        </div>

        <!-- Quick Site Selection Context -->
        @if($currentLocation)
        <div class="text-xs text-slate-500 font-medium px-2 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Target Context: <strong class="text-slate-900 font-mono">{{ $currentLocation->name }}</strong> ({{ $currentLocation->slug }})</span>
        </div>
        @endif
    </div>

    <!-- Printable Content Wrapper Container -->
    <div id="documentation-content" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-10 space-y-10">

        <!-- ========================================================================= -->
        <!-- SECTION 1: PMS API INTEGRATION SPECIFICATION -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'pms'" x-transition class="space-y-8">
            <!-- Header -->
            <div class="border-b border-slate-100 pb-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 text-brand text-xs font-semibold mb-3 border border-brand-200/60">
                    <span>HOTEL PROPERTY MANAGEMENT SYSTEM (PMS)</span>
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Hotel PMS API Integration Specification</h2>
                <p class="text-slate-600 text-sm mt-1 leading-relaxed">
                    Connect WiFiPads Captive Portal with enterprise Hotel Property Management Systems (PMS). Supported engines include <strong>Oracle Hospitality Opera Cloud / V5, Infor HMS, Cloudbeds, VHP, Mews, and Protel</strong>.
                </p>
            </div>

            <!-- Architecture Diagram Callout -->
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80 space-y-3">
                <div class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>System Workflow Overview</span>
                </div>
                <div class="font-mono text-xs text-slate-700 bg-white p-4 rounded-xl border border-slate-200 overflow-x-auto leading-relaxed">
                    [Guest Device] &rarr; (1. Connects Wi-Fi) &rarr; [MikroTik Edge Router]<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&darr;<br>
                    [WiFiPads Captive Portal] &larr; (2. Enters Room # &amp; Last Name) &larr; [Guest]<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&darr;<br>
                    [WiFiPads Cloud Controller] &rarr; (3. POST /verify-guest) &rarr; [Hotel PMS API / Opera / Cloudbeds]<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&darr; (4. Guest Verified &amp; Room Checked-in)<br>
                    [WiFiPads Cloud Controller] &rarr; (5. Stage in Sync Queue) &rarr; [MikroTik /ip hotspot user add]
                </div>
            </div>

            <!-- Authentication Standard -->
            <div class="space-y-3">
                <h3 class="text-base font-extrabold text-slate-900">1. Authentication Standard</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    All requests to the PMS Integration API must include a valid Bearer Token in the HTTP Authorization header or provide HMAC-SHA256 signature verification.
                </p>
                <div class="rounded-xl bg-slate-900 p-4 border border-slate-800 text-xs text-slate-200 font-mono">
                    <span class="text-slate-400">Authorization:</span> Bearer wfp_live_948f29402a8db41103c812903ab7
                </div>
            </div>

            <!-- Endpoint 1: Verify Guest Room & Last Name -->
            <div class="space-y-3 pt-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-md bg-emerald-600 text-white font-mono text-xs font-bold">POST</span>
                    <span class="font-mono text-xs font-bold text-slate-900">/api/v1/pms/verify-guest</span>
                </div>
                <p class="text-xs text-slate-600">
                    Validates whether the guest is currently registered and checked-in to the specified room in the hotel PMS database.
                </p>

                <!-- Request Schema -->
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Request Payload (JSON):</div>
                    <div class="rounded-xl bg-slate-900 p-4 border border-slate-800 text-xs font-mono text-emerald-400 overflow-x-auto relative group">
                        <button onclick="copyCode(this)" class="absolute top-3 right-3 text-[10px] px-2 py-1 rounded bg-slate-800 text-slate-300 hover:text-white border border-slate-700">Copy</button>
<pre>{
  "room_number": "302",
  "last_name": "Smith",
  "client_mac": "AA:BB:CC:11:22:33",
  "site_slug": "{{ $currentLocation->slug ?? 'hotel-location' }}"
}</pre>
                    </div>
                </div>

                <!-- Response Schema -->
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Success Response (200 OK):</div>
                    <div class="rounded-xl bg-slate-900 p-4 border border-slate-800 text-xs font-mono text-cyan-400 overflow-x-auto relative group">
                        <button onclick="copyCode(this)" class="absolute top-3 right-3 text-[10px] px-2 py-1 rounded bg-slate-800 text-slate-300 hover:text-white border border-slate-700">Copy</button>
<pre>{
  "status": "success",
  "verified": true,
  "guest": {
    "pms_guest_id": "OPERA-98210",
    "full_name": "John Smith",
    "room_number": "302",
    "checkin_date": "2026-09-07T14:00:00Z",
    "checkout_date": "2026-09-12T12:00:00Z",
    "vip_tier": "gold",
    "allocated_devices": 4
  },
  "provisioning": {
    "bandwidth_profile": "VIP-10M",
    "session_timeout_seconds": 86400,
    "idle_timeout_seconds": 1800
  }
}</pre>
                    </div>
                </div>
            </div>

            <!-- Endpoint 2: Post Folio Charge -->
            <div class="space-y-3 pt-4 border-t border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-md bg-blue-600 text-white font-mono text-xs font-bold">POST</span>
                    <span class="font-mono text-xs font-bold text-slate-900">/api/v1/pms/post-charge</span>
                </div>
                <p class="text-xs text-slate-600">
                    Posts an upgraded high-speed bandwidth package fee directly onto the guest's hotel room folio statement.
                </p>

                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Request Payload (JSON):</div>
                    <div class="rounded-xl bg-slate-900 p-4 border border-slate-800 text-xs font-mono text-emerald-400 overflow-x-auto relative group">
                        <button onclick="copyCode(this)" class="absolute top-3 right-3 text-[10px] px-2 py-1 rounded bg-slate-800 text-slate-300 hover:text-white border border-slate-700">Copy</button>
<pre>{
  "room_number": "302",
  "pms_guest_id": "OPERA-98210",
  "package_id": "pkg_ultra_stream_50m",
  "charge_amount": 50000,
  "currency": "IDR",
  "description": "High-Speed Premium Wi-Fi 24h Upgrade"
}</pre>
                    </div>
                </div>
            </div>

            <!-- Webhook Event Listeners -->
            <div class="space-y-3 pt-4 border-t border-slate-100">
                <h3 class="text-base font-extrabold text-slate-900">3. Inbound Webhook Event Listeners</h3>
                <p class="text-xs text-slate-600">
                    WiFiPads listens to real-time events published by your PMS server to automatically provision and teardown guest access:
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="font-bold text-slate-900 font-mono text-[11px]">guest.checkin</div>
                        <div class="text-slate-500 text-[11px] mt-1">Pre-generates voucher credentials or whitelists registered smart TV devices.</div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="font-bold text-rose-700 font-mono text-[11px]">guest.checkout</div>
                        <div class="text-slate-500 text-[11px] mt-1">Sends immediate Disconnect-Request (CoA) packet to disconnect all guest devices.</div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="font-bold text-blue-700 font-mono text-[11px]">room.move</div>
                        <div class="text-slate-500 text-[11px] mt-1">Updates room mapping and migrates associated active sessions to the new room number.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION 2: MIKROTIK ROUTER INTEGRATION ARCHITECTURE -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'mikrotik'" x-transition class="space-y-8">
            <!-- Header -->
            <div class="border-b border-slate-100 pb-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold mb-3 border border-emerald-200/60">
                    <span>EDGE GATEWAY INTEGRATION</span>
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">MikroTik RouterOS Integration Architecture</h2>
                <p class="text-slate-600 text-sm mt-1 leading-relaxed">
                    Technical architecture specifications, port requirements, Walled Garden lists, and deployment strategies for MikroTik RouterOS v6 and v7 hardware.
                </p>
            </div>

            <!-- Architecture Comparison Table -->
            <div class="space-y-3">
                <h3 class="text-base font-extrabold text-slate-900">1. Integration Methods Comparison</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-bold uppercase text-2xs tracking-wider border-b border-slate-200">
                                <th class="p-3">Feature Dimension</th>
                                <th class="p-3 text-emerald-700">Method 1: Zero-Tunnel (Reverse Polling)</th>
                                <th class="p-3 text-brand">Method 2: AAA RADIUS (RFC 2865 / CoA)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr>
                                <td class="p-3 font-semibold text-slate-900">Public Static IP Requirement</td>
                                <td class="p-3 text-emerald-700 font-bold">NOT REQUIRED (Works behind CGNAT & LTE 4G/5G)</td>
                                <td class="p-3 text-slate-600">Required on Cloud Server; Router needs WAN routing</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-semibold text-slate-900">Router CPU Overhead</td>
                                <td class="p-3 text-emerald-700 font-bold">Zero (No VPN encryption/decryption overhead)</td>
                                <td class="p-3 text-slate-600">Minimal UDP authentication packet overhead</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-semibold text-slate-900">Local Handshake</td>
                                <td class="p-3 text-slate-600">Instant on edge router (192.168.88.1/login)</td>
                                <td class="p-3 text-slate-600">Remote RADIUS Access-Request / Access-Accept</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-semibold text-slate-900">Recommended Use Case</td>
                                <td class="p-3 text-emerald-700 font-bold">Multi-tenant retail, cafes, hotels with dynamic WAN</td>
                                <td class="p-3 text-slate-600">Enterprise campuses with dedicated private static WAN</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Walled Garden Requirements -->
            <div class="space-y-3 pt-4 border-t border-slate-100">
                <h3 class="text-base font-extrabold text-slate-900">2. Walled Garden Domain Allowlist</h3>
                <p class="text-xs text-slate-600">
                    To prevent Captive Network Assistant (CNA) browser popups from breaking and to allow guest asset downloads before login, configure the following entries in <code class="bg-slate-100 px-1.5 py-0.5 rounded text-brand font-mono">/ip hotspot walled-garden</code>:
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="font-bold text-slate-900 mb-1.5 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span>WiFiPads Cloud Services:</span>
                        </div>
                        <ul class="space-y-1 font-mono text-slate-600 text-2xs">
                            <li>• {{ $serverHost }}</li>
                            <li>• *.wifipads.com</li>
                            <li>• cdn.jsdelivr.net (Icons & Alpine JS)</li>
                            <li>• fonts.googleapis.com</li>
                        </ul>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="font-bold text-slate-900 mb-1.5 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Operating System CNA Probes:</span>
                        </div>
                        <ul class="space-y-1 font-mono text-slate-600 text-2xs">
                            <li>• captive.apple.com (Apple iOS / macOS)</li>
                            <li>• connectivitycheck.gstatic.com (Android)</li>
                            <li>• msftconnecttest.com (Windows 10/11)</li>
                            <li>• detectportal.firefox.com (Firefox CNA)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Network Port Requirements -->
            <div class="space-y-3 pt-4 border-t border-slate-100">
                <h3 class="text-base font-extrabold text-slate-900">3. Network Ports & Firewall Whitelist</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-bold uppercase text-2xs tracking-wider border-b border-slate-200">
                                <th class="p-3">Protocol</th>
                                <th class="p-3">Port</th>
                                <th class="p-3">Direction</th>
                                <th class="p-3">Purpose</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-mono">
                            <tr>
                                <td class="p-3 font-sans font-bold text-slate-900">HTTPS</td>
                                <td class="p-3 font-bold text-brand">443 / TCP</td>
                                <td class="p-3 font-sans">Outbound (Router &rarr; Cloud)</td>
                                <td class="p-3 font-sans">Reverse Polling /tool fetch API & Hotspot Portal</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-sans font-bold text-slate-900">RouterOS API</td>
                                <td class="p-3 font-bold text-brand">8728 / 8729 TCP</td>
                                <td class="p-3 font-sans">Inbound (Cloud &rarr; Router)</td>
                                <td class="p-3 font-sans">Telemetry collection, kick user, and live health check</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-sans font-bold text-slate-900">RADIUS Auth</td>
                                <td class="p-3 font-bold text-emerald-700">1812 / UDP</td>
                                <td class="p-3 font-sans">Outbound (Router &rarr; Cloud)</td>
                                <td class="p-3 font-sans">RFC 2865 User Authentication Access-Request</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-sans font-bold text-slate-900">RADIUS CoA</td>
                                <td class="p-3 font-bold text-rose-700">3799 / UDP</td>
                                <td class="p-3 font-sans">Inbound (Cloud &rarr; Router)</td>
                                <td class="p-3 font-sans">RFC 3576 Packet of Disconnect / CoA Session Termination</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION 3: STEP-BY-STEP ROUTER PROVISIONING GUIDE -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'step-by-step'" x-transition class="space-y-8">
            <!-- Header -->
            <div class="border-b border-slate-100 pb-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-50 text-purple-700 text-xs font-semibold mb-3 border border-purple-200/60">
                    <span>STEP-BY-STEP DEPLOYMENT</span>
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Step-by-Step Edge Router Provisioning Guide</h2>
                <p class="text-slate-600 text-sm mt-1 leading-relaxed">
                    Follow this walkthrough to configure your MikroTik edge router from scratch in under 5 minutes.
                </p>
            </div>

            <!-- Steps Progress -->
            <div class="space-y-6">

                <!-- STEP 1 -->
                <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="w-8 h-8 rounded-xl bg-brand text-white flex items-center justify-center font-bold text-sm shrink-0">1</div>
                    <div class="space-y-2 flex-1">
                        <h4 class="font-extrabold text-slate-900 text-sm">Step 1: Verify Hardware & Hotspot Interface</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Ensure your MikroTik edge router has RouterOS v6.48+ or v7.x installed. Navigate to <strong>IP &rarr; Hotspot &rarr; Hotspot Setup</strong> and select your guest Wi-Fi LAN bridge interface (e.g. <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200">bridge-hotspot</code>).
                        </p>
                        <div class="rounded-xl bg-slate-900 p-3.5 border border-slate-800 text-xs font-mono text-emerald-400">
                            /ip hotspot setup
                        </div>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="w-8 h-8 rounded-xl bg-brand text-white flex items-center justify-center font-bold text-sm shrink-0">2</div>
                    <div class="space-y-2 flex-1">
                        <h4 class="font-extrabold text-slate-900 text-sm">Step 2: Generate 1-Click Provisioning Script</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            In the WiFiPads admin console, navigate to <strong>Sites &rarr; Edge Router Integration &rarr; Method 1</strong> and copy the pre-generated WinBox script specifically tailored for your site slug:
                        </p>
                        <div class="rounded-xl bg-slate-900 p-3.5 border border-slate-800 text-xs font-mono text-emerald-400 overflow-x-auto relative group">
                            <button onclick="copyCode(this)" class="absolute top-3 right-3 text-2xs px-2 py-1 rounded bg-slate-800 text-slate-300 hover:text-white border border-slate-700">Copy</button>
<pre># ==============================================================
# WiFiPads Provisioning Script for Site: {{ $currentLocation->slug ?? 'default-location' }}
# ==============================================================
/ip hotspot walled-garden
add dst-host={{ $serverHost }} comment="Nexa Portal Host"
add dst-host="*.nexa.net.id" comment="Nexa Domain Assets"
add dst-host="fonts.googleapis.com" comment="Google Fonts"
add dst-host="fonts.gstatic.com" comment="Google Fonts Static"
add dst-host="*.wifipads.com" comment="WiFiPads CDN"

/system scheduler
add name="wifipads-sync" interval=5s on-event={
    /tool fetch url="{{ $baseUrl }}/api/router/{{ $currentLocation->slug ?? 'default-location' }}/sync.rsc" dst-path="wifipads-sync.rsc" keep-result=yes
    /import file-name="wifipads-sync.rsc"
}</pre>
                        </div>
                    </div>
                </div>

                <!-- STEP 3 -->
                <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="w-8 h-8 rounded-xl bg-brand text-white flex items-center justify-center font-bold text-sm shrink-0">3</div>
                    <div class="space-y-2 flex-1">
                        <h4 class="font-extrabold text-slate-900 text-sm">Step 3: Paste into WinBox Terminal</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Open WinBox, click <strong>New Terminal</strong> on the left navigation, right-click inside the terminal window to paste the entire script, and press <kbd class="px-1.5 py-0.5 rounded bg-slate-200 text-slate-800 font-mono text-2xs font-bold">Enter</kbd>.
                        </p>
                    </div>
                </div>

                <!-- STEP 4 -->
                <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="w-8 h-8 rounded-xl bg-brand text-white flex items-center justify-center font-bold text-sm shrink-0">4</div>
                    <div class="space-y-2 flex-1">
                        <h4 class="font-extrabold text-slate-900 text-sm">Step 4: Configure Hotspot Redirect Target</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Open the hotspot files in <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200">/file</code> on your router. Update <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200">hotspot/login.html</code> to redirect unauthenticated visitors to:
                        </p>
                        <div class="rounded-xl bg-slate-900 p-3.5 border border-slate-800 text-xs font-mono text-cyan-400">
                            {{ $baseUrl }}/portal?loc={{ $currentLocation->slug ?? 'default-location' }}
                        </div>
                    </div>
                </div>

                <!-- STEP 5 -->
                <div class="flex items-start gap-4 p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200">
                    <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0">5</div>
                    <div class="space-y-2 flex-1">
                        <h4 class="font-extrabold text-emerald-900 text-sm">Step 5: Live Verification & Testing</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Connect your smartphone to the Wi-Fi network. The captive portal login assistant should pop up immediately displaying your active login template method.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Troubleshooting Checklist -->
            <div class="space-y-3 pt-4 border-t border-slate-100">
                <h3 class="text-base font-extrabold text-slate-900">4. Diagnostic Troubleshooting Checklist</h3>
                <div class="space-y-2.5 text-xs">
                    <details class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                        <summary class="font-bold text-slate-900">Why doesn't the captive portal CNA popup appear on iPhone / Android?</summary>
                        <p class="text-slate-600 mt-2 leading-relaxed">
                            Check whether DNS is correctly handed out by DHCP. Devices need to be able to query external DNS to trigger captive detection. Verify that <code class="font-mono bg-white px-1 rounded">captive.apple.com</code> and <code class="font-mono bg-white px-1 rounded">connectivitycheck.gstatic.com</code> are allowed or correctly redirected by the Hotspot HTTP proxy.
                        </p>
                    </details>
                    <details class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                        <summary class="font-bold text-slate-900">Why does /tool fetch report "connection timeout" in RouterOS log?</summary>
                        <p class="text-slate-600 mt-2 leading-relaxed">
                            Ensure the MikroTik router has a valid default route (<code class="font-mono bg-white px-1 rounded">/ip route add gateway=...</code>) and can reach the internet directly from its local routing table.
                        </p>
                    </details>
                    <details class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                        <summary class="font-bold text-slate-900">How do I verify if user credentials were synchronized successfully?</summary>
                        <p class="text-slate-600 mt-2 leading-relaxed">
                            Run <code class="font-mono bg-white px-1 rounded">/ip hotspot user print</code> in WinBox terminal. You will see newly registered vouchers or guest member accounts listed with their assigned rate limits and profile comments.
                        </p>
                    </details>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@section('scripts')
<!-- Include html2pdf.js for 1-Click Client-Side PDF Generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function docsManager() {
    return {
        activeTab: '{{ $activeTab ?? 'pms' }}',
        generatingPdf: false,

        switchTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        },

        downloadPdf() {
            this.generatingPdf = true;
            const element = document.getElementById('documentation-content');
            
            const opt = {
                margin:       [10, 10, 10, 10],
                filename:     `wifipads-${this.activeTab}-integration-guide.pdf`,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                this.generatingPdf = false;
            }).catch(() => {
                this.generatingPdf = false;
                // Fallback to native window print if html2pdf fails
                window.print();
            });
        }
    };
}

function copyCode(btn) {
    const pre = btn.parentNode.querySelector('pre');
    if (!pre) return;
    navigator.clipboard.writeText(pre.innerText).then(() => {
        const originalText = btn.innerText;
        btn.innerText = 'Copied!';
        btn.classList.add('bg-emerald-700', 'text-white');
        setTimeout(() => {
            btn.innerText = originalText;
            btn.classList.remove('bg-emerald-700', 'text-white');
        }, 2000);
    });
}
</script>
@endsection
