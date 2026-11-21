<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Chronological stock card for one SKU.
     */
    public function ledger(Request $request): View
    {
        $products = Product::query()->orderBy('name')->get();
        $productId = $request->string('product')->trim()->toString();
        $product = $products->firstWhere('id', $productId) ?? $products->first();

        $from = $this->date($request->input('from'), now()->startOfMonth());
        $to = $this->date($request->input('to'), now());

        $rows = [];
        $initial = 0.0;

        if ($product !== null) {
            $initial = (float) StockMovement::query()
                ->where('product_id', $product->id)
                ->where('created_at', '<', $from)
                ->sum('quantity');

            $running = $initial;

            $movements = StockMovement::query()
                ->with(['location.warehouse', 'user'])
                ->where('product_id', $product->id)
                ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            foreach ($movements as $movement) {
                $running += $movement->quantity;

                $rows[] = [
                    'movement' => $movement,
                    'in' => $movement->quantity > 0 ? $movement->quantity : 0,
                    'out' => $movement->quantity < 0 ? abs($movement->quantity) : 0,
                    'balance' => $running,
                ];
            }
        }

        return view(wms_view('reports.ledger'), [
            'products' => $products,
            'product' => $product,
            'rows' => $rows,
            'initial' => $initial,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'final' => $initial + collect($rows)->sum(fn (array $row): float => $row['movement']->quantity),
        ]);
    }

    /**
     * Export the stock card as a formatted Excel spreadsheet.
     */
    public function ledgerExport(Request $request): StreamedResponse
    {
        $product = Product::query()->findOrFail($request->string('product')->trim()->toString());
        $from = $this->date($request->input('from'), now()->startOfMonth());
        $to = $this->date($request->input('to'), now());

        $initial = (float) StockMovement::query()
            ->where('product_id', $product->id)
            ->where('created_at', '<', $from)
            ->sum('quantity');

        $movements = StockMovement::query()
            ->with(['location.warehouse', 'user'])
            ->where('product_id', $product->id)
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $running = $initial;
        $rows = $movements->map(function (StockMovement $movement) use (&$running): array {
            $running += (float) $movement->quantity;

            return [
                'movement' => $movement,
                'in' => $movement->quantity > 0 ? (float) $movement->quantity : 0.0,
                'out' => $movement->quantity < 0 ? abs((float) $movement->quantity) : 0.0,
                'balance' => $running,
            ];
        });

        $html = view('pages.admin.reports.ledger-excel', [
            'product' => $product,
            'rows' => $rows,
            'initial' => $initial,
            'final' => $running,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ])->render();

        $filename = 'kartu-stok-'.str_replace(['/', '\\'], '-', $product->sku).'-'.$from->format('Ymd').'-'.$to->format('Ymd').'.xls';

        return response()->streamDownload(function () use ($html): void {
            echo "\xEF\xBB\xBF".$html;
        }, $filename, ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    /**
     * Aging buckets for stored lots.
     */
    public function aging(): View
    {
        $stocks = InventoryStock::query()
            ->with(['product', 'location.warehouse'])
            ->where('qty_on_hand', '>', 0)
            ->orderBy('received_at')
            ->get();

        $rows = $stocks->map(fn (InventoryStock $stock): array => [
            'stock' => $stock,
            'age' => (int) ($stock->received_at?->diffInDays(now()) ?? 0),
            'bucket' => $this->ageBucket((int) ($stock->received_at?->diffInDays(now()) ?? 0)),
            'value' => (float) $stock->qty_on_hand * (float) $stock->product->unit_cost,
        ]);

        $summary = ['0-30' => 0.0, '31-60' => 0.0, '61-90' => 0.0, '>90' => 0.0];

        foreach ($rows as $row) {
            $summary[$row['bucket']] += (float) $row['stock']->qty_on_hand;
        }

        return view(wms_view('reports.aging'), [
            'rows' => $rows,
            'summary' => $summary,
            'totalValue' => $rows->sum('value'),
        ]);
    }

    public function agingExport(): StreamedResponse
    {
        $stocks = InventoryStock::query()
            ->with(['product', 'location.warehouse'])
            ->where('qty_on_hand', '>', 0)
            ->orderBy('received_at')
            ->get();

        return response()->streamDownload(function () use ($stocks): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['SKU', 'Produk', 'Gudang', 'Bin', 'Batch', 'Kedaluwarsa', 'Tanggal Masuk', 'Umur (hari)', 'Bucket', 'Qty', 'Nilai']);

            foreach ($stocks as $stock) {
                $age = (int) ($stock->received_at?->diffInDays(now()) ?? 0);

                fputcsv($handle, [
                    $stock->product->sku,
                    $stock->product->name,
                    $stock->location->warehouse->name,
                    $stock->location->full_code,
                    $stock->batch_number,
                    $stock->expired_date?->format('Y-m-d'),
                    $stock->received_at?->format('Y-m-d'),
                    $age,
                    $this->ageBucket($age),
                    $stock->qty_on_hand,
                    (float) $stock->qty_on_hand * (float) $stock->product->unit_cost,
                ]);
            }

            fclose($handle);
        }, 'aging-stok.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Bin occupancy per warehouse and zone.
     */
    public function capacity(): View
    {
        return view(wms_view('reports.capacity'), $this->capacityData());
    }

    public function capacityExport(): StreamedResponse
    {
        $data = $this->capacityData();

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Gudang', 'Zona', 'Total Bin', 'Bin Terisi', 'Utilisasi (%)']);

            foreach ($data['zones'] as $zone) {
                fputcsv($handle, [
                    $zone->warehouse_name,
                    $zone->zone,
                    $zone->total_bins,
                    $zone->occupied_bins,
                    $zone->total_bins > 0 ? round($zone->occupied_bins / $zone->total_bins * 100, 1) : 0,
                ]);
            }

            fclose($handle);
        }, 'utilisasi-kapasitas.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Shared capacity statistics.
     *
     * @return array<string, mixed>
     */
    private function capacityData(): array
    {
        $warehouses = Warehouse::query()
            ->withCount(['locations as total_bins' => fn ($query) => $query->where('is_staging', false)])
            ->withCount(['locations as occupied_bins' => fn ($query) => $query
                ->where('is_staging', false)
                ->whereHas('stocks', fn ($stock) => $stock->where('qty_on_hand', '>', 0))])
            ->orderBy('name')
            ->get();

        $zones = Location::query()
            ->join('warehouses', 'warehouses.id', '=', 'locations.warehouse_id')
            ->where('locations.is_staging', false)
            ->select('warehouses.name as warehouse_name', 'locations.zone')
            ->selectRaw('COUNT(*) as total_bins')
            ->selectRaw('SUM(CASE WHEN EXISTS (SELECT 1 FROM inventory_stocks WHERE inventory_stocks.location_id = locations.id AND inventory_stocks.qty_on_hand > 0) THEN 1 ELSE 0 END) as occupied_bins')
            ->groupBy('warehouses.name', 'locations.zone')
            ->orderBy('warehouses.name')
            ->orderBy('locations.zone')
            ->get();

        $zones = $zones->map(function ($zone) {
            $zone->total_bins = (int) $zone->total_bins;
            $zone->occupied_bins = (int) $zone->occupied_bins;

            return $zone;
        });

        return [
            'warehouses' => $warehouses,
            'zones' => $zones,
        ];
    }

    /**
     * Parse a date input with a fallback.
     */
    private function date(mixed $value, Carbon $fallback): Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return $fallback;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function ageBucket(int $days): string
    {
        return match (true) {
            $days <= 30 => '0-30',
            $days <= 60 => '31-60',
            $days <= 90 => '61-90',
            default => '>90',
        };
    }
}
