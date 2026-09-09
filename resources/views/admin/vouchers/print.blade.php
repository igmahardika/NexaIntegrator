<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Voucher — {{ $batchName }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            background: #fff;
            color: #000;
            padding: 16px;
        }
        .print-header {
            text-align: center;
            padding: 12px;
            border-bottom: 2px dashed #000;
            margin-bottom: 16px;
        }
        .print-header h1 { font-size: 1.4rem; font-weight: 900; }
        .print-header p  { font-size: 0.7rem; color: #555; margin-top: 4px; }

        .voucher-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .voucher-card {
            border: 1.5px solid #000;
            border-radius: 8px;
            padding: 12px 10px;
            page-break-inside: avoid;
            position: relative;
            overflow: hidden;
        }
        .voucher-card::before {
            content: 'WiFiPads';
            position: absolute;
            top: 4px; right: 6px;
            font-size: 0.55rem;
            font-weight: 900;
            letter-spacing: 0.1em;
            color: #ccc;
        }

        .vc-header {
            font-size: 0.55rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #777;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .vc-code {
            font-size: 1.05rem;
            font-weight: 900;
            letter-spacing: 0.15em;
            text-align: center;
            padding: 6px 0;
            background: #f5f5f5;
            border-radius: 4px;
            margin-bottom: 8px;
            border: 1px dashed #999;
        }

        .qr-placeholder {
            width: 64px; height: 64px;
            margin: 0 auto 6px;
        }

        .vc-meta {
            font-size: 0.58rem;
            color: #555;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3px;
        }
        .vc-meta span { font-weight: 700; color: #000; }
        .vc-status {
            font-size: 0.55rem;
            text-align: center;
            margin-top: 6px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
        }
        .vc-status.used { background: #fee2e2; color: #dc2626; }
        .vc-status.ok   { background: #d1fae5; color: #065f46; }

        @media print {
            body { padding: 8px; }
            .no-print { display: none !important; }
            .voucher-card { border: 1.5px solid #000 !important; }
            @page { margin: 10mm; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom:16px; display:flex; gap:10px; align-items:center;">
    <button onclick="window.print()" style="background:#22449E; color:#fff; border:none; padding:9px 20px; border-radius:8px; cursor:pointer; font-weight:700; display:inline-flex; align-items:center; gap:6px; font-family:sans-serif; font-size:13px; box-shadow:0 2px 8px rgba(34,68,158,0.3);">
        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        <span>Print Voucher</span>
    </button>
    <button onclick="window.close()" style="background:#f3f4f6; color:#374151; border:1px solid #d1d5db; padding:9px 16px; border-radius:8px; cursor:pointer; font-family:sans-serif; font-size:13px; font-weight:600;">
        Tutup
    </button>
    <span style="font-size:0.8rem; color:#4b5563; font-family:sans-serif;">
        Batch: <strong>{{ $batchName }}</strong> &bull; Total: <strong>{{ $vouchers->count() }} voucher</strong>
    </span>
</div>

<div class="print-header">
    <div style="display:inline-flex; align-items:center; gap:8px; justify-content:center;">
        <svg style="width:24px;height:24px;color:#22449E;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
        </svg>
        <h1 style="display:inline; margin:0; font-size:1.4rem;">WiFiPads Hotspot Voucher</h1>
    </div>
    <p>Batch: {{ $batchName }} &bull; Dicetak: {{ now()->format('d M Y H:i') }} WIB</p>
</div>

<div class="voucher-grid">
    @foreach($vouchers as $v)
    <div class="voucher-card">
        <div class="vc-header">
            <span>Akses Internet WiFi</span>
            <span style="float:right;">✂ Potong</span>
        </div>

        <!-- QR Code for fast scan login -->
        <div style="text-align:center;">
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=72x72&data={{ urlencode(route('portal', ['voucher' => $v->code])) }}&margin=2"
                alt="QR {{ $v->code }}"
                class="qr-placeholder"
                onerror="this.style.display='none'"
            >
        </div>

        <div class="vc-code">{{ $v->code }}</div>

        <div class="vc-meta">
            <div>Durasi:<br><span>{{ $v->duration_minutes >= 60 ? ($v->duration_minutes / 60) . ' jam' : $v->duration_minutes . ' mnt' }}</span></div>
            <div>Kecepatan:<br><span>{{ $v->rate_limit }}</span></div>
            <div>Lokasi:<br><span>{{ $v->location?->name ?? 'Semua' }}</span></div>
            <div>Kedaluwarsa:<br><span>{{ $v->expired_at ? $v->expired_at->format('d/m/Y') : 'Tanpa batas' }}</span></div>
        </div>

        <div class="vc-status {{ $v->is_used ? 'used' : 'ok' }}">
            {{ $v->is_used ? '[✗ TERPAKAI]' : '[✓ TERSEDIA]' }}
        </div>
    </div>
    @endforeach
</div>

<script>
// Auto trigger print on load (optional)
// window.addEventListener('load', function() { window.print(); });
</script>
</body>
</html>
