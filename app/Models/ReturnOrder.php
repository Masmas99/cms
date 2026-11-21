<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['reference_no', 'sales_order_id', 'customer_name', 'status', 'notes', 'received_by'])]
class ReturnOrder extends Model
{
    use HasUuids;

    public const STATUS_RECEIVED = 'received';

    public const STATUS_COMPLETED = 'completed';

    /**
     * @return BelongsTo<SalesOrder, $this>
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * @return HasMany<ReturnItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }

    /**
     * Whether every item has a final disposition.
     */
    public function isFullyDisposed(): bool
    {
        return $this->items()->where('disposition', 'pending')->doesntExist();
    }
}
