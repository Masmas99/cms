<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Executive and operational dashboard, scoped by role.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $days = in_array($request->integer('days'), [7, 30, 90], true) ? $request->integer('days') : 7;

        $stockProducts = Product::query()
            ->where('is_active', true)
            ->withSum('stocks as on_hand', 'qty_on_hand')
            ->withSum('stocks as reserved_qty', 'qty_reserved')
            ->get();

        $belowMinimum = $stockProducts
            ->filter(fn (Product $product): bool => $product->min_stock > 0 && ((float) $product->on_hand - (float) $product->reserved_qty) <= $product->min_stock)
            ->sortBy(fn (Product $product): float => (float) $product->on_hand)
            ->take(8)
            ->values();

        $outOfStock = $stockProducts
            ->filter(fn (Product $product): bool => (float) $product->on_hand <= 0)
            ->take(8)
            ->values();

        $expiring = InventoryStock::query()
            ->with(['product', 'location.warehouse'])
            ->where('qty_on_hand', '>', 0)
            ->whereNotNull('expired_date')
            ->whereDate('expired_date', '>=', now()->toDateString())
            ->whereDate('expired_date', '<=', now()->addDays(30)->toDateString())
            ->orderBy('expired_date')
            ->limit(8)
            ->get();

        $warehouses = Warehouse::query()
            ->withCount(['locations as total_bins' => fn ($query) => $query->where('is_staging', false)])
            ->withCount(['locations as occupied_bins' => fn ($query) => $query
                ->where('is_staging', false)
                ->whereHas('stocks', fn ($stock) => $stock->where('qty_on_hand', '>', 0))])
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view(wms_view('dashboard'), [
            'isAdmin' => $user->isAdmin(),
            'days' => $days,
            'activeSkus' => $stockProducts->count(),
            'totalOnHand' => (float) InventoryStock::query()->sum('qty_on_hand'),
            'totalReserved' => (float) InventoryStock::query()->sum('qty_reserved'),
            'inboundToday' => (float) StockMovement::query()
                ->where('type', StockMovement::TYPE_INBOUND)
                ->whereDate('created_at', now()->toDateString())
                ->sum('quantity'),
            'outboundToday' => abs((float) StockMovement::query()
                ->where('type', StockMovement::TYPE_OUTBOUND)
                ->whereDate('created_at', now()->toDateString())
                ->sum('quantity')),
            'assetValue' => $user->isAdmin()
                ? (float) InventoryStock::query()
                    ->join('products', 'products.id', '=', 'inventory_stocks.product_id')
                    ->selectRaw('COALESCE(SUM(inventory_stocks.qty_on_hand * products.unit_cost), 0) as total')
                    ->value('total')
                : null,
            'belowMinimum' => $belowMinimum,
            'outOfStock' => $outOfStock,
            'expiring' => $expiring,
            'warehouses' => $warehouses,
            'trend' => $this->movementTrend($days),
            'openInbound' => PurchaseOrder::query()->open()->count(),
            'openOutbound' => SalesOrder::query()
                ->whereIn('status', [SalesOrder::STATUS_RESERVED, SalesOrder::STATUS_PICKING, SalesOrder::STATUS_PACKED])
                ->count(),
            'pendingAdjustments' => StockAdjustment::query()->where('status', StockAdjustment::STATUS_PENDING)->count(),
            'recentMovements' => StockMovement::query()
                ->with(['product', 'location.warehouse', 'user'])
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    /**
     * Daily inbound vs outbound volume for the requested window.
     *
     * @return array{labels: array<int, string>, inbound: array<int, float>, outbound: array<int, float>, max: float}
     */
    private function movementTrend(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = StockMovement::query()
            ->where('created_at', '>=', $start)
            ->whereIn('type', [StockMovement::TYPE_INBOUND, StockMovement::TYPE_OUTBOUND])
            ->selectRaw('DATE(created_at) as day, type, SUM(quantity) as total')
            ->groupBy('day', 'type')
            ->get();

        $keyed = [];

        foreach ($rows as $row) {
            $keyed[(string) $row->day][$row->type] = abs((float) $row->total);
        }

        $labels = [];
        $inbound = [];
        $outbound = [];

        for ($index = 0; $index < $days; $index++) {
            $date = $start->copy()->addDays($index)->toDateString();
            $labels[] = Carbon::parse($date)->format('d/m');
            $inbound[] = $keyed[$date][StockMovement::TYPE_INBOUND] ?? 0.0;
            $outbound[] = $keyed[$date][StockMovement::TYPE_OUTBOUND] ?? 0.0;
        }

        $max = max(1.0, max($inbound), max($outbound));

        return [
            'labels' => $labels,
            'inbound' => $inbound,
            'outbound' => $outbound,
            'max' => $max,
        ];
    }
}
