<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'receipt_id', 'purchase_order_item_id', 'product_id', 'location_id', 'put_away_location_id',
    'batch_number', 'expired_date', 'qty_good', 'qty_rejected', 'status', 'notes',
])]
class ReceiptItem extends Model
{
    use HasUuids;

    public const STATUS_STAGED = 'staged';

    public const STATUS_PUT_AWAY = 'put_away';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expired_date' => 'date',
            'qty_good' => 'float',
            'qty_rejected' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Receipt, $this>
     */
    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }

    /**
     * @return BelongsTo<PurchaseOrderItem, $this>
     */
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function putAwayLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'put_away_location_id');
    }
}
