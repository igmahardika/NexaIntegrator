<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Voucher - {{ $batchName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .voucher-card { page-break-inside: avoid; border: 1px dashed #94a3b8 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 p-6 font-sans text-slate-800">

    <!-- Top Action Toolbar (Hidden during Print) -->
    <div class="max-w-5xl mx-auto mb-6 p-4 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center justify-between no-print">
        <div>
            <h1 class="text-base font-extrabold text-slate-900">Cetak Voucher: {{ $batchName }}</h1>
            <p class="text-xs text-slate-500">Site: {{ $currentSite->name ?? 'Hotspot' }} | Total: {{ $vouchers->count() }} voucher</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.close()" class="px-4 py-2 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100 border border-slate-200">
                Tutup
            </button>
            <button onclick="window.print()" class="px-4 py-2 rounded-lg text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Halaman Ini
            </button>
        </div>
    </div>

    <!-- Voucher Grid (3 columns on desktop, 3 columns on A4 print) -->
    <div class="max-w-5xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($vouchers as $v)
        <div class="voucher-card bg-white rounded-xl p-4 border border-slate-300 relative shadow-xs flex flex-col justify-between">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-3">
                <div class="flex items-center gap-1.5">
                    <div class="w-6 h-6 rounded-md bg-blue-600 text-white flex items-center justify-center font-bold text-xs">
                        W
                    </div>
                    <div>
                        <span class="font-extrabold text-xs text-slate-900 block leading-tight">{{ $currentSite->name ?? 'WiFi Hotspot' }}</span>
                        <span class="text-2xs text-slate-500">Akses Internet Tamu</span>
                    </div>
                </div>
                <span class="text-2xs font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                    {{ $v->profile ? $v->profile->rate_limit : 'High Speed' }}
                </span>
            </div>

            <!-- Code Section -->
            <div class="text-center my-2 p-3 bg-slate-50 rounded-lg border border-slate-200">
                <span class="text-2xs uppercase tracking-wider font-bold text-slate-500 block">KODE VOUCHER</span>
                <span class="text-xl font-mono font-black text-slate-900 tracking-wider block mt-0.5 select-all">{{ $v->identifier }}</span>
            </div>

            <!-- Details & Instructions -->
            <div class="text-2xs text-slate-500 space-y-1 mt-2 border-t border-slate-100 pt-2">
                <div class="flex justify-between">
                    <span>Masa Aktif:</span>
                    <span class="font-bold text-slate-700">{{ $v->uptime_limit ? gmdate('H:i:s', $v->uptime_limit) : '2 Jam' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Kuota Data:</span>
                    <span class="font-bold text-slate-700">{{ $v->data_limit_bytes ? round($v->data_limit_bytes / 1048576) . ' MB' : 'Unlimited' }}</span>
                </div>
                <p class="text-center text-3xs text-slate-400 mt-2">Hubungkan ke Wi-Fi, buka browser, masukkan kode di atas.</p>
            </div>
        </div>
        @endforeach
    </div>

</body>
</html>
