<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapexColumnConfig extends Model
{
    protected $fillable = [
        'department_id',
        'column_index',
        'label',
        'approver_role',
        'approver_user_id',
        'is_digital'
    ];

    protected $casts = [
        'is_digital' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}
