<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sku', 'barcode', 'name', 'category', 'uom', 'rotation', 'min_stock', 'max_stock', 'unit_cost', 'weight_kg', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public const ROTATION_FIFO = 'FIFO';

    public const ROTATION_FEFO = 'FEFO';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_stock' => 'float',
            'max_stock' => 'float',
            'unit_cost' => 'decimal:2',
            'weight_kg' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<InventoryStock, $this>
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * @param  Builder<Product>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Total units available to promise across every bin.
     */
    public function availableQuantity(): float
    {
        return (float) $this->stocks()
            ->selectRaw('COALESCE(SUM(qty_on_hand - qty_reserved), 0) as available')
            ->value('available');
    }

    /**
     * Total units physically on hand across every bin.
     */
    public function onHandQuantity(): float
    {
        return (float) $this->stocks()->sum('qty_on_hand');
    }

    /**
     * Whether stock has reached the configured reorder point.
     */
    public function isBelowMinimum(): bool
    {
        return $this->min_stock > 0 && $this->availableQuantity() <= $this->min_stock;
    }

    /**
     * Whether the product uses expiry based rotation.
     */
    public function usesFefo(): bool
    {
        return $this->rotation === self::ROTATION_FEFO;
    }
}
