<?php

namespace Modules\QcComplaintSystem\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QcFindingComment extends Model
{
    protected $fillable = [
        'qc_finding_id',
        'parent_comment_id',
        'user_id',
        'content',
    ];

    public function finding(): BelongsTo
    {
        return $this->belongsTo(QcFinding::class, 'qc_finding_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(QcFindingComment::class, 'parent_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(QcFindingComment::class, 'parent_comment_id')
            ->orderBy('created_at', 'asc');
    }

    public function allReplies(): HasMany
    {
        return $this->hasMany(QcFindingComment::class, 'parent_comment_id');
    }

    public function isReply(): bool
    {
        return !is_null($this->parent_comment_id);
    }

    public function scopeMainComments($query)
    {
        return $query->whereNull('parent_comment_id')->orderBy('created_at', 'desc');
    }
}
