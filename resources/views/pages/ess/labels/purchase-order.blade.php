<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase order {{ $order->reference_no }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 12px;
        }
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            max-width: 210mm;
            margin: 0 auto 16px;
            padding: 14px 16px;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            background: #ffffff;
        }
        .toolbar p { margin: 0; font-weight: 600; }
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
        .sheet { max-width: 210mm; margin: 0 auto; padding: 14mm; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; }
        .head { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 12px; }
        .brand { display: flex; align-items: center; gap: 10px; }
        .brand img { width: 46px; height: auto; }
        .brand .name { font-size: 16px; font-weight: 800; letter-spacing: -0.02em; }
        .brand .tag { font-size: 10px; color: #64748b; }
        .doc { text-align: right; }
        .doc h1 { margin: 0; font-size: 18px; letter-spacing: 0.04em; text-transform: uppercase; }
        .doc p { margin: 3px 0 0; color: #475569; }
        .info { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 24px; margin: 16px 0; }
        .info dt { font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #64748b; }
        .info dd { margin: 3px 0 0; font-size: 12px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #f1f5f9; padding: 8px; text-align: left; font-size: 9px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #475569; border-bottom: 1px solid #cbd5e1; }
        tbody td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .num { text-align: right; white-space: nowrap; }
        .product { font-weight: 600; }
        .sku { font-size: 10px; color: #64748b; }
        .footer { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; }
        .barcode { width: 60mm; }
        .barcode svg { width: 100%; height: auto; }
        .sign { display: flex; gap: 40px; text-align: center; font-size: 10px; color: #475569; }
        .sign div { width: 45mm; }
        .sign .line { margin-top: 44px; border-top: 1px solid #94a3b8; padding-top: 4px; }
        .note { margin-top: 16px; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; font-size: 11px; color: #475569; }
        @media print {
            body { padding: 0; background: #ffffff; }
            .no-print { display: none !important; }
            .sheet { border: 0; border-radius: 0; padding: 0; max-width: none; }
            @page { size: A4; margin: 14mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <p>Purchase order {{ $order->reference_no }}</p>
        <div style="display:flex;gap:8px;">
            <a class="button" href="{{ wms_route('purchase-orders.show', $order) }}" style="background:#ffffff;color:#334155;box-shadow:inset 0 0 0 1px #cbd5e1;">Kembali</a>
            <button type="button" class="button" onclick="window.print()">Cetak</button>
        </div>
    </div>

    <div class="sheet">
        <div class="head">
            <div class="brand">
                <img src="{{ asset('images/logo.webp') }}" alt="Rumitech">
                <div>
                    <p class="name">Rumitech</p>
                    <p class="tag">Warehouse Management System</p>
                </div>
            </div>

            <div class="doc">
                <h1>Purchase Order</h1>
                <p>{{ $order->reference_no }}</p>
                <p>{{ $order->created_at?->format('d/m/Y') }}</p>
            </div>
        </div>

        <dl class="info">
            <div>
                <dt>Pemasok</dt>
                <dd>{{ $order->supplier?->name }}{{ $order->supplier?->code ? ' ('.$order->supplier->code.')' : '' }}</dd>
            </div>
            <div>
                <dt>Narahubung</dt>
                <dd>{{ $order->supplier?->contact_person ?: '-' }}{{ $order->supplier?->phone ? ' | '.$order->supplier->phone : '' }}</dd>
            </div>
            <div>
                <dt>Gudang tujuan</dt>
                <dd>{{ $order->warehouse?->name }}</dd>
            </div>
            <div>
                <dt>Perkiraan kedatangan</dt>
                <dd>{{ $order->expected_date?->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt>Dibuat oleh</dt>
                <dd>{{ $order->creator?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt>Status</dt>
                <dd>{{ $order->statusLabel() }}</dd>
            </div>
        </dl>

        <table>
            <thead>
                <tr>
                    <th style="width: 8mm;">No</th>
                    <th>Produk</th>
                    <th class="num">Diharapkan</th>
                    <th class="num">Diterima</th>
                    <th class="num">Ditolak</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <p class="product">{{ $item->product?->name }}</p>
                            <p class="sku">{{ $item->product?->sku }}{{ $item->product?->uom ? ' | '.$item->product->uom : '' }}</p>
                        </td>
                        <td class="num">{{ number_format($item->qty_expected, 2, ',', '.') }}</td>
                        <td class="num">{{ number_format($item->qty_received, 2, ',', '.') }}</td>
                        <td class="num">{{ number_format($item->qty_rejected, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($order->notes)
            <div class="note">
                <strong>Catatan:</strong> {{ $order->notes }}
            </div>
        @endif

        <div class="footer">
            <div class="barcode">{!! $barcode !!}</div>

            <div class="sign">
                <div>
                    <p>Disiapkan oleh</p>
                    <p class="line">{{ $order->creator?->name ?? '' }}</p>
                </div>
                <div>
                    <p>Pemasok</p>
                    <p class="line"></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
