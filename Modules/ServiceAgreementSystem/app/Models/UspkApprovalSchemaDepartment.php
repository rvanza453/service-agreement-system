<?php

namespace Modules\ServiceAgreementSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ServiceAgreementSystem\Database\Factories\UspkApprovalSchemaDepartmentFactory;

class UspkApprovalSchemaDepartment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'schema_id',
        'department_id',
    ];

    // protected static function newFactory(): UspkApprovalSchemaDepartmentFactory
    // {
    //     // return UspkApprovalSchemaDepartmentFactory::new();
    // }
}
