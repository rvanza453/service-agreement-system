<?php

namespace Modules\QcComplaintSystem\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcApprovalConfig extends Model
{
    protected $fillable = [
        'approver_user_id',
        'approver_user_ids',
        'updated_by',
    ];

    protected $casts = [
        'approver_user_ids' => 'array',
    ];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getApproverUserIdsAttribute($value): array
    {
        if (is_array($value)) {
            return array_values(array_unique(array_map('intval', $value)));
        }

        $decoded = json_decode((string) $value, true);

        if (is_array($decoded)) {
            return array_values(array_unique(array_map('intval', $decoded)));
        }

        return $this->approver_user_id ? [(int) $this->approver_user_id] : [];
    }
}
