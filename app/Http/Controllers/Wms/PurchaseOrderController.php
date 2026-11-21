<?php

namespace App\Http\Controllers\Wms;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\ReferenceGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    /**
     * Display scheduled and completed purchase orders.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $orders = PurchaseOrder::query()
            ->with(['supplier', 'warehouse'])
            ->withCount('items')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn (Builder $supplier) => $supplier->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(wms_view('purchase-orders.index'), [
            'orders' => $orders,
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Show the PO creation form.
     */
    public function create(): View
    {
        return view(wms_view('purchase-orders.create'), [
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a purchase order with its lines.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'expected_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', 'exists:products,id'],
            'items.*.qty_expected' => ['required', 'numeric', 'min:0.01'],
        ]);

        $order = DB::transaction(function () use ($data, $request): PurchaseOrder {
            $order = PurchaseOrder::create([
                'reference_no' => ReferenceGenerator::make('PO', 'purchase_orders'),
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'created_by' => $request->user()->id,
                'expected_date' => $data['expected_date'],
                'status' => PurchaseOrder::STATUS_SCHEDULED,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'qty_expected' => $item['qty_expected'],
                ]);
            }

            return $order;
        });

        return redirect()
            ->route(wms_route_name('purchase-orders.show'), $order)
            ->with('success', 'Purchase order berhasil dibuat.');
    }

    /**
     * Show a purchase order and its receiving progress.
     */
    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load([
            'supplier',
            'warehouse',
            'creator',
            'items.product',
            'receipt.items.product',
            'receipt.items.location',
            'receipt.items.putAwayLocation',
            'receipt.receiver',
        ]);

        return view(wms_view('purchase-orders.show'), [
            'order' => $purchaseOrder,
            'stagedItems' => $purchaseOrder->receipt?->items->where('status', ReceiptItem::STATUS_STAGED)->where('qty_good', '>', 0) ?? collect(),
        ]);
    }

    /**
     * Show the receiving & QC form.
     */
    public function receive(PurchaseOrder $purchaseOrder): View|RedirectResponse
    {
        if (in_array($purchaseOrder->status, [PurchaseOrder::STATUS_COMPLETED, PurchaseOrder::STATUS_CANCELLED], true)) {
            return redirect()->route(wms_route_name('purchase-orders.show'), $purchaseOrder)->with('error', 'Purchase order ini sudah selesai.');
        }

        $purchaseOrder->load(['items.product', 'supplier', 'warehouse']);

        return view(wms_view('purchase-orders.receive'), [
            'order' => $purchaseOrder,
        ]);
    }

    /**
     * Store the physical receiving counts into the staging area.
     */
    public function receiveStore(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->load(['items.product', 'warehouse']);

        if (in_array($purchaseOrder->status, [PurchaseOrder::STATUS_COMPLETED, PurchaseOrder::STATUS_CANCELLED], true)) {
            return redirect()->route(wms_route_name('purchase-orders.show'), $purchaseOrder)->with('error', 'Purchase order ini sudah selesai.');
        }

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array'],
            'items.*.qty_good' => ['nullable', 'numeric', 'min:0'],
            'items.*.qty_rejected' => ['nullable', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:80'],
            'items.*.expired_date' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($data, $purchaseOrder, $request): void {
            $staging = $purchaseOrder->warehouse->stagingLocation()
                ?? $purchaseOrder->warehouse->locations()->create([
                    'zone' => 'STG',
                    'aisle' => 'STG',
                    'rack' => 'STG',
                    'bin_code' => 'IN',
                    'full_code' => Location::buildFullCode($purchaseOrder->warehouse->code, 'STG', 'STG', 'STG', 'IN'),
                    'capacity_m3' => 0,
                    'capacity_kg' => 0,
                    'is_staging' => true,
                    'is_active' => true,
                ]);

            $receipt = $purchaseOrder->receipt ?? Receipt::create([
                'reference_no' => ReferenceGenerator::make('RCV', 'receipts'),
                'purchase_order_id' => $purchaseOrder->id,
                'received_by' => $request->user()->id,
                'received_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $receivedAny = false;

            /** @var PurchaseOrderItem $item */
            foreach ($purchaseOrder->items as $item) {
                $row = $data['items'][$item->id] ?? null;

                if ($row === null) {
                    continue;
                }

                $good = (float) ($row['qty_good'] ?? 0);
                $rejected = (float) ($row['qty_rejected'] ?? 0);

                if ($good <= 0 && $rejected <= 0) {
                    continue;
                }

                if ($good + $rejected > $item->outstandingQuantity() + 0.0001) {
                    throw ValidationException::withMessages([
                        'items' => "Kuantitas {$item->product->sku} melebihi sisa yang dijadwalkan.",
                    ]);
                }

                $batch = trim((string) ($row['batch_number'] ?? '')) ?: 'NO-BATCH';
                $expiredDate = filled($row['expired_date'] ?? null) ? Carbon::parse($row['expired_date'])->startOfDay() : null;

                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'location_id' => $staging->id,
                    'batch_number' => $batch,
                    'expired_date' => $expiredDate,
                    'qty_good' => $good,
                    'qty_rejected' => $rejected,
                    'status' => ReceiptItem::STATUS_STAGED,
                    'notes' => null,
                ]);

                if ($good > 0) {
                    $this->inventory->addStock(
                        $item->product,
                        $staging,
                        $good,
                        $batch,
                        $expiredDate,
                        $request->user(),
                        StockMovement::TYPE_INBOUND,
                        $receipt->reference_no,
                        'Receiving '.$purchaseOrder->reference_no,
                    );
                }

                $item->increment('qty_received', $good);
                $item->increment('qty_rejected', $rejected);
                $receivedAny = true;
            }

            if (! $receivedAny) {
                throw ValidationException::withMessages([
                    'items' => 'Masukkan minimal satu kuantitas barang yang diterima.',
                ]);
            }

            $purchaseOrder->update(['status' => PurchaseOrder::STATUS_RECEIVING]);
        });

        return redirect()
            ->route(wms_route_name('purchase-orders.put-away'), $purchaseOrder)
            ->with('success', 'Barang diterima di staging area. Lanjutkan put-away ke bin.');
    }

    /**
     * Show staged goods with recommended bins.
     */
    public function putAway(PurchaseOrder $purchaseOrder): View|RedirectResponse
    {
        $purchaseOrder->load(['receipt.items.product', 'receipt.items.location', 'warehouse']);

        $stagedItems = $purchaseOrder->receipt?->items
            ->where('status', ReceiptItem::STATUS_STAGED)
            ->where('qty_good', '>', 0) ?? collect();

        if ($stagedItems->isEmpty()) {
            return redirect()->route(wms_route_name('purchase-orders.show'), $purchaseOrder)
                ->with('error', 'Tidak ada barang yang menunggu put-away.');
        }

        $locations = $purchaseOrder->warehouse->locations()
            ->pickable()
            ->withSum('stocks as qty_on_hand', 'qty_on_hand')
            ->orderBy('qty_on_hand')
            ->orderBy('zone')
            ->orderBy('aisle')
            ->orderBy('rack')
            ->orderBy('bin_code')
            ->get();

        $suggestions = $stagedItems
            ->mapWithKeys(fn (ReceiptItem $item): array => [$item->id => $locations->first()?->id])
            ->all();

        return view(wms_view('purchase-orders.put-away'), [
            'order' => $purchaseOrder,
            'items' => $stagedItems,
            'locations' => $locations,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Move staged goods into their final bins.
     */
    public function putAwayStore(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->load(['receipt.items.product', 'warehouse']);

        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.location_id' => ['nullable', 'exists:locations,id'],
        ]);

        DB::transaction(function () use ($data, $purchaseOrder, $request): void {
            $stagedItems = $purchaseOrder->receipt?->items
                ->where('status', ReceiptItem::STATUS_STAGED)
                ->where('qty_good', '>', 0) ?? collect();

            $movedAny = false;

            /** @var ReceiptItem $item */
            foreach ($stagedItems as $item) {
                $locationId = $data['items'][$item->id]['location_id'] ?? null;

                if ($locationId === null) {
                    continue;
                }

                $destination = Location::query()
                    ->whereKey($locationId)
                    ->where('warehouse_id', $purchaseOrder->warehouse_id)
                    ->where('is_staging', false)
                    ->where('is_active', true)
                    ->firstOrFail();

                $source = InventoryStock::query()
                    ->where('location_id', $item->location_id)
                    ->where('product_id', $item->product_id)
                    ->where('batch_number', $item->batch_number)
                    ->lockForUpdate()
                    ->first();

                if ($source === null || $source->availableQuantity() < $item->qty_good) {
                    throw ValidationException::withMessages([
                        'items' => "Stok staging untuk {$item->product->sku} tidak ditemukan atau sudah dipindahkan.",
                    ]);
                }

                $this->inventory->transfer(
                    $source,
                    $destination,
                    $item->qty_good,
                    $request->user(),
                    $purchaseOrder->receipt->reference_no.'-PUT',
                    'Put-away '.$purchaseOrder->reference_no,
                );

                $item->update([
                    'put_away_location_id' => $destination->id,
                    'status' => ReceiptItem::STATUS_PUT_AWAY,
                ]);

                $movedAny = true;
            }

            if (! $movedAny) {
                throw ValidationException::withMessages([
                    'items' => 'Pilih minimal satu bin tujuan.',
                ]);
            }

            $purchaseOrder->refresh()->load('items');

            $stillStaged = $purchaseOrder->receipt?->items()->where('status', ReceiptItem::STATUS_STAGED)->exists();

            if (! $stillStaged && $purchaseOrder->isFullyReceived()) {
                $purchaseOrder->update(['status' => PurchaseOrder::STATUS_COMPLETED]);
            }
        });

        return redirect()
            ->route(wms_route_name('purchase-orders.show'), $purchaseOrder)
            ->with('success', 'Put-away selesai dan stok bin telah diperbarui.');
    }

    /**
     * Cancel an open purchase order.
     */
    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status === PurchaseOrder::STATUS_COMPLETED) {
            return back()->with('error', 'Purchase order yang sudah selesai tidak dapat dibatalkan.');
        }

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_CANCELLED]);

        return redirect()->route(wms_route_name('purchase-orders.show'), $purchaseOrder)->with('success', 'Purchase order dibatalkan.');
    }
}
