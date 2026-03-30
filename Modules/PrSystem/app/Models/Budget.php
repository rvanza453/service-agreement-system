<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = ['sub_department_id', 'department_id', 'job_id', 'category', 'amount', 'used_amount', 'year'];

    public function subDepartment()
    {
        return $this->belongsTo(SubDepartment::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
