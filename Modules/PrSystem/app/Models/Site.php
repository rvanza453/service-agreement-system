<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'location'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_site', 'site_id', 'product_id')
                    ->withTimestamps();
    }
    
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
