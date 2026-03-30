<?php

namespace Modules\QcComplaintSystem\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcFindingCompletionEvidence extends Model
{
    protected $table = 'qc_finding_completion_evidences';

    protected $fillable = [
        'qc_finding_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    public function finding(): BelongsTo
    {
        return $this->belongsTo(QcFinding::class, 'qc_finding_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}