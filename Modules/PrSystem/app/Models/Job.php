<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'department_id',
        'code',
        'name',
        'job_coa_id',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
        
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
