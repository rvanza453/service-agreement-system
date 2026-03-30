<?php

namespace Modules\SystemISPO\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IspoEntryAttachment extends Model
{
    use HasFactory;

    protected $fillable = ['ispo_document_entry_id', 'file_path', 'file_name'];

    public function entry()
    {
        return $this->belongsTo(IspoDocumentEntry::class, 'ispo_document_entry_id');
    }
}
