<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'location_id', 'user_id', 'type', 'quantity', 'batch_number', 'expired_date', 'reference_no', 'notes'])]
class StockMovement extends Model
{
    use HasUuids;

    public const TYPE_INBOUND = 'inbound';

    public const TYPE_OUTBOUND = 'outbound';

    public const TYPE_TRANSFER_IN = 'transfer_in';

    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public const TYPE_ADJUSTMENT_IN = 'adjustment_in';

    public const TYPE_ADJUSTMENT_OUT = 'adjustment_out';

    public const TYPE_RETURN = 'return';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'expired_date' => 'date',
        ];
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Human readable movement label.
     */
    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_INBOUND => 'Barang masuk',
            self::TYPE_OUTBOUND => 'Barang keluar',
            self::TYPE_TRANSFER_IN => 'Transfer masuk',
            self::TYPE_TRANSFER_OUT => 'Transfer keluar',
            self::TYPE_ADJUSTMENT_IN => 'Penyesuaian (+)',
            self::TYPE_ADJUSTMENT_OUT => 'Penyesuaian (-)',
            self::TYPE_RETURN => 'Retur',
            default => ucfirst($this->type),
        };
    }
}
