<?php

namespace Modules\SystemISPO\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IspoDocumentEntry extends Model
{
    use HasFactory;

    protected $fillable = ['ispo_document_id', 'ispo_item_id', 'status', 'notes', 'attachment_path'];

    public function document()
    {
        return $this->belongsTo(IspoDocument::class, 'ispo_document_id');
    }

    public function item()
    {
        return $this->belongsTo(IspoItem::class, 'ispo_item_id');
    }

    public function attachments()
    {
        return $this->hasMany(IspoEntryAttachment::class, 'ispo_document_entry_id');
    }

    public function history()
    {
        return $this->hasMany(IspoEntryHistory::class, 'ispo_document_entry_id')->orderBy('created_at', 'desc');
    }
}
