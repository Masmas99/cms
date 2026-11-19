<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    /**
     * Display every stock lot with its reservation state.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $warehouseId = $request->integer('warehouse');
        $filter = $request->string('filter')->trim()->toString();

        $stocks = InventoryStock::query()
            ->with(['product', 'location.warehouse'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->whereHas('product', fn (Builder $product) => $product
                    ->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%"));
            })
            ->when($warehouseId > 0, fn (Builder $query) => $query->whereHas('location', fn (Builder $location) => $location->where('warehouse_id', $warehouseId)))
            ->when($filter === 'available', fn (Builder $query) => $query->whereRaw('qty_on_hand - qty_reserved > 0'))
            ->when($filter === 'empty', fn (Builder $query) => $query->where('qty_on_hand', '<=', 0))
            ->when($filter === 'expiring', fn (Builder $query) => $query
                ->whereNotNull('expired_date')
                ->whereDate('expired_date', '>=', now()->toDateString())
                ->whereDate('expired_date', '<=', now()->addDays(30)->toDateString()))
            ->when($filter === 'expired', fn (Builder $query) => $query
                ->whereNotNull('expired_date')
                ->whereDate('expired_date', '<', now()->toDateString()))
            ->orderBy('expired_date')
            ->orderBy('product_id')
            ->paginate(20)
            ->withQueryString();

        return view(wms_view('stocks.index'), [
            'stocks' => $stocks,
            'warehouses' => Warehouse::query()->orderBy('name')->get(),
            'search' => $search,
            'warehouseId' => $warehouseId,
            'filter' => $filter,
            'totalOnHand' => (float) InventoryStock::query()->sum('qty_on_hand'),
            'totalReserved' => (float) InventoryStock::query()->sum('qty_reserved'),
        ]);
    }
}
