<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseStock extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'warehouse_id', 'quantity'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getFormattedQuantityAttribute()
    {
        return rtrim(rtrim(number_format($this->quantity, 4, '.', ''), '0'), '.');
    }
}
