<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'approver_id',
        'level',
        'role_name',
        'status',
        'approved_at',
        'remarks',
        'adjusted_quantities',
        'hold_reply',
        'replied_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'replied_at' => 'datetime',
        'adjusted_quantities' => 'array',
    ];

    public function getAdjustedQuantityForItem($itemId)
    {
        if (!$this->adjusted_quantities) {
            return null;
        }
        return $this->adjusted_quantities[$itemId] ?? null;
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
