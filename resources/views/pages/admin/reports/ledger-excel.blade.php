<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <meta name="ProgId" content="Excel.Sheet">
    <meta name="Generator" content="Rumitech">
    <style>
        table {
            border-collapse: collapse;
            font-family: Calibri, Arial, sans-serif;
            font-size: 11pt;
        }

        .title {
            color: #12213F;
            font-size: 18pt;
            font-weight: bold;
            padding: 0 0 4px;
        }

        .subtitle {
            color: #475569;
            font-size: 10.5pt;
            padding: 0 0 2px;
        }

        .header {
            background-color: #183E8C;
            border: 1px solid #183E8C;
            color: #FFFFFF;
            font-weight: bold;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }

        .cell {
            border: 1px solid #DBEAFE;
            padding: 6px 8px;
            vertical-align: top;
            mso-number-format: "\@";
        }

        .alt {
            background-color: #F8FAFF;
        }

        .num {
            border: 1px solid #DBEAFE;
            padding: 6px 8px;
            text-align: right;
            mso-number-format: "\#\,\#\#0\.00";
        }

        .total {
            background-color: #E4ECFB;
            border: 1px solid #C6D6F6;
            font-weight: bold;
            padding: 6px 8px;
        }

        .total-num {
            background-color: #E4ECFB;
            border: 1px solid #C6D6F6;
            font-weight: bold;
            padding: 6px 8px;
            text-align: right;
            mso-number-format: "\#\,\#\#0\.00";
        }
    </style>
</head>
<body>
    <table>
        <colgroup>
            <col style="width: 130px">
            <col style="width: 120px">
            <col style="width: 150px">
            <col style="width: 150px">
            <col style="width: 170px">
            <col style="width: 100px">
            <col style="width: 100px">
            <col style="width: 110px">
        </colgroup>

        <tr>
            <td colspan="8" class="title">Kartu Stok</td>
        </tr>
        <tr>
            <td colspan="8" class="subtitle">Produk: {{ $product->sku }} - {{ $product->name }} ({{ $product->uom }})</td>
        </tr>
        <tr>
            <td colspan="8" class="subtitle">Periode: {{ date('d/m/Y', strtotime($from)) }} - {{ date('d/m/Y', strtotime($to)) }}</td>
        </tr>
        <tr>
            <td colspan="8" class="subtitle">Dicetak: {{ now()->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td colspan="8">&nbsp;</td>
        </tr>

        <tr style="height: 24px">
            <td class="header">Tanggal</td>
            <td class="header">Tipe</td>
            <td class="header">Referensi</td>
            <td class="header">Lokasi</td>
            <td class="header">User</td>
            <td class="header" style="text-align: right">Masuk</td>
            <td class="header" style="text-align: right">Keluar</td>
            <td class="header" style="text-align: right">Saldo</td>
        </tr>

        <tr>
            <td colspan="7" class="total">Saldo awal</td>
            <td class="total-num">{{ $initial }}</td>
        </tr>

        @foreach ($rows as $index => $row)
            @php
                $movement = $row['movement'];
                $cellClass = $index % 2 === 1 ? 'cell alt' : 'cell';
                $numClass = $index % 2 === 1 ? 'num alt' : 'num';
            @endphp

            <tr>
                <td class="{{ $cellClass }}">{{ $movement->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                <td class="{{ $cellClass }}">{{ $movement->typeLabel() }}</td>
                <td class="{{ $cellClass }}">{{ $movement->reference_no ?: '-' }}</td>
                <td class="{{ $cellClass }}">{{ $movement->location?->full_code ?: '-' }}</td>
                <td class="{{ $cellClass }}">{{ $movement->user?->name ?: '-' }}</td>
                <td class="{{ $numClass }}">{{ $row['in'] > 0 ? $row['in'] : '' }}</td>
                <td class="{{ $numClass }}">{{ $row['out'] > 0 ? $row['out'] : '' }}</td>
                <td class="{{ $numClass }}">{{ $row['balance'] }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="5" class="total">Total</td>
            <td class="total-num">{{ collect($rows)->sum('in') }}</td>
            <td class="total-num">{{ collect($rows)->sum('out') }}</td>
            <td class="total-num">{{ $final }}</td>
        </tr>
    </table>
</body>
</html>
