<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\StockTransfer;
use App\Services\InventoryService;
use App\Services\ReferenceGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(): View
    {
        return view(wms_view('transfers.index'), [
            'transfers' => StockTransfer::query()
                ->with(['product', 'fromLocation.warehouse', 'toLocation.warehouse', 'creator'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(Request $request): View
    {
        $stocks = InventoryStock::query()
            ->with(['product', 'location.warehouse'])
            ->whereRaw('qty_on_hand - qty_reserved > 0')
            ->whereHas('location', fn ($query) => $query->where('is_staging', false))
            ->orderBy('product_id')
            ->limit(500)
            ->get();

        $selectedStock = $request->filled('stock')
            ? $stocks->firstWhere('id', $request->integer('stock'))
            : null;

        return view(wms_view('transfers.create'), [
            'stocks' => $stocks,
            'selectedStock' => $selectedStock,
            'locations' => Location::query()->pickable()->with('warehouse')->orderBy('warehouse_id')->orderBy('zone')->orderBy('aisle')->orderBy('rack')->orderBy('bin_code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'inventory_stock_id' => ['required', 'exists:inventory_stocks,id'],
            'to_location_id' => ['required', 'exists:locations,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $source = InventoryStock::query()->with(['product', 'location'])->findOrFail($data['inventory_stock_id']);
        $destination = Location::query()->findOrFail($data['to_location_id']);

        if ($destination->id === $source->location_id) {
            throw ValidationException::withMessages(['to_location_id' => 'Bin tujuan harus berbeda dari bin asal.']);
        }

        if (! $destination->is_active || $destination->is_staging) {
            throw ValidationException::withMessages(['to_location_id' => 'Pilih bin penyimpanan yang aktif.']);
        }

        if ($data['quantity'] > $source->availableQuantity() + 0.0001) {
            throw ValidationException::withMessages(['quantity' => 'Kuantitas melebihi stok tersedia ('.$source->availableQuantity().').']);
        }

        $transfer = DB::transaction(function () use ($data, $source, $destination, $request): StockTransfer {
            $transfer = StockTransfer::create([
                'reference_no' => ReferenceGenerator::make('TRF', 'stock_transfers'),
                'product_id' => $source->product_id,
                'from_location_id' => $source->location_id,
                'to_location_id' => $destination->id,
                'quantity' => $data['quantity'],
                'batch_number' => $source->batch_number,
                'expired_date' => $source->expired_date,
                'status' => StockTransfer::STATUS_COMPLETED,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $this->inventory->transfer(
                $source,
                $destination,
                $data['quantity'],
                $request->user(),
                $transfer->reference_no,
                $data['notes'] ?? null,
            );

            return $transfer;
        });

        return redirect()
            ->route(wms_route_name('transfers.index'))
            ->with('success', "Transfer {$transfer->reference_no} berhasil dipindahkan.");
    }
}
