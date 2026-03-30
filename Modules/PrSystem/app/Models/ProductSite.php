<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductSite extends Pivot
{
    protected $table = 'product_site';
}