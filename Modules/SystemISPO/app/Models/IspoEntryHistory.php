<?php

namespace Modules\SystemISPO\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class IspoEntryHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ispo_document_entry_id',
        'user_id',
        'role',
        'status',
        'notes',
        'audit_status',
        'audit_notes',
        'attachments_snapshot',
    ];

    protected $casts = [
        'attachments_snapshot' => 'array',
    ];

    public function entry()
    {
        return $this->belongsTo(IspoDocumentEntry::class, 'ispo_document_entry_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
