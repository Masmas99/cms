<?php

namespace App\Models;

use Database\Factories\InventoryStockFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['location_id', 'product_id', 'batch_number', 'expired_date', 'received_at', 'qty_on_hand', 'qty_reserved'])]
class InventoryStock extends Model
{
    /** @use HasFactory<InventoryStockFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expired_date' => 'date',
            'received_at' => 'datetime',
            'qty_on_hand' => 'float',
            'qty_reserved' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<StockAllocation, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(StockAllocation::class);
    }

    /**
     * @param  Builder<InventoryStock>  $query
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->whereRaw('qty_on_hand - qty_reserved > 0');
    }

    /**
     * Units that can still be allocated to an order.
     */
    public function availableQuantity(): float
    {
        return max(0, $this->qty_on_hand - $this->qty_reserved);
    }

    /**
     * Whether this lot is within the expiry warning window.
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expired_date !== null
            && $this->expired_date->isFuture()
            && $this->expired_date->lte(now()->addDays($days));
    }

    /**
     * Whether this lot has already expired.
     */
    public function isExpired(): bool
    {
        return $this->expired_date !== null && $this->expired_date->isPast();
    }
}
