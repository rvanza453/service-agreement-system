<?php

namespace Modules\ServiceAgreementSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ServiceAgreementSystem\Database\Factories\UspkApprovalSchemaFactory;

class UspkApprovalSchema extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'uspk_approval_schema_departments', 'schema_id', 'department_id');
    }

    public function steps()
    {
        return $this->hasMany(UspkApprovalSchemaStep::class, 'schema_id')->orderBy('level');
    }

    // protected static function newFactory(): UspkApprovalSchemaFactory
    // {
    //     // return UspkApprovalSchemaFactory::new();
    // }
}
