<?php

namespace App\Models;

use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['warehouse_id', 'zone', 'aisle', 'rack', 'bin_code', 'full_code', 'capacity_m3', 'capacity_kg', 'is_staging', 'is_active'])]
class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity_m3' => 'float',
            'capacity_kg' => 'float',
            'is_staging' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Build the scannable full code for a bin.
     */
    public static function buildFullCode(string $warehouseCode, string $zone, string $aisle, string $rack, string $binCode): string
    {
        return strtoupper(implode('-', array_filter([$warehouseCode, $zone, $aisle, $rack, $binCode])));
    }

    /**
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return HasMany<InventoryStock, $this>
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    /**
     * @param  Builder<Location>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Location>  $query
     */
    public function scopePickable(Builder $query): void
    {
        $query->where('is_active', true)->where('is_staging', false);
    }

    /**
     * Total quantity physically stored in this bin.
     */
    public function quantityOnHand(): float
    {
        return (float) $this->stocks()->sum('qty_on_hand');
    }

    /**
     * Whether the bin currently holds any stock.
     */
    public function isOccupied(): bool
    {
        return $this->stocks()->where('qty_on_hand', '>', 0)->exists();
    }
}
