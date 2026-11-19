<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\StockAdjustment;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Warehouse;
use App\Services\ReferenceGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockOpnameController extends Controller
{
    public function index(): View
    {
        return view(wms_view('opnames.index'), [
            'opnames' => StockOpname::query()
                ->with('warehouse')
                ->withCount('items')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view(wms_view('opnames.create'), [
            'warehouses' => Warehouse::query()->orderBy('name')->get(),
            'zones' => Location::query()->where('is_staging', false)->distinct()->orderBy('zone')->pluck('zone'),
        ]);
    }

    /**
     * Snapshot the current bin contents into a blind count sheet.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'zone' => ['nullable', 'string', 'max:20'],
            'scheduled_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $stocks = InventoryStock::query()
            ->with('location')
            ->where('qty_on_hand', '>', 0)
            ->whereHas('location', function ($query) use ($data): void {
                $query->where('warehouse_id', $data['warehouse_id'])->where('is_staging', false);

                if (filled($data['zone'] ?? null)) {
                    $query->where('zone', $data['zone']);
                }
            })
            ->get();

        if ($stocks->isEmpty()) {
            throw ValidationException::withMessages([
                'warehouse_id' => 'Tidak ada stok aktif pada gudang/zona yang dipilih.',
            ]);
        }

        $opname = DB::transaction(function () use ($data, $stocks, $request): StockOpname {
            $opname = StockOpname::create([
                'reference_no' => ReferenceGenerator::make('OPN', 'stock_opnames'),
                'warehouse_id' => $data['warehouse_id'],
                'zone' => $data['zone'] ?? null,
                'scheduled_date' => $data['scheduled_date'],
                'status' => StockOpname::STATUS_COUNTING,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($stocks as $stock) {
                $opname->items()->create([
                    'product_id' => $stock->product_id,
                    'location_id' => $stock->location_id,
                    'batch_number' => $stock->batch_number,
                    'system_qty' => $stock->qty_on_hand,
                ]);
            }

            return $opname;
        });

        return redirect()
            ->route(wms_route_name('opnames.show'), $opname)
            ->with('success', 'Stock opname dibuat. User dapat mulai melakukan blind count.');
    }

    public function show(StockOpname $stockOpname): View
    {
        $stockOpname->load([
            'warehouse',
            'creator',
            'items.product',
            'items.location',
            'items.counter',
            'items.adjustment',
        ]);

        return view(wms_view('opnames.show'), [
            'opname' => $stockOpname,
            'counted' => $stockOpname->items->whereNotNull('counted_qty')->count(),
        ]);
    }

    /**
     * Save physical counts. The system quantity stays hidden from regular users.
     */
    public function count(Request $request, StockOpname $stockOpname): RedirectResponse
    {
        if ($stockOpname->status === StockOpname::STATUS_COMPLETED) {
            return back()->with('error', 'Opname ini sudah diselesaikan.');
        }

        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data, $stockOpname, $request): void {
            foreach ($data['items'] as $itemId => $countedQty) {
                if ($countedQty === null || $countedQty === '') {
                    continue;
                }

                /** @var StockOpnameItem|null $item */
                $item = StockOpnameItem::query()
                    ->where('stock_opname_id', $stockOpname->id)
                    ->whereKey($itemId)
                    ->first();

                if ($item === null) {
                    continue;
                }

                $difference = (float) $countedQty - $item->system_qty;

                $item->update([
                    'counted_qty' => (float) $countedQty,
                    'difference' => $difference,
                    'counted_by' => $request->user()->id,
                    'counted_at' => now(),
                ]);

                $this->syncAdjustment($item, $difference, $request);
            }
        });

        return redirect()
            ->route(wms_route_name('opnames.show'), $stockOpname)
            ->with('success', 'Hasil hitung fisik disimpan. Selisih diteruskan ke admin untuk approval.');
    }

    public function complete(StockOpname $stockOpname): RedirectResponse
    {
        if (! $stockOpname->isFullyCounted()) {
            return back()->with('error', 'Seluruh baris harus dihitung sebelum opname diselesaikan.');
        }

        $stockOpname->update([
            'status' => StockOpname::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return redirect()->route(wms_route_name('opnames.show'), $stockOpname)->with('success', 'Stock opname ditutup.');
    }

    /**
     * Create or update the pending adjustment linked to an opname line.
     */
    private function syncAdjustment(StockOpnameItem $item, float $difference, Request $request): void
    {
        $adjustment = StockAdjustment::query()->where('stock_opname_item_id', $item->id)->first();

        if (abs($difference) < 0.0001) {
            if ($adjustment !== null && $adjustment->status === StockAdjustment::STATUS_PENDING) {
                $adjustment->delete();
            }

            return;
        }

        if ($adjustment !== null && $adjustment->status !== StockAdjustment::STATUS_PENDING) {
            return;
        }

        if ($adjustment === null) {
            StockAdjustment::create([
                'reference_no' => ReferenceGenerator::make('ADJ', 'stock_adjustments'),
                'stock_opname_item_id' => $item->id,
                'product_id' => $item->product_id,
                'location_id' => $item->location_id,
                'batch_number' => $item->batch_number,
                'quantity_difference' => $difference,
                'reason' => 'Selisih hasil opname '.$item->stockOpname->reference_no,
                'status' => StockAdjustment::STATUS_PENDING,
                'requested_by' => $request->user()->id,
            ]);

            return;
        }

        $adjustment->update([
            'quantity_difference' => $difference,
            'requested_by' => $request->user()->id,
        ]);
    }
}
