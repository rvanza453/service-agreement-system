<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrItem extends Model
{
    use HasFactory;

    protected $fillable = ['purchase_request_id', 'product_id', 'job_id', 'item_name', 'specification', 'remarks', 'quantity', 'unit', 'price_estimation', 'subtotal', 'manual_category', 'url_link'];

    protected $appends = ['final_quantity'];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function poItems(): HasMany
    {
        return $this->hasMany(PoItem::class);
    }

    // Check if this item has been used in any PO
    public function hasPoGenerated()
    {
        return $this->poItems()->exists();
    }

    // Get the PO that contains this item (first one if multiple)
    public function getFirstPo()
    {
        $poItem = $this->poItems()->with('purchaseOrder')->first();
        return $poItem ? $poItem->purchaseOrder : null;
    }

    /**
     * Get final quantity considering HO adjustments
     * Returns the quantity from the highest-level HO approver who adjusted it,
     * or the original quantity if no HO adjustments were made
     */
    public function getFinalQuantity()
    {
        // Ensure purchaseRequest is loaded
        if (!$this->relationLoaded('purchaseRequest')) {
            $this->load('purchaseRequest');
        }
        
        $pr = $this->purchaseRequest;
        
        // If PR not found or not in approved status, return original quantity
        if (!$pr || $pr->status !== \Modules\PrSystem\Enums\PrStatus::APPROVED->value) {
            return $this->quantity;
        }
        
        // Get all approved HO approvals with adjusted quantities for this item
        $hoApprovals = PrApproval::where('purchase_request_id', $pr->id)
            ->where('status', \Modules\PrSystem\Enums\PrStatus::APPROVED->value)
            ->whereNotNull('adjusted_quantities')
            ->orderBy('level', 'desc') // Highest level first
            ->get();

        foreach ($hoApprovals as $approval) {
            $adjustedQty = $approval->getAdjustedQuantityForItem($this->id);
            if ($adjustedQty !== null) {
                return $adjustedQty;
            }
        }

        // No HO adjustments, return original quantity
        return $this->quantity;
    }

    /**
     * Accessor for final_quantity (for use in views and API responses)
     */
    public function getFinalQuantityAttribute()
    {
        return $this->getFinalQuantity();
    }
}
