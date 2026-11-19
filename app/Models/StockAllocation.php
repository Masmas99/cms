<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sales_order_item_id', 'inventory_stock_id', 'quantity', 'pick_sequence', 'picked_at', 'packed_at'])]
class StockAllocation extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'pick_sequence' => 'integer',
            'picked_at' => 'datetime',
            'packed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<SalesOrderItem, $this>
     */
    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    /**
     * @return BelongsTo<InventoryStock, $this>
     */
    public function inventoryStock(): BelongsTo
    {
        return $this->belongsTo(InventoryStock::class);
    }
}
