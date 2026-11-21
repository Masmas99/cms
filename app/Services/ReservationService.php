<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockAllocation;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function __construct(private readonly InventoryService $inventory) {}

    /**
     * Reserve stock for every order line using the product's FIFO/FEFO rule.
     *
     * @throws ValidationException
     */
    public function reserve(SalesOrder $order): void
    {
        DB::transaction(function () use ($order): void {
            $order = SalesOrder::query()->lockForUpdate()->with('items.product')->findOrFail($order->id);

            if ($order->status !== SalesOrder::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'order' => 'Hanya sales order berstatus draft yang dapat dialokasikan.',
                ]);
            }

            foreach ($order->items as $item) {
                $this->reserveItem($order, $item);
            }

            $this->assignPickSequences($order);

            $order->update([
                'status' => SalesOrder::STATUS_RESERVED,
                'reserved_at' => now(),
            ]);
        });
    }

    /**
     * Release every unpicked reservation and return the order to draft.
     */
    public function release(SalesOrder $order): void
    {
        DB::transaction(function () use ($order): void {
            $order = SalesOrder::query()->lockForUpdate()->with('items')->findOrFail($order->id);

            if (in_array($order->status, [SalesOrder::STATUS_SHIPPED, SalesOrder::STATUS_CANCELLED], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Sales order ini tidak dapat dibatalkan alokasinya.',
                ]);
            }

            $allocations = StockAllocation::query()
                ->whereHas('salesOrderItem', fn ($query) => $query->where('sales_order_id', $order->id))
                ->whereNull('picked_at')
                ->with('inventoryStock')
                ->lockForUpdate()
                ->get();

            foreach ($allocations as $allocation) {
                InventoryStock::query()
                    ->whereKey($allocation->inventory_stock_id)
                    ->decrement('qty_reserved', $allocation->quantity);

                $allocation->delete();
            }

            foreach ($order->items as $item) {
                $item->update([
                    'qty_allocated' => $item->qty_picked,
                ]);
            }

            $order->update([
                'status' => SalesOrder::STATUS_DRAFT,
                'reserved_at' => null,
            ]);
        });
    }

    /**
     * Confirm a pick, deduct the physical stock and write the ledger entry.
     */
    public function pick(StockAllocation $allocation, User $user): void
    {
        DB::transaction(function () use ($allocation, $user): void {
            $allocation = StockAllocation::query()
                ->lockForUpdate()
                ->with(['salesOrderItem.salesOrder', 'inventoryStock.product', 'inventoryStock.location'])
                ->findOrFail($allocation->id);

            if ($allocation->picked_at !== null) {
                throw ValidationException::withMessages([
                    'allocation' => 'Item ini sudah dikonfirmasi sebelumnya.',
                ]);
            }

            $stock = InventoryStock::query()->lockForUpdate()->findOrFail($allocation->inventory_stock_id);

            if ($stock->qty_reserved < $allocation->quantity || $stock->qty_on_hand < $allocation->quantity) {
                throw ValidationException::withMessages([
                    'allocation' => 'Reservasi stok tidak valid. Silakan alokasikan ulang pesanan.',
                ]);
            }

            $stock->decrement('qty_on_hand', $allocation->quantity);
            $stock->decrement('qty_reserved', $allocation->quantity);

            $item = $allocation->salesOrderItem;
            $order = $item->salesOrder;

            $this->inventory->recordMovement(
                $stock->product,
                $stock->location,
                $user,
                StockMovement::TYPE_OUTBOUND,
                -$allocation->quantity,
                $stock->batch_number,
                $stock->expired_date,
                $order->reference_no,
                'Picking '.$order->reference_no,
            );

            $allocation->update(['picked_at' => now()]);
            $item->increment('qty_picked', $allocation->quantity);

            if ($order->status === SalesOrder::STATUS_RESERVED) {
                $order->update(['status' => SalesOrder::STATUS_PICKING]);
            }
        });
    }

    /**
     * Resolve the next unpicked allocation from a scanned bin or SKU code.
     */
    public function findPickableByCode(SalesOrder $order, string $code): ?StockAllocation
    {
        $code = trim($code);

        if ($code === '') {
            return null;
        }

        return StockAllocation::query()
            ->whereHas('salesOrderItem', fn ($query) => $query->where('sales_order_id', $order->id))
            ->whereNull('picked_at')
            ->where(function ($query) use ($code): void {
                $query->whereHas('inventoryStock.location', fn ($location) => $location->where('full_code', $code))
                    ->orWhereHas('inventoryStock.product', fn ($product) => $product
                        ->where('sku', $code)
                        ->orWhere('barcode', $code));
            })
            ->orderBy('pick_sequence')
            ->first();
    }

    /**
     * Verify a picked allocation at the packing table.
     */
    public function pack(StockAllocation $allocation, User $user): void
    {
        DB::transaction(function () use ($allocation, $user): void {
            $allocation = StockAllocation::query()->lockForUpdate()->findOrFail($allocation->id);

            if ($allocation->picked_at === null) {
                throw ValidationException::withMessages([
                    'allocation' => 'Item harus di-pick sebelum diverifikasi di packing.',
                ]);
            }

            $allocation->update(['packed_at' => now()]);

            $order = $allocation->salesOrderItem->salesOrder()->first();

            if ($order !== null && $order->status === SalesOrder::STATUS_PICKING && $order->isFullyPacked()) {
                $order->update([
                    'status' => SalesOrder::STATUS_PACKED,
                    'packed_at' => now(),
                ]);

                $order->shipment()->updateOrCreate(
                    ['sales_order_id' => $order->id],
                    ['packed_by' => $user->id, 'packed_at' => now()],
                );
            }
        });
    }

    /**
     * Hand the parcel to the courier and capture the tracking number.
     */
    public function ship(SalesOrder $order, ?string $courierId, ?string $trackingNumber, User $user): void
    {
        DB::transaction(function () use ($order, $courierId, $trackingNumber, $user): void {
            $order = SalesOrder::query()->lockForUpdate()->with('items.allocations')->findOrFail($order->id);

            if (! in_array($order->status, [SalesOrder::STATUS_PICKING, SalesOrder::STATUS_PACKED], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Pesanan belum siap untuk dikirim.',
                ]);
            }

            if (! $order->isFullyPicked()) {
                throw ValidationException::withMessages([
                    'order' => 'Seluruh item harus di-pick sebelum pengiriman.',
                ]);
            }

            if (! $order->isFullyPacked()) {
                throw ValidationException::withMessages([
                    'order' => 'Seluruh item harus diverifikasi packing sebelum pengiriman.',
                ]);
            }

            $order->shipment()->updateOrCreate(
                ['sales_order_id' => $order->id],
                [
                    'courier_id' => $courierId,
                    'tracking_number' => $trackingNumber,
                    'packed_by' => $user->id,
                    'packed_at' => $order->packed_at ?? now(),
                    'shipped_at' => now(),
                ],
            );

            $order->update([
                'status' => SalesOrder::STATUS_SHIPPED,
                'packed_at' => $order->packed_at ?? now(),
                'shipped_at' => now(),
            ]);
        });
    }

    /**
     * Allocate a single order line across the best available lots.
     */
    private function reserveItem(SalesOrder $order, SalesOrderItem $item): void
    {
        $product = $item->product;
        $remaining = (float) $item->qty_ordered;

        $query = InventoryStock::query()
            ->where('product_id', $item->product_id)
            ->whereRaw('qty_on_hand - qty_reserved > 0')
            ->where(function ($query): void {
                $query->whereNull('expired_date')->orWhereDate('expired_date', '>=', now()->toDateString());
            })
            ->lockForUpdate();

        if ($product->usesFefo()) {
            $query->orderByRaw('CASE WHEN expired_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('expired_date')
                ->orderBy('received_at');
        } else {
            $query->orderBy('received_at')->orderBy('id');
        }

        /** @var Collection<int, InventoryStock> $stocks */
        $stocks = $query->get();

        foreach ($stocks as $stock) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($remaining, $stock->availableQuantity());

            if ($take <= 0) {
                continue;
            }

            StockAllocation::create([
                'sales_order_item_id' => $item->id,
                'inventory_stock_id' => $stock->id,
                'quantity' => $take,
            ]);

            $stock->increment('qty_reserved', $take);
            $remaining -= $take;
        }

        if ($remaining > 0.0001) {
            throw ValidationException::withMessages([
                'stock' => "Stok untuk {$product->sku} tidak mencukupi. Kurang {$remaining} {$product->uom}.",
            ]);
        }

        $item->update(['qty_allocated' => $item->qty_ordered]);
    }

    /**
     * Order allocations by the physical walking route of the warehouse.
     */
    private function assignPickSequences(SalesOrder $order): void
    {
        $allocations = StockAllocation::query()
            ->whereHas('salesOrderItem', fn ($query) => $query->where('sales_order_id', $order->id))
            ->with('inventoryStock.location')
            ->get()
            ->sortBy(fn (StockAllocation $allocation): string => sprintf(
                '%05d-%s-%s-%s-%s',
                $allocation->inventoryStock->location->warehouse_id,
                $allocation->inventoryStock->location->zone,
                $allocation->inventoryStock->location->aisle,
                $allocation->inventoryStock->location->rack,
                $allocation->inventoryStock->location->bin_code,
            ));

        $sequence = 1;

        foreach ($allocations as $allocation) {
            $allocation->update(['pick_sequence' => $sequence++]);
        }
    }
}
