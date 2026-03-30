<?php

namespace Modules\ServiceAgreementSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ServiceAgreementSystem\Database\Factories\UspkApprovalSchemaStepFactory;

class UspkApprovalSchemaStep extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'schema_id',
        'level',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // protected static function newFactory(): UspkApprovalSchemaStepFactory
    // {
    //     // return UspkApprovalSchemaStepFactory::new();
    // }
}
