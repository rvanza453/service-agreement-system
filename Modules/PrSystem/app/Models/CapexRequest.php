<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapexRequest extends Model
{
    protected $fillable = [
        'capex_number',
        'user_id',
        'department_id',
        'capex_budget_id',
        'quantity',
        'price',
        'amount',
        'type',
        'code_budget_ditanam',
        'description',
        'questionnaire_answers',
        'status',
        'current_step',
        'signed_file_path',
        'supporting_document_path',
        'is_verified',
        'pr_id'
    ];

    protected $casts = [
        'questionnaire_answers' => 'array',
        'amount' => 'decimal:2',
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'code_budget_ditanam' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->capex_number)) {
                // Format: XXXX/CPX/SITE/ROMAN/YEAR
                $siteName = $model->department->site->name ?? 'HO';
                $romans = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
                $roman = $romans[date('n')] ?? 'I';
                $year = date('Y');
                
                $latest = static::whereYear('created_at', $year)->latest('id')->first();
                $sequence = $latest ? intval(substr($latest->capex_number, 0, 4)) + 1 : 1;
                
                $model->capex_number = sprintf("%04d/CPX/%s/%s/%s", $sequence, $siteName, $roman, $year);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function capexBudget()
    {
        return $this->belongsTo(CapexBudget::class);
    }

    public function approvals()
    {
        return $this->hasMany(CapexApproval::class);
    }}
