<?php

namespace Modules\ServiceAgreementSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UspkTender extends Model
{
    protected $fillable = [
        'uspk_submission_id',
        'contractor_id',
        'tender_value',
        'tender_duration',
        'description',
        'is_selected',
        'attachment_path',
    ];

    protected $casts = [
        'tender_value' => 'decimal:2',
        'is_selected' => 'boolean',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(UspkSubmission::class, 'uspk_submission_id');
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }
}
