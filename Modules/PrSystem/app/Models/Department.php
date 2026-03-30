<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'warehouse_id',
        'name',
        'coa',
        'budget',
        'use_global_approval',
        'budget_type'
    ];

    protected $casts = [
        'budget_type' => \Modules\PrSystem\Enums\BudgetingType::class,
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function approverConfigs(): HasMany
    {
        return $this->hasMany(ApproverConfig::class);
    }

    public function subDepartments(): HasMany
    {
        return $this->hasMany(SubDepartment::class);
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function capexConfigs(): HasMany
    {
        return $this->hasMany(CapexColumnConfig::class);
    }
}
