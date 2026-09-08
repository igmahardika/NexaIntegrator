@extends('layouts.admin')
@section('title', 'Edge Gateways & Hardware')
@section('page-title', 'Edge Gateways & Hardware')
@section('page-subtitle', 'Manage edge router hardware, MikroTik RouterOS API credentials, and gateway telemetry')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="locationManager()">

    <!-- Add Location Form -->
    <div class="card p-6 bg-white border border-slate-200/80 shadow-xs h-fit">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#22449E] flex items-center justify-center font-bold">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Add Edge Gateway</h3>
        </div>

        <form method="POST" action="{{ route('admin.locations.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label text-slate-700 font-semibold">Location / Gateway Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" class="input" required value="{{ old('name') }}" placeholder="e.g. Jakarta HQ Gateway">
            </div>
            <div>
                <label class="label text-slate-700 font-semibold">Address / Deployment Notes</label>
                <textarea name="address" class="input" rows="2" placeholder="Building A, 4th Floor Server Room...">{{ old('address') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label text-slate-700 font-semibold">Router IP</label>
                    <input type="text" name="router_ip" class="input font-mono" value="{{ old('router_ip', '192.168.88.1') }}" placeholder="192.168.88.1">
                </div>
                <div>
                    <label class="label text-slate-700 font-semibold">API Port</label>
                    <input type="number" name="router_port" class="input font-mono" value="{{ old('router_port', 8728) }}">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label text-slate-700 font-semibold">API Username</label>
                    <input type="text" name="router_user" class="input font-mono" value="{{ old('router_user', 'admin') }}">
                </div>
                <div>
                    <label class="label text-slate-700 font-semibold">API Password</label>
                    <input type="password" name="router_password" class="input font-mono" placeholder="••••••••">
                </div>
            </div>
            <div>
                <label class="label text-slate-700 font-semibold">Hotspot DNS Hostname</label>
                <input type="text" name="dns_name" class="input font-mono" value="{{ old('dns_name') }}" placeholder="wifi.login">
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" value="1" id="la" checked class="w-4 h-4 rounded border-slate-300 text-[#22449E]">
                <label for="la" class="text-xs text-slate-700 select-none cursor-pointer font-medium">Gateway is active</label>
            </div>
            <button type="submit" class="btn-primary w-full shadow-sm flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>Save Gateway Location</span>
            </button>
        </form>
    </div>

    <!-- Location List -->
    <div class="lg:col-span-2 space-y-4">
        @forelse($locations as $location)
        <div class="card p-5 bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h4 class="font-bold text-slate-900 text-sm">{{ $location->name }}</h4>
                        @if($location->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Disabled</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mb-3">{{ $location->address ?? 'No physical address specified' }}</p>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <div>
                            <div class="text-slate-500 font-medium text-[11px]">Router IP</div>
                            <div class="text-slate-900 font-mono font-bold">{{ $location->router_ip ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-slate-500 font-medium text-[11px]">API Port</div>
                            <div class="text-slate-900 font-mono font-bold">{{ $location->router_port }}</div>
                        </div>
                        <div>
                            <div class="text-slate-500 font-medium text-[11px]">Vouchers</div>
                            <div class="text-slate-900 font-semibold">{{ $location->vouchers_count }}</div>
                        </div>
                        <div>
                            <div class="text-slate-500 font-medium text-[11px]">Sessions</div>
                            <div class="text-slate-900 font-semibold">{{ $location->sessions_count }}</div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 flex-shrink-0">
                    <!-- Test Connection -->
                    <button
                        @click="testConnection('{{ $location->id }}', '{{ $location->name }}')"
                        class="btn-secondary text-xs py-1.5 px-3 flex items-center justify-center gap-1.5 font-semibold"
                        :disabled="testing === '{{ $location->id }}'"
                    >
                        <span x-show="testing !== '{{ $location->id }}'" class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-[#22449E]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Test Ping
                        </span>
                        <span x-show="testing === '{{ $location->id }}'" x-cloak class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Testing...
                        </span>
                    </button>

                    <!-- Telemetry Health -->
                    <button
                        @click="checkHealth('{{ $location->id }}')"
                        class="btn-secondary text-xs py-1.5 px-3 flex items-center justify-center gap-1 font-semibold"
                    >
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Telemetry
                    </button>

                    <!-- Delete -->
                    <form method="POST" action="{{ route('admin.locations.destroy', $location) }}"
                        onsubmit="return confirm('Delete gateway location {{ $location->name }} permanently?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger w-full text-center text-xs py-1">✕</button>
                    </form>
                </div>
            </div>

            <!-- Connection Result -->
            <div x-show="results['{{ $location->id }}']" x-cloak class="mt-3 pt-3 border-t border-slate-100">
                <div :class="results['{{ $location->id }}']?.connected ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200'"
                    class="text-xs font-semibold p-2.5 rounded-xl border flex items-start gap-2">
                    <span x-text="results['{{ $location->id }}']?.connected ? '✓' : '✕'"></span>
                    <span x-text="results['{{ $location->id }}']?.message"></span>
                </div>
            </div>

            <!-- Health Result -->
            <div x-show="healthData['{{ $location->id }}']" x-cloak class="mt-3 pt-3 border-t border-slate-100">
                <div class="grid grid-cols-3 gap-3 text-xs bg-slate-50 p-3 rounded-xl border border-slate-200/60">
                    <div>
                        <div class="text-slate-500 font-medium text-[11px]">CPU Load</div>
                        <div class="text-slate-900 font-bold" x-text="(healthData['{{ $location->id }}']?.cpu_load ?? 0) + '%'"></div>
                    </div>
                    <div>
                        <div class="text-slate-500 font-medium text-[11px]">Memory Usage</div>
                        <div class="text-slate-900 font-bold" x-text="(healthData['{{ $location->id }}']?.memory_pct ?? 0) + '%'"></div>
                    </div>
                    <div>
                        <div class="text-slate-500 font-medium text-[11px]">Uptime</div>
                        <div class="text-slate-900 font-bold" x-text="healthData['{{ $location->id }}']?.uptime ?? '—'"></div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card p-12 text-center text-slate-500 bg-white border border-slate-200/80">
            <div class="w-12 h-12 mx-auto mb-2 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <p class="text-xs font-semibold text-slate-700">No edge gateways configured yet.</p>
            <p class="text-xs text-slate-500 mt-1">Add your first edge router gateway using the form on the left.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
function locationManager() {
    return {
        testing: null,
        results: {},
        healthData: {},

        testConnection(locationId, name) {
            this.testing = locationId;
            this.results[locationId] = null;

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');

            fetch(`/admin/locations/${locationId}/test`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMeta?.content || '',
                },
            })
            .then(r => r.json())
            .then(data => {
                this.results[locationId] = {
                    connected: data.connected,
                    message: data.connected
                        ? `Connected to ${name} · Latency: ${data.latency_ms}ms · RouterOS: ${data.ros_version || 'unknown'}`
                        : `Failed to connect: ${data.error || 'Router unreachable'}`,
                };
            })
            .catch(e => {
                this.results[locationId] = { connected: false, message: 'Error: ' + e.message };
            })
            .finally(() => { this.testing = null; });
        },

        checkHealth(locationId) {
            fetch(`/admin/locations/${locationId}/health`)
                .then(r => r.json())
                .then(data => {
                    this.healthData[locationId] = data;
                })
                .catch(e => {
                    alert('Failed to retrieve hardware health telemetry: ' + e.message);
                });
        }
    };
}
</script>
@endsection
