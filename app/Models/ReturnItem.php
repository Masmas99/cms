<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'return_order_id', 'product_id', 'quantity', 'condition', 'disposition',
    'location_id', 'batch_number', 'notes', 'decided_by', 'decided_at',
])]
class ReturnItem extends Model
{
    public const CONDITION_GOOD = 'good';

    public const CONDITION_DAMAGED = 'damaged';

    public const DISPOSITION_PENDING = 'pending';

    public const DISPOSITION_RESTOCK = 'restock';

    public const DISPOSITION_QUARANTINE = 'quarantine';

    public const DISPOSITION_SCRAP = 'scrap';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'decided_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ReturnOrder, $this>
     */
    public function returnOrder(): BelongsTo
    {
        return $this->belongsTo(ReturnOrder::class);
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
     * @return BelongsTo<User, $this>
     */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /**
     * Human readable disposition label.
     */
    public function dispositionLabel(): string
    {
        return match ($this->disposition) {
            self::DISPOSITION_PENDING => 'Menunggu disposisi',
            self::DISPOSITION_RESTOCK => 'Restock',
            self::DISPOSITION_QUARANTINE => 'Karantina',
            self::DISPOSITION_SCRAP => 'Scrap',
            default => ucfirst($this->disposition),
        };
    }
}
