<?php

namespace Modules\SystemISPO\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IspoDocument extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'year', 'document_number', 'start_date', 'end_date', 'status'];

    public function site()
    {
        return $this->belongsTo(\Modules\ServiceAgreementSystem\Models\Site::class, 'site_id');
    }

    public function entries()
    {
        return $this->hasMany(IspoDocumentEntry::class, 'ispo_document_id');
    }
}
