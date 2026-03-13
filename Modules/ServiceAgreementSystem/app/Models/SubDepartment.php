<?php

namespace Modules\ServiceAgreementSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubDepartment extends Model
{
    protected $fillable = ['department_id', 'name', 'coa'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }
}
