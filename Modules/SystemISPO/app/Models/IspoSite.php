<?php

namespace Modules\SystemISPO\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IspoSite extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name'];

    public function documents()
    {
        return $this->hasMany(IspoDocument::class);
    }
}
