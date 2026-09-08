<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WiFiPads — Integration & API Documentation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#22449E',
                            hover: '#1b3680',
                        }
                    },
                    fontSize: {
                        '2xs': ['0.625rem', { lineHeight: '0.875rem' }],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }
        code, pre {
            font-family: 'JetBrains Mono', monospace;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
            }
            .page-break {
                page-break-before: always;
            }
            @page {
                margin: 1.5cm;
                size: A4 portrait;
            }
        }
    </style>
</head>
<body class="p-6 md:p-12">

    <!-- Floating Print Control Bar (Screen only) -->
    <div class="no-print fixed top-5 right-5 z-50 flex items-center gap-3 bg-white p-3 rounded-2xl shadow-xl border border-slate-200">
        <button onclick="window.print()" class="px-4 py-2 text-xs font-bold text-white bg-brand hover:bg-brand-hover rounded-xl transition flex items-center gap-1.5 shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Print or Save to PDF</span>
        </button>
        <button onclick="window.close()" class="px-3 py-2 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
            Close
        </button>
    </div>

    <!-- Document Wrapper -->
    <div class="max-w-4xl mx-auto bg-white p-8 md:p-14 rounded-2xl border border-slate-200 shadow-sm print:border-none print:shadow-none print:p-0 space-y-12">

        <!-- Document Header -->
        <div class="border-b-2 border-slate-900 pb-6 flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded bg-brand text-white text-2xs font-black tracking-wider uppercase">WIFIPADS CONTROLLER</span>
                    <span class="text-slate-500 text-xs font-mono">• Official Technical Documentation</span>
                </div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Enterprise Integration & API Manual</h1>
                <p class="text-slate-500 text-xs mt-1">Multi-Tenant Network Access Control & Edge Gateway Controller</p>
            </div>
            <div class="text-right text-xs text-slate-500 font-mono hidden sm:block">
                <div>Date: {{ date('F d, Y') }}</div>
                <div>Status: Production Ready</div>
            </div>
        </div>

        <!-- ================= TOPIC 1: PMS API INTEGRATION ================= -->
        @if($topic === 'pms' || $topic === 'all')
        <section class="space-y-6 {{ $topic === 'all' ? '' : '' }}">
            <div class="border-b border-slate-200 pb-3">
                <span class="text-xs font-bold text-brand uppercase tracking-wider">Module 1</span>
                <h2 class="text-xl font-extrabold text-slate-900">Hotel Property Management System (PMS) API</h2>
                <p class="text-xs text-slate-600 mt-1">Compatible with Oracle Hospitality (Opera Cloud/V5), Infor HMS, Cloudbeds, VHP, and Mews.</p>
            </div>

            <!-- Workflow -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                <div class="font-bold text-slate-900 uppercase tracking-wider text-2xs">Workflow Summary:</div>
                <div class="font-mono text-slate-700 leading-relaxed text-2xs">
                    1. Guest connects Wi-Fi &rarr; Captive portal prompts Room # &amp; Last Name.<br>
                    2. WiFiPads executes POST /api/v1/pms/verify-guest against PMS.<br>
                    3. Upon confirmation, credentials are staged in RouterOS sync queue.<br>
                    4. Guest checkout triggers automated Disconnect-Request (RFC 3576 CoA).
                </div>
            </div>

            <!-- Endpoints -->
            <div class="space-y-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm mb-1">A. Verify Guest Room &amp; Last Name</h3>
                    <div class="font-mono text-xs text-slate-700 mb-2">Endpoint: <strong class="text-emerald-700">POST</strong> /api/v1/pms/verify-guest</div>
                    <div class="bg-slate-950 text-emerald-400 p-4 rounded-xl font-mono text-xs overflow-x-auto">
<pre>{
  "room_number": "302",
  "last_name": "Smith",
  "client_mac": "AA:BB:CC:11:22:33",
  "site_slug": "hotel-bali"
}</pre>
                    </div>
                </div>

                <div>
                    <div class="text-2xs font-bold text-slate-600 mb-1">Response Payload (200 OK):</div>
                    <div class="bg-slate-950 text-cyan-400 p-4 rounded-xl font-mono text-xs overflow-x-auto">
<pre>{
  "status": "success",
  "verified": true,
  "guest": {
    "pms_guest_id": "OPERA-98210",
    "full_name": "John Smith",
    "room_number": "302",
    "checkout_date": "2026-09-12T12:00:00Z",
    "vip_tier": "gold"
  },
  "provisioning": {
    "bandwidth_profile": "VIP-10M",
    "session_timeout_seconds": 86400
  }
}</pre>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- ================= TOPIC 2: MIKROTIK ROUTEROS INTEGRATION ================= -->
        @if($topic === 'mikrotik' || $topic === 'all')
        <section class="space-y-6 {{ $topic === 'all' ? 'page-break pt-8' : '' }}">
            <div class="border-b border-slate-200 pb-3">
                <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Module 2</span>
                <h2 class="text-xl font-extrabold text-slate-900">MikroTik RouterOS Network Integration</h2>
                <p class="text-xs text-slate-600 mt-1">Network architecture, port bindings, and walled garden specifications.</p>
            </div>

            <!-- Architecture Comparison Table -->
            <table class="w-full text-xs text-left border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 font-bold text-slate-800 uppercase text-2xs">
                    <tr>
                        <th class="p-3 border-b">Feature</th>
                        <th class="p-3 border-b text-emerald-800">Method 1: Zero-Tunnel (Reverse Polling)</th>
                        <th class="p-3 border-b text-blue-800">Method 2: AAA RADIUS (RFC 2865)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr>
                        <td class="p-2.5 font-bold">Public IP</td>
                        <td class="p-2.5 font-semibold text-emerald-700">Not required (Bypasses CGNAT &amp; LTE)</td>
                        <td class="p-2.5">Required on Cloud RADIUS server</td>
                    </tr>
                    <tr>
                        <td class="p-2.5 font-bold">Router Load</td>
                        <td class="p-2.5 font-semibold text-emerald-700">0% CPU encryption overhead</td>
                        <td class="p-2.5">Minimal UDP authentication packet load</td>
                    </tr>
                    <tr>
                        <td class="p-2.5 font-bold">Best For</td>
                        <td class="p-2.5">Retail chains, cafes, multi-site hotels</td>
                        <td class="p-2.5">Enterprise private WAN campus networks</td>
                    </tr>
                </tbody>
            </table>

            <!-- Walled Garden -->
            <div class="space-y-2">
                <h3 class="font-bold text-slate-900 text-sm">Walled Garden Allowlist Entries:</h3>
                <div class="p-4 bg-slate-900 text-emerald-400 font-mono text-xs rounded-xl overflow-x-auto">
<pre>/ip hotspot walled-garden
add dst-host={{ $serverHost }} comment="WiFiPads Cloud Controller"
add dst-host="*.wifipads.com" comment="WiFiPads CDN"
add dst-host=captive.apple.com comment="Apple CNA"
add dst-host=connectivitycheck.gstatic.com comment="Android CNA"
add dst-host=msftconnecttest.com comment="Windows CNA"</pre>
                </div>
            </div>
        </section>
        @endif

        <!-- ================= TOPIC 3: STEP-BY-STEP GUIDE ================= -->
        @if($topic === 'step-by-step' || $topic === 'all')
        <section class="space-y-6 {{ $topic === 'all' ? 'page-break pt-8' : '' }}">
            <div class="border-b border-slate-200 pb-3">
                <span class="text-xs font-bold text-purple-700 uppercase tracking-wider">Module 3</span>
                <h2 class="text-xl font-extrabold text-slate-900">Step-by-Step Edge Router Provisioning Guide</h2>
                <p class="text-xs text-slate-600 mt-1">Walkthrough for configuring MikroTik RouterOS hardware in under 5 minutes.</p>
            </div>

            <div class="space-y-4 text-xs">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                    <div class="font-bold text-slate-900 text-sm">Step 1: Setup Hotspot Bridge</div>
                    <p class="text-slate-600">Run <code class="font-mono bg-white px-1.5 py-0.5 rounded border">/ip hotspot setup</code> and bind to your client access bridge interface.</p>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                    <div class="font-bold text-slate-900 text-sm">Step 2: Execute 1-Click Provisioning Script</div>
                    <p class="text-slate-600">Copy the provisioning script from the WiFiPads console and execute it in WinBox New Terminal:</p>
                    <div class="bg-slate-900 text-emerald-400 p-3 rounded-lg font-mono text-2xs overflow-x-auto">
<pre>/system scheduler add name="wifipads-sync" interval=5s on-event={
    /tool fetch url="{{ $baseUrl }}/api/router/site-slug/sync.rsc" dst-path="wifipads-sync.rsc" keep-result=yes
    /import file-name="wifipads-sync.rsc"
}</pre>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                    <div class="font-bold text-slate-900 text-sm">Step 3: Point Redirection to Cloud Portal</div>
                    <p class="text-slate-600">Edit <code class="font-mono bg-white px-1.5 py-0.5 rounded border">hotspot/login.html</code> on the router flash to redirect clients to:</p>
                    <div class="bg-slate-900 text-cyan-400 p-2.5 rounded-lg font-mono text-2xs">
                        {{ $baseUrl }}/portal?loc={{ $currentLocation->slug ?? 'default-location' }}
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                    <div class="font-bold text-slate-900 text-sm">Step 4: Live Verification</div>
                    <p class="text-slate-600">Connect a test client smartphone. The captive assistant window should pop up immediately presenting the active login method.</p>
                </div>
            </div>
        </section>
        @endif

        <!-- Footer -->
        <div class="pt-8 border-t border-slate-200 text-center text-xs text-slate-500 font-mono">
            &copy; {{ date('Y') }} WiFiPads Network Access Control. All rights reserved.
        </div>

    </div>

</body>
</html>
