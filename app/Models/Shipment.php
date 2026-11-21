<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sales_order_id', 'courier_id', 'tracking_number', 'packed_by', 'packed_at', 'shipped_at'])]
class Shipment extends Model
{
    use HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'packed_at' => 'datetime',
            'shipped_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<SalesOrder, $this>
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * @return BelongsTo<Courier, $this>
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function packer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packed_by');
    }
}
