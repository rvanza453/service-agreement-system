<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubDepartment extends Model
{
    protected $fillable = ['department_id', 'name', 'coa'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function jobCoas()
    {
        return $this->hasMany(JobCoa::class);
    }
}
