<?php

namespace App\Models;

use Database\Factories\SalesOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['reference_no', 'customer_name', 'customer_phone', 'status', 'notes', 'created_by', 'reserved_at', 'packed_at', 'shipped_at'])]
class SalesOrder extends Model
{
    /** @use HasFactory<SalesOrderFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_RESERVED = 'reserved';

    public const STATUS_PICKING = 'picking';

    public const STATUS_PACKED = 'packed';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reserved_at' => 'datetime',
            'packed_at' => 'datetime',
            'shipped_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<SalesOrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    /**
     * @return HasOne<Shipment, $this>
     */
    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    /**
     * @return HasMany<ReturnOrder, $this>
     */
    public function returns(): HasMany
    {
        return $this->hasMany(ReturnOrder::class);
    }

    /**
     * @param  Builder<SalesOrder>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_RESERVED, self::STATUS_PICKING, self::STATUS_PACKED]);
    }

    /**
     * Human readable status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_RESERVED => 'Stok dialokasikan',
            self::STATUS_PICKING => 'Picking',
            self::STATUS_PACKED => 'Siap kirim',
            self::STATUS_SHIPPED => 'Dikirim',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    /**
     * Whether every ordered unit has been picked.
     */
    public function isFullyPicked(): bool
    {
        return $this->items->every(
            fn (SalesOrderItem $item): bool => $item->qty_picked >= $item->qty_ordered
        );
    }

    /**
     * Whether every picked allocation has been verified at packing.
     */
    public function isFullyPacked(): bool
    {
        return $this->items->every(
            fn (SalesOrderItem $item): bool => $item->allocations->every(
                fn (StockAllocation $allocation): bool => $allocation->packed_at !== null
            )
        );
    }
}
