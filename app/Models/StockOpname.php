<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['reference_no', 'warehouse_id', 'zone', 'scheduled_date', 'status', 'notes', 'created_by', 'completed_at'])]
class StockOpname extends Model
{
    use HasUuids;

    public const STATUS_COUNTING = 'counting';

    public const STATUS_COMPLETED = 'completed';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<StockOpnameItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    /**
     * @return HasManyThrough<StockAdjustment, StockOpnameItem, $this>
     */
    public function adjustments(): HasManyThrough
    {
        return $this->hasManyThrough(StockAdjustment::class, StockOpnameItem::class);
    }

    /**
     * Number of counted lines.
     */
    public function countedLines(): int
    {
        return $this->items()->whereNotNull('counted_qty')->count();
    }

    /**
     * Whether every line has been counted.
     */
    public function isFullyCounted(): bool
    {
        return $this->items()->whereNull('counted_qty')->doesntExist();
    }
}
