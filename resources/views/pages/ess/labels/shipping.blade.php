<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label kirim {{ $order->reference_no }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            background: #f1f5f9;
            color: #0f172a;
        }
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            max-width: 100mm;
            margin: 0 auto 16px;
            padding: 14px 16px;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            background: #ffffff;
        }
        .toolbar p { margin: 0; font-weight: 600; font-size: 13px; }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 16px;
            font-size: 13px;
            font-weight: 600;
            border: 0;
            border-radius: 8px;
            background: #1d4ed8;
            color: #ffffff;
            cursor: pointer;
            text-decoration: none;
        }
        .sheet {
            width: 100mm;
            margin: 0 auto;
            padding: 8mm;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }
        .head { display: flex; align-items: center; justify-content: space-between; gap: 10px; border-bottom: 2px solid #0f172a; padding-bottom: 8px; }
        .head img { width: 34px; height: auto; }
        .head .name { font-size: 14px; font-weight: 800; letter-spacing: -0.02em; }
        .head .tag { font-size: 8px; color: #64748b; }
        .courier { margin-top: 10px; padding: 8px 10px; border-radius: 6px; background: #0f172a; color: #ffffff; }
        .courier .label { font-size: 8px; letter-spacing: 0.14em; text-transform: uppercase; color: #cbd5e1; }
        .courier .value { font-size: 16px; font-weight: 800; }
        .block { margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0; }
        .block .label { font-size: 8px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: #64748b; }
        .block .value { margin-top: 2px; font-size: 13px; font-weight: 700; }
        .block .sub { margin-top: 2px; font-size: 10px; color: #475569; }
        .barcode { margin-top: 12px; text-align: center; }
        .barcode svg { width: 100%; height: auto; max-height: 26mm; }
        .code { font-family: ui-monospace, Menlo, monospace; font-size: 12px; font-weight: 700; text-align: center; letter-spacing: 0.04em; }
        .meta { display: flex; justify-content: space-between; gap: 10px; margin-top: 10px; padding-top: 8px; border-top: 1px dashed #cbd5e1; font-size: 9px; color: #64748b; }
        @media print {
            body { padding: 0; background: #ffffff; }
            .no-print { display: none !important; }
            .sheet { border: 0; border-radius: 0; padding: 0; width: auto; }
            @page { size: 100mm 150mm; margin: 6mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <p>Label kirim {{ $order->reference_no }}</p>
        <div style="display:flex;gap:8px;">
            <a class="button" href="{{ wms_route('sales-orders.show', $order) }}" style="background:#ffffff;color:#334155;box-shadow:inset 0 0 0 1px #cbd5e1;">Kembali</a>
            <button type="button" class="button" onclick="window.print()">Cetak</button>
        </div>
    </div>

    <div class="sheet">
        <div class="head">
            <div style="display:flex;align-items:center;gap:8px;">
                <img src="{{ asset('images/logo.webp') }}" alt="Rumitech">
                <div>
                    <p class="name">Rumitech</p>
                    <p class="tag">Warehouse Management System</p>
                </div>
            </div>
            <p style="margin:0;font-size:10px;font-weight:700;text-align:right;">{{ $order->reference_no }}</p>
        </div>

        <div class="courier">
            <p class="label">Ekspedisi</p>
            <p class="value">{{ $order->shipment?->courier?->name ?? 'Belum ditentukan' }}</p>
        </div>

        <div class="block">
            <p class="label">Penerima</p>
            <p class="value">{{ $order->customer_name }}</p>
            <p class="sub">{{ $order->customer_phone ?: 'Nomor telepon belum diisi' }}</p>
        </div>

        <div class="block">
            <p class="label">Nomor resi</p>
            <p class="value">{{ $order->shipment?->tracking_number ?? $code }}</p>
        </div>

        <div class="barcode">{!! $barcode !!}</div>
        <p class="code">{{ $code }}</p>

        <div class="meta">
            <span>{{ number_format((float) $order->items->sum('qty_picked'), 2, ',', '.') }} unit</span>
            <span>{{ $order->items->count() }} baris</span>
            <span>{{ $order->shipped_at?->format('d/m/Y') ?? $order->created_at?->format('d/m/Y') }}</span>
        </div>
    </div>
</body>
</html>
