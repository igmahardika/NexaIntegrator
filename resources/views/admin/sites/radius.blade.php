@extends('layouts.admin')
@section('title', 'Edge Router Integration & Provisioning — ' . $site->name)
@section('page-title', 'Edge Router Integration & Provisioning')
@section('page-subtitle', 'Local MikroTik Edge Router (Zero-Tunnel / CGNAT Friendly) & AAA RADIUS Server Management for ' . $site->name)

@section('content')
<div x-data="routerIntegrationManager()" class="space-y-6">

    <!-- Top Site Hero Banner -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-brand shrink-0 shadow-sm">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2.5 mb-1.5">
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $site->name }}</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        Edge Controller Ready
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $site->radius_enabled ? 'bg-blue-50 text-brand border border-blue-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                        {{ $site->radius_enabled ? 'RADIUS Active' : 'RADIUS Standby' }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 font-medium">
                    <span>Site Slug: <strong class="text-slate-800 font-mono">{{ $site->slug }}</strong></span>
                    <span>•</span>
                    <span>Router Gateway: <strong class="text-slate-800 font-mono">{{ $site->router_ip ?: '192.168.88.1 (Local)' }}</strong></span>
                    <span>•</span>
                    <span>Shared Secret: <strong class="text-slate-800 font-mono">{{ $site->radius_secret ? '••••••••' : 'Not configured' }}</strong></span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('admin.sites.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                &larr; Back to Sites
            </a>
            <a href="{{ route('admin.sites.template.gallery', $site) }}" class="px-4 py-2 text-xs font-semibold text-white bg-brand hover:bg-brand-hover rounded-xl shadow-sm transition">
                Template Studio &rarr;
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <button 
            @click="activeTab = 'notunnel'" 
            :class="activeTab === 'notunnel' ? 'bg-brand text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
            class="px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span>Method 1: Local MikroTik User Provisioning (Zero-Tunnel / CGNAT)</span>
            <span class="px-1.5 py-0.5 rounded text-2xs font-bold" :class="activeTab === 'notunnel' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'">Recommended</span>
        </button>

        <button 
            @click="activeTab = 'radius'" 
            :class="activeTab === 'radius' ? 'bg-brand text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
            class="px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
            </svg>
            <span>Method 2: Enterprise AAA RADIUS (Zero Flash Storage)</span>
        </button>

        <button 
            @click="activeTab = 'loginhtml'" 
            :class="activeTab === 'loginhtml' ? 'bg-brand text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
            class="px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
            </svg>
            <span>Method 3: Minimalist login.html (&lt; 1 KB)</span>
            <span class="px-1.5 py-0.5 rounded text-2xs font-bold" :class="activeTab === 'loginhtml' ? 'bg-white/20 text-white' : 'bg-blue-100 text-brand'">Zero Burden</span>
        </button>

        <button 
            @click="activeTab = 'traffic'; startTrafficMonitoring()" 
            :class="activeTab === 'traffic' ? 'bg-brand text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
            class="px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Method 4: WAN Traffic & MRTG</span>
            <span class="px-1.5 py-0.5 rounded text-2xs font-bold" :class="activeTab === 'traffic' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-700'">Real-time</span>
        </button>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 1: ZERO-TUNNEL LOCAL USER PROVISIONING -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'notunnel'" class="space-y-6" x-transition>

        <!-- Feature Highlight & Queue Live Monitor -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Metric 1: Pending Queue -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Router Sync Queue</span>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900" x-text="syncData.pending_users">...</div>
                <p class="text-2xs text-slate-500 mt-1">Pending user authorizations awaiting RouterOS fetch</p>
            </div>

            <!-- Metric 2: Synced Users -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Synchronized Users</span>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
                        ✓
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900" x-text="syncData.synced_users">...</div>
                <p class="text-2xs text-slate-500 mt-1">Active in <code class="text-emerald-700 bg-emerald-50 px-1 rounded font-mono">/ip hotspot user</code></p>
            </div>

            <!-- Metric 3: Sync Status / Trigger -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Edge Polling Heartbeat</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                    </div>
                    <div class="text-xs text-slate-700 font-medium">
                        Last Synchronization: <span class="font-mono text-slate-900 font-bold" x-text="syncData.last_synced ? formatTimestamp(syncData.last_synced) : 'Never'"></span>
                    </div>
                </div>
                <div class="pt-2">
                    <button @click="refreshSyncStatus()" :disabled="loadingStatus" class="w-full py-2 px-3 text-xs font-semibold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition flex items-center justify-center gap-1.5">
                        <span x-show="!loadingStatus">Refresh Queue Heartbeat</span>
                        <span x-show="loadingStatus" x-cloak>Connecting to Cloud...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- How It Works Architecture Card -->
        <div class="bg-gradient-to-br from-brand-hover via-brand to-slate-900 rounded-2xl p-6 text-white shadow-md">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-blue-200 text-xs font-semibold backdrop-blur-xs">
                        <span>Zero-Tunnel Architecture (Bypasses Carrier-Grade NAT & Firewalls)</span>
                    </div>
                    <h3 class="text-lg font-bold tracking-tight">How Does Local MikroTik User Provisioning Work?</h3>
                    <p class="text-xs text-blue-100/90 leading-relaxed">
                        You do <strong>not require a static public IP or VPN tunnel</strong> (WireGuard/OpenVPN).
                        When visitors fill out surveys, enter vouchers, or submit credentials on the portal, WiFiPads stages them in the edge synchronization queue.
                        The built-in MikroTik RouterOS scheduler queries the endpoint every 5 seconds via <code class="bg-black/30 px-1.5 py-0.5 rounded text-amber-300 font-mono">/tool fetch</code> and registers credentials directly into <code class="bg-black/30 px-1.5 py-0.5 rounded text-emerald-300 font-mono">/ip hotspot user</code> locally!
                    </p>
                </div>

                <div class="bg-white/10 p-4 rounded-xl backdrop-blur-xs border border-white/10 text-xs space-y-2 shrink-0 lg:w-72">
                    <div class="font-bold text-white mb-1">Architecture Benefits:</div>
                    <div class="flex items-center gap-2 text-blue-100">
                        <span class="text-emerald-400 font-bold">✓</span> Zero VPN Encryption CPU Overhead
                    </div>
                    <div class="flex items-center gap-2 text-blue-100">
                        <span class="text-emerald-400 font-bold">✓</span> Immune to ISP Mobile Port Blocking
                    </div>
                    <div class="flex items-center gap-2 text-blue-100">
                        <span class="text-emerald-400 font-bold">✓</span> Instant Handshake at <span class="font-mono text-amber-300">192.168.88.1/login</span>
                    </div>
                    <div class="flex items-center gap-2 text-blue-100">
                        <span class="text-emerald-400 font-bold">✓</span> Fail-Safe Offline Local Database
                    </div>
                </div>
            </div>
        </div>

        <!-- 1-Click MikroTik No-Tunnel Setup Script -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-mono text-xs font-bold">1</span>
                        <span>1-Click RouterOS WinBox Provisioning Script</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Copy the script below and paste into <strong>New Terminal</strong> in your MikroTik WinBox session:
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a :href="syncUrl" target="_blank" class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition flex items-center gap-1">
                        <span>View Raw .rsc</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                    <button @click="copyNoTunnelScript()" class="px-4 py-2 text-xs font-bold text-white bg-brand hover:bg-brand-hover rounded-xl shadow-sm transition flex items-center gap-1.5">
                        <template x-if="!copiedNoTunnel">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                Copy WinBox Script
                            </span>
                        </template>
                        <template x-if="copiedNoTunnel">
                            <span class="text-white font-bold flex items-center gap-1">
                                ✓ Copied to Clipboard!
                            </span>
                        </template>
                    </button>
                </div>
            </div>

            <!-- Terminal Code Box -->
            <div class="relative rounded-xl bg-slate-900 p-5 border border-slate-800 font-mono text-xs text-emerald-400 overflow-x-auto shadow-inner max-h-[460px]">
                <pre class="leading-relaxed select-all" id="noTunnelScriptBox">{{ $noTunnelScript }}</pre>
            </div>

            <!-- Visual 4-Step Guide -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-3">
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div class="text-xs font-bold text-slate-900 mb-1">1. Launch WinBox</div>
                    <div class="text-2xs text-slate-500">Connect to this site's MikroTik edge router using WinBox.</div>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div class="text-xs font-bold text-slate-900 mb-1">2. Open Terminal</div>
                    <div class="text-2xs text-slate-500">Click <strong>New Terminal</strong> from the navigation sidebar.</div>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div class="text-xs font-bold text-slate-900 mb-1">3. Paste Commands</div>
                    <div class="text-2xs text-slate-500">Right-click to paste the generated provisioning script.</div>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div class="text-xs font-bold text-emerald-700 mb-1">4. Press Enter</div>
                    <div class="text-2xs text-slate-500">Walled garden, profiles, and background scheduler activate automatically!</div>
                </div>
            </div>
        </div>

    </div>

    <!-- ================================================================= -->
    <!-- TAB 2: AAA RADIUS CLIENT CONFIGURATION (RFC 2865 / CoA) -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'radius'" class="space-y-6" x-transition x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left: RADIUS Form & CoA Test -->
            <div class="lg:col-span-6 space-y-6">
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <span>RADIUS Client Configuration</span>
                    </h3>

                    <form method="POST" action="{{ route('admin.sites.radius.update', $site) }}" class="space-y-4">
                        @csrf

                        <div class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                            <input type="checkbox" name="radius_enabled" value="1" id="radius_enabled_cb" {{ $site->radius_enabled ? 'checked' : '' }} class="accent-brand w-4 h-4 rounded cursor-pointer">
                            <label for="radius_enabled_cb" class="text-xs text-slate-800 font-semibold cursor-pointer">
                                Enable RADIUS AAA Client Authentication for This Site
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">RADIUS Server IP / FQDN</label>
                                <input type="text" name="radius_server_ip" value="{{ old('radius_server_ip', $site->radius_server_ip ?: $serverHost) }}" class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">NAS Identifier (Site ID)</label>
                                <input type="text" name="radius_nas_id" value="{{ old('radius_nas_id', $site->radius_nas_id ?: $site->slug) }}" required class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Auth Port (UDP)</label>
                                <input type="number" name="radius_auth_port" value="{{ old('radius_auth_port', $site->radius_auth_port ?: 1812) }}" required class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Acct Port (UDP)</label>
                                <input type="number" name="radius_acct_port" value="{{ old('radius_acct_port', $site->radius_acct_port ?: 1813) }}" required class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">CoA/PoD Port</label>
                                <input type="number" name="radius_coa_port" value="{{ old('radius_coa_port', $site->radius_coa_port ?: 3799) }}" required class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Shared Secret Key <span class="text-rose-500">*</span></label>
                            <input type="text" name="radius_secret" value="{{ old('radius_secret', $site->radius_secret ?: 'wifipads_secret_123') }}" required class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                            <p class="text-2xs text-slate-500 mt-1">Must match the secret entered in MikroTik RouterOS /radius settings.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Default Rate Limit (Rx/Tx)</label>
                                <input type="text" name="default_rate_limit" value="{{ old('default_rate_limit', $site->default_rate_limit ?: '5M/10M') }}" required class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand" placeholder="5M/10M">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Session Timeout (Seconds)</label>
                                <input type="number" name="default_session_timeout" value="{{ old('default_session_timeout', $site->default_session_timeout ?: 7200) }}" required class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-200 flex justify-end">
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-brand hover:bg-brand-hover rounded-xl shadow-sm transition">
                                Save RADIUS Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Packet of Disconnect (CoA) Diagnostic Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>RFC 3576 Packet of Disconnect (CoA) Test</span>
                    </h3>
                    <p class="text-xs text-slate-500 mb-4">
                        Send a test RFC 3576 Disconnect-Request packet to the MikroTik edge router on UDP port {{ $site->radius_coa_port ?: 3799 }}.
                    </p>

                    <div class="flex items-center gap-2">
                        <input type="text" x-model="testMac" placeholder="AA:BB:CC:DD:EE:FF" class="flex-1 px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand">
                        <button @click="testCoa()" :disabled="testing" class="px-4 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
                            <span x-show="!testing">Send Disconnect Packet</span>
                            <span x-show="testing" x-cloak>Transmitting...</span>
                        </button>
                    </div>

                    <template x-if="coaResult">
                        <div class="mt-3 p-3 rounded-xl text-xs font-mono border" :class="coaResult.success ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200'">
                            <div class="font-bold mb-1" x-text="coaResult.success ? '✓ Response Received:' : '✕ Disconnect Failed:'"></div>
                            <div x-text="coaResult.message || coaResult.error"></div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right: 1-Click RADIUS RouterOS Script with ROS v7/v6 Toggle -->
            <div class="lg:col-span-6 space-y-4">
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-lg bg-blue-50 text-brand flex items-center justify-center text-xs font-bold font-mono">
                                    ROS
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                                    RouterOS RADIUS Script
                                </h3>
                            </div>

                            <!-- ROS Version Toggle (v7 vs v6) -->
                            <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200">
                                <button 
                                    @click="rosVersion = 'v7'" 
                                    :class="rosVersion === 'v7' ? 'bg-white text-brand shadow-xs font-black' : 'text-slate-600 hover:text-slate-900'"
                                    class="px-2.5 py-1 text-2xs rounded-lg transition"
                                >
                                    ROS v7 (Modern)
                                </button>
                                <button 
                                    @click="rosVersion = 'v6'" 
                                    :class="rosVersion === 'v6' ? 'bg-white text-brand shadow-xs font-black' : 'text-slate-600 hover:text-slate-900'"
                                    class="px-2.5 py-1 text-2xs rounded-lg transition"
                                >
                                    ROS v6 (Legacy)
                                </button>
                            </div>

                            <button type="button" @click="copyRadiusScript()" :disabled="copiedRadius" aria-live="polite" class="px-3 py-1.5 text-xs font-bold text-brand bg-blue-50 hover:bg-blue-100 disabled:bg-emerald-50 disabled:text-emerald-700 border border-blue-200 rounded-xl transition flex items-center gap-1.5 focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none">
                                <template x-if="!copiedRadius">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        Copy Script
                                    </span>
                                </template>
                                <template x-if="copiedRadius">
                                    <span class="text-emerald-700 font-bold flex items-center gap-1">
                                        ✓ Copied to Clipboard!
                                    </span>
                                </template>
                            </button>
                        </div>

                        <p class="text-xs text-slate-500 mb-3">
                            Salin dan jalankan skrip ini langsung pada <strong class="text-slate-800">Terminal WinBox</strong> untuk mengaktifkan AAA RADIUS & Walled Garden:
                        </p>

                        <!-- Terminal Code Display Box -->
                        <div class="relative rounded-xl bg-slate-900 p-4 border border-slate-800 font-mono text-xs text-emerald-400 overflow-x-auto shadow-inner max-h-[460px]">
                            <pre x-show="rosVersion === 'v7'" class="leading-relaxed select-all" id="radiusScriptBoxV7">{{ $scriptV7 ?? $mikrotikScript }}</pre>
                            <pre x-show="rosVersion === 'v6'" class="leading-relaxed select-all" id="radiusScriptBoxV6" x-cloak>{{ $scriptV6 ?? $mikrotikScript }}</pre>
                        </div>
                    </div>
                </div>

                <!-- Live RouterOS API Hardware Diagnostics Panel -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                            <span>RouterOS API Hardware Telemetry (Port {{ $site->router_port ?: 8728 }})</span>
                        </h3>

                        <button @click="testApi()" :disabled="testingApi" class="px-3 py-1.5 text-xs font-bold text-brand bg-blue-50 hover:bg-blue-100 rounded-xl transition flex items-center gap-1">
                            <span x-show="!testingApi">⚡ Test API Connection</span>
                            <span x-show="testingApi" x-cloak>Querying Router...</span>
                        </button>
                    </div>

                    <p class="text-xs text-slate-500 mb-3">
                        Uji autentikasi API RouterOS secara real-time dan baca resource perangkat router (CPU, RAM, dan Uptime).
                    </p>

                    <template x-if="apiResult">
                        <div class="p-3.5 rounded-xl text-xs font-mono border" :class="apiResult.connected ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-rose-50 text-rose-900 border-rose-200'">
                            <div class="font-bold flex items-center gap-1.5 mb-1.5">
                                <span x-text="apiResult.connected ? '✓ Router Terhubung & Aktif' : '✕ Koneksi Router Gagal'"></span>
                                <span class="text-2xs font-normal" x-text="apiResult.latency_ms ? '(' + apiResult.latency_ms + ' ms)' : ''"></span>
                            </div>

                            <template x-if="apiResult.connected">
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 border-t border-emerald-200/60 text-2xs">
                                    <div><strong>Board:</strong> <span x-text="apiResult.board_name"></span></div>
                                    <div><strong>Version:</strong> <span x-text="apiResult.version"></span></div>
                                    <div><strong>CPU:</strong> <span x-text="apiResult.cpu_load"></span></div>
                                    <div><strong>RAM Bebas:</strong> <span x-text="apiResult.free_memory"></span></div>
                                </div>
                            </template>

                            <template x-if="!apiResult.connected">
                                <div class="text-2xs text-rose-700" x-text="apiResult.error"></div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 3: MINIMALIST LOGIN.HTML (< 1 KB) — ZERO FLASH STORAGE -->
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
                        File ringan tanpa gambar, font, atau script berat. Mencegah router MikroTik kehabisan storage flash (16MB).
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="downloadLoginHtml()" class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Download login.html</span>
                    </button>
                    <button @click="copyLoginHtml()" class="px-3.5 py-2 text-xs font-bold text-white bg-brand hover:bg-brand-hover rounded-xl shadow-sm transition flex items-center gap-1.5">
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
                        Router MikroTik (seperti RB750Gr3 hEX atau hAP) hanya memiliki memori flash 16MB. Menyimpan template portal HTML yang berat di flash router membuat router lambat, sering freeze, dan merusak chip flash. File di samping hanya berukuran <strong>750 bytes</strong> dan langsung mengarahkan tamu ke Cloud Portal WiFiPads dengan mulus!
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 space-y-2">
                        <strong class="font-bold block text-slate-900">Cara Memasang di MikroTik:</strong>
                        <ol class="list-decimal pl-4 space-y-1 text-slate-600">
                            <li>Buka <strong>WinBox</strong> dan hubungkan ke router.</li>
                            <li>Buka menu <strong>Files</strong> di sidebar WinBox.</li>
                            <li>Buka folder <strong>hotspot</strong>.</li>
                            <li>Seret (drag & drop) file <code>login.html</code> ke dalam folder <strong>hotspot</strong> untuk menimpa file lama.</li>
                            <li>Selesai! Captive portal langsung aktif dengan aman.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 4: LIVE WAN TRAFFIC & MRTG MONITOR -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'traffic'" class="space-y-6" x-transition x-cloak>
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <span>Live Internet WAN Throughput & Telemetri Hardware</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Memantau utilisasi bandwidth agregat router edge di lokasi cabang untuk perencanaan kapasitas (*capacity planning*).
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        @click="toggleTrafficPolling()" 
                        class="px-3.5 py-2 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
                        :class="trafficPolling ? 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'"
                    >
                        <span class="w-2 h-2 rounded-full" :class="trafficPolling ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500'"></span>
                        <span x-text="trafficPolling ? 'Pause Stream' : 'Start Stream (2s)'"></span>
                    </button>
                </div>
            </div>

            <!-- Current Rate Metric Badges -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 my-6">
                <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-100 flex items-center justify-between">
                    <div>
                        <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Interface WAN Aktif</div>
                        <div class="text-xl font-black text-slate-900 font-mono" x-text="trafficData.interface || 'ether1'">ether1</div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-2xs font-bold bg-blue-100 text-blue-800">Gateway WAN</span>
                </div>

                <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-100 flex items-center justify-between">
                    <div>
                        <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Download (RX Rate)</div>
                        <div class="text-2xl font-black text-emerald-700 font-mono" x-text="trafficData.rx_human || '0 bps'">0 bps</div>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">↓</span>
                </div>

                <div class="p-4 rounded-xl bg-purple-50/60 border border-purple-100 flex items-center justify-between">
                    <div>
                        <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Upload (TX Rate)</div>
                        <div class="text-2xl font-black text-purple-700 font-mono" x-text="trafficData.tx_human || '0 bps'">0 bps</div>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs">↑</span>
                </div>
            </div>

            <!-- Chart Canvas Container -->
            <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/40">
                <div class="text-xs font-bold text-slate-700 mb-3 flex items-center justify-between">
                    <span>Grafik Throughput Real-Time (Kbps/Mbps)</span>
                    <span class="text-2xs text-slate-400 font-mono">Interval Sampling: 2 Detik</span>
                </div>
                <div class="h-64 relative">
                    <canvas id="wanTrafficChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
function routerIntegrationManager() {
    return {
        activeTab: 'notunnel',
        rosVersion: 'v7',
        testMac: 'AA:BB:CC:DD:EE:FF',
        testing: false,
        testingApi: false,
        apiResult: null,
        copiedNoTunnel: false,
        copiedRadius: false,
        copiedLoginHtml: false,
        coaResult: null,
        loadingStatus: false,
        syncUrl: '{{ $syncUrl }}',
        syncData: {
            pending_users: '...',
            synced_users: '...',
            last_synced: null
        },
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
            this.refreshSyncStatus();
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

        refreshSyncStatus() {
            this.loadingStatus = true;
            fetch('{{ route('api.router.sync-status', $site->slug) }}{{ $site->radius_secret ? '?key=' . urlencode($site->radius_secret) : '' }}')
                .then(r => r.json())
                .then(data => {
                    this.syncData = data;
                    this.loadingStatus = false;
                })
                .catch(() => {
                    this.loadingStatus = false;
                });
        },

        formatTimestamp(isoString) {
            try {
                const d = new Date(isoString);
                return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' (' + d.toLocaleDateString('en-US') + ')';
            } catch (e) {
                return isoString;
            }
        },

        copyNoTunnelScript() {
            const scriptText = document.getElementById('noTunnelScriptBox').innerText;
            navigator.clipboard.writeText(scriptText).then(() => {
                this.copiedNoTunnel = true;
                setTimeout(() => this.copiedNoTunnel = false, 3000);
            });
        },

        copyRadiusScript() {
            const elId = this.rosVersion === 'v7' ? 'radiusScriptBoxV7' : 'radiusScriptBoxV6';
            const el = document.getElementById(elId) || document.getElementById('radiusScriptBoxV7');
            const scriptText = el ? el.innerText : '';
            navigator.clipboard.writeText(scriptText).then(() => {
                this.copiedRadius = true;
                setTimeout(() => this.copiedRadius = false, 3000);
            });
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

        testCoa() {
            this.testing = true;
            this.coaResult = null;

            fetch('{{ route('admin.sites.radius.test-coa', $site) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ mac: this.testMac })
            })
            .then(res => res.json())
            .then(data => {
                this.coaResult = data;
                this.testing = false;
            })
            .catch(err => {
                this.coaResult = { success: false, error: err.message };
                this.testing = false;
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
