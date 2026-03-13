<?php

namespace Modules\ServiceAgreementSystem\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UspkSubmission extends Model
{
    protected $fillable = [
        'uspk_number',
        'title',
        'description',
        'location',
        'work_type',
        'department_id',
        'sub_department_id',
        'block_id',
        'job_id',
        'uspk_budget_activity_id',
        'estimated_value',
        'estimated_duration',
        'status',
        'submitted_by',
        'submitted_at',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(SubDepartment::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function budgetActivity(): BelongsTo
    {
        return $this->belongsTo(UspkBudgetActivity::class, 'uspk_budget_activity_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function tenders(): HasMany
    {
        return $this->hasMany(UspkTender::class);
    }

    public function selectedTender(): HasOne
    {
        return $this->hasOne(UspkTender::class)->where('is_selected', true);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(UspkApproval::class)->orderBy('level');
    }

    /**
     * Cek apakah USPK masih bisa diedit
     */
    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Cek apakah USPK bisa disubmit
     */
    public function isSubmittable(): bool
    {
        return $this->status === self::STATUS_DRAFT && $this->tenders()->count() >= 1;
    }
}
