<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'location',
        'address',
        'pic_name',
        'phone',
        'admin_phone',
        'email',
        'status',
    ];
}
