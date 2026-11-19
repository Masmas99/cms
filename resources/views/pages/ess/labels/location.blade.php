<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label bin {{ $location->full_code }}</title>
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
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            max-width: 760px;
            margin: 0 auto 20px;
            padding: 16px;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            background: #ffffff;
        }
        .toolbar h1 { margin: 0; font-size: 16px; }
        .toolbar p { margin: 4px 0 0; font-size: 12px; color: #64748b; }
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
        .sheet { max-width: 760px; margin: 0 auto; display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
        .label {
            width: 100mm;
            min-height: 60mm;
            padding: 7mm;
            border: 1px dashed #94a3b8;
            border-radius: 8px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 4mm;
        }
        .label .eyebrow { font-size: 9px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: #64748b; }
        .label .code { font-size: 26px; font-weight: 800; letter-spacing: 0.04em; }
        .label .warehouse { font-size: 11px; color: #475569; }
        .label .zone { font-size: 10px; color: #64748b; }
        .label .barcode { text-align: center; }
        .label .barcode svg { width: 100%; height: auto; max-height: 22mm; }
        @media print {
            body { padding: 0; background: #ffffff; }
            .no-print { display: none !important; }
            .sheet { display: block; }
            .label { border: 1px solid #e2e8f0; }
            @page { size: A4; margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <div>
            <h1>Label bin</h1>
            <p>{{ $location->full_code }} | {{ $location->warehouse?->name }}</p>
        </div>
        <button type="button" class="button" onclick="window.print()">Cetak</button>
    </div>

    <div class="sheet">
        <div class="label">
            <div>
                <p class="eyebrow">Lokasi penyimpanan</p>
                <p class="code">{{ $location->full_code }}</p>
                <p class="warehouse">{{ $location->warehouse?->name }} ({{ $location->warehouse?->code }})</p>
                <p class="zone">Zona {{ $location->zone ?: '-' }}{{ $location->is_staging ? ' | Area staging' : '' }}</p>
            </div>

            <div class="barcode">{!! $barcode !!}</div>
        </div>
    </div>
</body>
</html>
