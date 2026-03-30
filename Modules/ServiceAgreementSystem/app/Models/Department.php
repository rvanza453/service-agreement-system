<?php

namespace Modules\ServiceAgreementSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Added for belongsToMany relation

// Assuming UspkSubmission and UspkApprovalSchema are in the same namespace or need to be imported
// If they are in a different namespace, you would need to add their specific use statements, e.g.:
// use Modules\ServiceAgreementSystem\Models\UspkSubmission;
// use Modules\ServiceAgreementSystem\Models\UspkApprovalSchema;

class Department extends Model
{
    protected $fillable = ['site_id', 'name', 'coa', 'budget_type', 'budget'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function uspkSubmissions(): HasMany // Added return type hint for consistency
    {
        return $this->hasMany(UspkSubmission::class);
    }

    public function approvalSchemas(): BelongsToMany // Added return type hint for consistency
    {
        return $this->belongsToMany(UspkApprovalSchema::class, 'uspk_approval_schema_departments', 'department_id', 'schema_id');
    }

    public function subDepartments(): HasMany
    {
        return $this->hasMany(SubDepartment::class);
    }
}
