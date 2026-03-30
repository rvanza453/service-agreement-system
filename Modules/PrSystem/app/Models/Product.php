<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'unit', 'min_stock', 'category', 'price_estimation'];

    public function sites()
    {
        return $this->belongsToMany(Site::class, 'product_site', 'product_id', 'site_id')
                    ->withTimestamps();
    }
    
    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }
}
