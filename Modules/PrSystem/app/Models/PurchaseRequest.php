<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'pr_number',
        'status',
        'request_date',
        'description',
        'total_estimated_cost',
        'sub_department_id',

    ];

    protected $casts = [
        'request_date' => 'date',
        'total_estimated_cost' => 'decimal:2',

    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(SubDepartment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PrApproval::class)->orderBy('level');
    }
    
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    public function getFinalTotalAttribute()
    {
        if ($this->status !== 'Approved') {
            return $this->total_estimated_cost;
        }

        // Calculate sum of items with final approved quantities
        return $this->items->sum(function ($item) {
            return $item->getFinalQuantity() * $item->price_estimation;
        });
    }

    /**
     * Get the date when PR was fully approved
     */
    public function getApprovedAtAttribute()
    {
        if ($this->status !== 'Approved') {
            return null;
        }

        // Get the HIGHEST-level approval record (HO/final approver)
        // ->reorder() is needed to clear the default orderBy('level') ASC from the relationship,
        // otherwise both ORDER BY clauses apply and ASC wins — returning level 1 (lowest) instead.
        $lastApproval = $this->approvals()
                             ->reorder()
                             ->where('status', 'Approved')
                             ->orderBy('level', 'desc')
                             ->first();

        // Fallback to updated_at if no approval record found (legacy data safety)
        return $lastApproval ? $lastApproval->approved_at : $this->updated_at;
    }

    /**
     * Check if PR is expired (more than 14 days since approval)
     */
    public function isExpired()
    {
        if ($this->status !== 'Approved') {
            return false;
        }
        
        $approvedAt = $this->approved_at;
        if (!$approvedAt) return false;
        
        // Check if 14 days have passed
        return $approvedAt->copy()->addDays(14)->isPast();
    }

    /**
     * Get the PO generation status of the PR items
     * Returns: 'Belum PO', 'Partial PO', 'Complete PO'
     */
    public function getPoStatusAttribute()
    {
        $totalItems = $this->items->count();
        
        if ($totalItems === 0) {
            return 'Belum PO';
        }

        // Count items that have at least one related PO Item
        $itemsWithPo = $this->items->filter(function ($item) {
            return $item->poItems()->exists();
        })->count();

        if ($itemsWithPo === 0) {
            return 'Waiting PO';
        }

        if ($itemsWithPo === $totalItems) {
            return 'Complete PO';
        }

        return 'Partial PO';
    }
    
    public function getCurrentApprover()
    {
        if ($this->status !== 'Pending' && $this->status !== 'On Hold') {
            return null;
        }

        // Find the first pending approval (lowest level that hasn't been approved)
        $pendingApproval = $this->approvals()
            ->where('status', 'Pending')
            ->orderBy('level')
            ->first();

        if (!$pendingApproval) {
            return null;
        }

        // Check if all lower levels are approved
        $lowerLevelsApproved = !$this->approvals()
            ->where('level', '<', $pendingApproval->level)
            ->where('status', '!=', 'Approved')
            ->exists();

        if ($lowerLevelsApproved) {
            return $pendingApproval->approver;
        }

        return null;
    }
}

