<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Add stock to a bin lot and record the movement.
     */
    public function addStock(
        Product $product,
        Location $location,
        float $quantity,
        string $batchNumber,
        ?Carbon $expiredDate,
        ?User $user,
        string $type = StockMovement::TYPE_INBOUND,
        ?string $referenceNo = null,
        ?string $notes = null,
    ): InventoryStock {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Kuantitas harus lebih besar dari nol.');
        }

        return DB::transaction(function () use ($product, $location, $quantity, $batchNumber, $expiredDate, $user, $type, $referenceNo, $notes): InventoryStock {
            $stock = InventoryStock::query()
                ->lockForUpdate()
                ->firstOrCreate(
                    [
                        'location_id' => $location->id,
                        'product_id' => $product->id,
                        'batch_number' => $batchNumber,
                    ],
                    [
                        'expired_date' => $expiredDate,
                        'received_at' => now(),
                        'qty_on_hand' => 0,
                        'qty_reserved' => 0,
                    ],
                );

            $stock->increment('qty_on_hand', $quantity);

            $this->recordMovement($product, $location, $user, $type, $quantity, $batchNumber, $expiredDate, $referenceNo, $notes);

            return $stock->refresh();
        });
    }

    /**
     * Remove stock from a bin lot. Reserved stock cannot be removed.
     */
    public function removeStock(
        InventoryStock $stock,
        float $quantity,
        ?User $user,
        string $type,
        ?string $referenceNo = null,
        ?string $notes = null,
    ): void {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Kuantitas harus lebih besar dari nol.');
        }

        DB::transaction(function () use ($stock, $quantity, $user, $type, $referenceNo, $notes): void {
            $locked = InventoryStock::query()->lockForUpdate()->findOrFail($stock->id);
            $available = $locked->qty_on_hand - $locked->qty_reserved;

            if ($available < $quantity) {
                throw new InvalidArgumentException('Stok tersedia tidak mencukupi untuk transaksi ini.');
            }

            $locked->decrement('qty_on_hand', $quantity);

            $this->recordMovement(
                $locked->product,
                $locked->location,
                $user,
                $type,
                -$quantity,
                $locked->batch_number,
                $locked->expired_date,
                $referenceNo,
                $notes,
            );
        });
    }

    /**
     * Move stock between two bins, preserving batch and expiry.
     */
    public function transfer(
        InventoryStock $source,
        Location $destination,
        float $quantity,
        User $user,
        string $referenceNo,
        ?string $notes = null,
    ): void {
        DB::transaction(function () use ($source, $destination, $quantity, $user, $referenceNo, $notes): void {
            $locked = InventoryStock::query()->lockForUpdate()->findOrFail($source->id);

            if ($locked->availableQuantity() < $quantity) {
                throw new InvalidArgumentException('Stok tersedia tidak mencukupi untuk transfer.');
            }

            $locked->decrement('qty_on_hand', $quantity);

            $this->recordMovement(
                $locked->product,
                $locked->location,
                $user,
                StockMovement::TYPE_TRANSFER_OUT,
                -$quantity,
                $locked->batch_number,
                $locked->expired_date,
                $referenceNo,
                $notes,
            );

            $this->addStock(
                $locked->product,
                $destination,
                $quantity,
                $locked->batch_number,
                $locked->expired_date,
                $user,
                StockMovement::TYPE_TRANSFER_IN,
                $referenceNo,
                $notes,
            );
        });
    }

    /**
     * Apply an approved stock adjustment to the ledger.
     */
    public function applyAdjustment(StockAdjustment $adjustment, User $user): void
    {
        DB::transaction(function () use ($adjustment, $user): void {
            $difference = (float) $adjustment->quantity_difference;
            $stock = InventoryStock::query()
                ->lockForUpdate()
                ->where('location_id', $adjustment->location_id)
                ->where('product_id', $adjustment->product_id)
                ->where('batch_number', $adjustment->batch_number)
                ->first();

            if ($stock === null && $difference < 0) {
                throw new InvalidArgumentException('Lot stok tidak ditemukan untuk penyesuaian negatif.');
            }

            if ($stock === null) {
                $location = Location::query()->findOrFail($adjustment->location_id);
                $product = Product::query()->findOrFail($adjustment->product_id);

                $this->addStock(
                    $product,
                    $location,
                    $difference,
                    $adjustment->batch_number,
                    null,
                    $user,
                    StockMovement::TYPE_ADJUSTMENT_IN,
                    $adjustment->reference_no,
                    $adjustment->reason,
                );

                return;
            }

            if ($difference < 0 && $stock->availableQuantity() < abs($difference)) {
                throw new InvalidArgumentException('Stok tersedia tidak mencukupi untuk penyesuaian negatif.');
            }

            if ($difference > 0) {
                $stock->increment('qty_on_hand', $difference);
                $type = StockMovement::TYPE_ADJUSTMENT_IN;
            } else {
                $stock->decrement('qty_on_hand', abs($difference));
                $type = StockMovement::TYPE_ADJUSTMENT_OUT;
            }

            $this->recordMovement(
                $stock->product,
                $stock->location,
                $user,
                $type,
                $difference,
                $stock->batch_number,
                $stock->expired_date,
                $adjustment->reference_no,
                $adjustment->reason,
            );
        });
    }

    /**
     * Write an immutable movement row.
     */
    public function recordMovement(
        Product $product,
        Location $location,
        ?User $user,
        string $type,
        float $quantity,
        string $batchNumber,
        ?Carbon $expiredDate,
        ?string $referenceNo,
        ?string $notes = null,
    ): StockMovement {
        return StockMovement::create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'user_id' => $user?->id,
            'type' => $type,
            'quantity' => $quantity,
            'batch_number' => $batchNumber,
            'expired_date' => $expiredDate,
            'reference_no' => $referenceNo,
            'notes' => $notes,
        ]);
    }
}
