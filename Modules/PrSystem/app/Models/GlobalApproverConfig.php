<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalApproverConfig extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'role_name', 'level', 'site_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
