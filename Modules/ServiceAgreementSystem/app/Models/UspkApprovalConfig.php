<?php

namespace Modules\ServiceAgreementSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ServiceAgreementSystem\Database\Factories\UspkApprovalConfigFactory;

class UspkApprovalConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'role_name',
        'level',
        'min_value',
    ];

    public function department()
    {
        return $this->belongsTo(\Modules\ServiceAgreementSystem\Models\Department::class);
    }
}
