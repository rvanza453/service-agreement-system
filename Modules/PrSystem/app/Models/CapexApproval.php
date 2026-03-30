<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapexApproval extends Model
{
    protected $fillable = [
        'capex_request_id',
        'column_index',
        'approver_id',
        'status',
        'remarks',
        'signed_at'
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function capexRequest()
    {
        return $this->belongsTo(CapexRequest::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }}
