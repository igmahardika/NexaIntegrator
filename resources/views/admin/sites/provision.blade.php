@extends('layouts.admin')
@section('title', 'Site Provisioning Handover — ' . $site->name)
@section('page-title', 'Site Provisioning & Router Handover')
@section('page-subtitle', 'Site berhasil didaftarkan. Salin script konfigurasi MikroTik dan unduh template login.html di bawah ini.')

@section('content')
<div x-data="provisionManager()" class="max-w-5xl mx-auto space-y-6">

    <!-- Success Confirmation Header Card -->
    <div class="card p-6 bg-white border border-slate-200/80 shadow-xs">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100/80 border border-emerald-200 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-black text-slate-900">{{ $site->name }}</h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-bold uppercase {{ $site->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $site->is_active ? 'Active' : 'Disabled' }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-bold uppercase bg-blue-100 text-brand">
                            {{ $site->gateway_mode_label }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Database tenant terisolasi telah aktif. Siap menerima koneksi dari router MikroTik.
                    </p>
                </div>
            </div>

            <!-- Portal Test Link Button -->
            <a href="{{ route('portal', ['loc' => $site->slug]) }}" target="_blank"
                class="btn-secondary text-xs font-semibold flex items-center gap-2 shrink-0">
                <span>Buka Captive Portal</span>
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
        </div>

        <!-- Site Parameters Summary Pills -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5 pt-4 border-t border-slate-100 text-xs">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                <span class="text-2xs text-slate-500 font-bold uppercase tracking-wider block mb-0.5">Slug Site</span>
                <span class="font-mono font-bold text-slate-900">{{ $site->slug }}</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                <span class="text-2xs text-slate-500 font-bold uppercase tracking-wider block mb-0.5">Kategori Venue</span>
                <span class="font-bold text-slate-900 capitalize">{{ $site->business_type }}</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                <span class="text-2xs text-slate-500 font-bold uppercase tracking-wider block mb-0.5">Template Aktif</span>
                <span class="font-bold text-slate-900 capitalize">{{ str_replace('-', ' ', $site->active_template) }}</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                <span class="text-2xs text-slate-500 font-bold uppercase tracking-wider block mb-0.5">DNS Hotspot</span>
                <span class="font-mono font-bold text-brand">{{ $site->dns_name ?: 'wifi.login' }}</span>
            </div>
        </div>
    </div>

    <!-- Quick WinBox Setup Guide & Download Bar -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Steps Guide Card -->
        <div class="md:col-span-2 card p-6 bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-brand font-bold text-xs">
                    i
                </div>
                <h3 class="font-bold text-sm text-slate-900">Cara Menerapkan Konfigurasi di MikroTik (WinBox)</h3>
            </div>

            <ol class="space-y-3 text-xs text-slate-700">
                <li class="flex items-start gap-3">
                    <span class="w-5 h-5 rounded-full bg-brand text-white flex items-center justify-center font-bold text-2xs shrink-0 mt-0.5">1</span>
                    <div>
                        <strong class="text-slate-900">Buka WinBox</strong> dan lakukan koneksi ke router MikroTik venue (via IP / MAC address).
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-5 h-5 rounded-full bg-brand text-white flex items-center justify-center font-bold text-2xs shrink-0 mt-0.5">2</span>
                    <div>
                        Buka menu <strong class="text-slate-900">New Terminal</strong>, klik tombol <strong>"Salin Script WinBox"</strong> di bawah, lalu paste ke terminal dan tekan <strong>Enter</strong>.
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-5 h-5 rounded-full bg-brand text-white flex items-center justify-center font-bold text-2xs shrink-0 mt-0.5">3</span>
                    <div>
                        Klik tombol <strong>"Download login.html"</strong> di samping, lalu upload file tersebut ke folder <code class="bg-slate-100 text-brand px-1.5 py-0.5 rounded font-mono text-2xs">hotspot/login.html</code> pada menu <strong>Files</strong> di WinBox.
                    </div>
                </li>
            </ol>
        </div>

        <!-- Download login.html Card -->
        <div class="card p-6 bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-brand mb-3">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </div>
                <h3 class="font-bold text-sm text-slate-900 mb-1">Redirect Template HTML</h3>
                <p class="text-2xs text-slate-500 leading-relaxed">
                    File redirect ultra-ringan (&lt;1 KB) yang langsung mengarahkan browser pengunjung ke Cloud Captive Portal.
                </p>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.radius.download-login-html', $site) }}"
                    class="btn-primary w-full text-xs font-semibold flex items-center justify-center gap-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Download login.html</span>
                </a>
            </div>
        </div>

    </div>

    <!-- Terminal Code Block (1-Click Copy) -->
    <div class="card overflow-hidden bg-slate-900 border border-slate-800 shadow-lg rounded-2xl">
        <!-- Terminal Header -->
        <div class="px-5 py-3.5 bg-slate-950 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-rose-500/80 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-amber-500/80 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-emerald-500/80 inline-block"></span>
                </div>
                <span class="text-2xs font-mono text-slate-400 font-bold uppercase tracking-wider">
                    MikroTik WinBox Terminal — {{ $site->gateway_mode_label }} Script (.rsc)
                </span>
            </div>

            <!-- Copy Button -->
            <button
                type="button"
                @click="copyScript"
                class="px-3 py-1.5 rounded-lg bg-brand hover:bg-brand-hover text-white text-xs font-semibold flex items-center gap-1.5 transition-all shadow-xs cursor-pointer"
            >
                <template x-if="copied">
                    <svg class="w-3.5 h-3.5 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </template>
                <template x-if="!copied">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                    </svg>
                </template>
                <span x-text="copied ? 'Script Disalin!' : 'Salin Script WinBox'"></span>
            </button>
        </div>

        <!-- Terminal Body -->
        <div class="p-5 overflow-x-auto text-xs font-mono leading-relaxed max-h-[460px] overflow-y-auto">
            <pre class="text-emerald-400 whitespace-pre" id="scriptContent">{{ $script }}</pre>
        </div>
    </div>

    <!-- Action Shortcuts Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 pt-2">
        <a href="{{ route('admin.sites.index') }}" class="btn-secondary text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Daftar Sites</span>
        </a>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.sites.template.customizer', $site) }}" class="btn-secondary text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
                <span>Kustomisasi Desain Portal</span>
            </a>

            <a href="{{ route('admin.hotspot-users.index') }}" class="btn-primary text-xs font-semibold flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Mulai Buat Voucher & User</span>
            </a>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
function provisionManager() {
    return {
        copied: false,
        copyScript() {
            const scriptEl = document.getElementById('scriptContent');
            if (scriptEl) {
                const text = scriptEl.innerText;
                navigator.clipboard.writeText(text).then(() => {
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 3000);
                }).catch(() => {
                    // Fallback
                    const ta = document.createElement('textarea');
                    ta.value = text;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 3000);
                });
            }
        }
    };
}
</script>
@endsection
