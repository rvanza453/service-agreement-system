<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'pr_item_id',
        'quantity',
        'unit',
        'unit_price',
        'subtotal'
    ];

    protected $casts = [
        // 'unit_price' => 'decimal:2', // Removed to support 3 decimals
        // 'subtotal' => 'decimal:2',
        'unit_price' => 'float',
        'subtotal' => 'float',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function prItem(): BelongsTo
    {
        return $this->belongsTo(PrItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($poItem) {
            $poItem->subtotal = $poItem->quantity * $poItem->unit_price;
        });
    }
}
