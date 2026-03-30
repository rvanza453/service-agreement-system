<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapexBudget extends Model
{
    protected $fillable = [
        'department_id',
        'capex_asset_id',
        'budget_code',
        'amount',
        'pta_amount',
        'remaining_amount',
        'original_quantity',
        'remaining_quantity',
        'is_budgeted',
        'fiscal_year',
        'is_active'
    ];

    protected $casts = [
        'is_budgeted' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function capexAsset()
    {
        return $this->belongsTo(CapexAsset::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $year = $model->fiscal_year ?? date('Y');
            $count = self::where('fiscal_year', $year)->count() + 1;
            $model->budget_code = 'CPX-BGT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        });
    }
}
