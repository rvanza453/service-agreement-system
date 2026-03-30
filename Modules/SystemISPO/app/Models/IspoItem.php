<?php

namespace Modules\SystemISPO\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IspoItem extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'type', 'code', 'name', 'description', 'order_index'];

    public function parent()
    {
        return $this->belongsTo(IspoItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(IspoItem::class, 'parent_id')->orderBy('order_index');
    }

    public function descendants()
    {
        return $this->children()->with('descendants');
    }
}
