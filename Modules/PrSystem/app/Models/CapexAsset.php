<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapexAsset extends Model
{
    protected $fillable = ['code', 'name', 'description', 'is_active'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->code)) {
                $latest = static::latest('id')->first();
                $nextId = $latest ? $latest->id + 1 : 1;
                $model->code = 'CI-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }}
