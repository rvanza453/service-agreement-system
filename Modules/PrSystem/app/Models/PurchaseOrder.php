<?php

namespace Modules\PrSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'po_number',
        'po_date',
        'delivery_date',
        'pr_number',
        'pr_date',
        'status',
        'vendor_id',
        'vendor_name',
        'vendor_address',
        'vendor_postal_code',
        'vendor_phone',
        'vendor_contact_person',
        'vendor_contact_phone',
        'vendor_email',
        'discount_percentage',
        'discount_amount',
        'dpp_lainnya',
        'dpp',
        'ppn_percentage',
        'ppn_amount',
        'final_amount',
        'notes',
        'use_vat',
    ];

    protected $casts = [
        'po_date' => 'date',
        'delivery_date' => 'date',
        'pr_date' => 'date',
        // 'discount_percentage' => 'decimal:2', // Removed to support higher precision
        // 'discount_amount' => 'decimal:2',
        // 'dpp_lainnya' => 'decimal:2',
        // 'dpp' => 'decimal:2',
        // 'ppn_percentage' => 'decimal:2',
        // 'ppn_amount' => 'decimal:2',
        // 'final_amount' => 'decimal:2',
        'discount_percentage' => 'float',
        'discount_amount' => 'float',
        'dpp_lainnya' => 'float',
        'dpp' => 'float',
        'ppn_percentage' => 'float',
        'ppn_amount' => 'float',
        'final_amount' => 'float',
        'use_vat' => 'boolean',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PoItem::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    // Calculate subtotal from all items
    public function getSubtotalAttribute()
    {
        return $this->items->sum('subtotal');
    }

    // Calculate totals: Discount, DPP, PPN (12% if enabled), and Final Amount
    public function calculateTotals()
    {
        $subtotal = $this->items->sum('subtotal');
        
        // Calculate Discount
        $this->discount_amount = $subtotal * ($this->discount_percentage / 100);
        $amountAfterDiscount = $subtotal - $this->discount_amount;
        
        // DPP = Amount After Discount
        $this->dpp = $amountAfterDiscount;
        
        // Calculate PPN
        if ($this->use_vat) {
            // Auto-calculate DPP Lainnya
            $this->dpp_lainnya = $amountAfterDiscount * (11 / 12);
            
            // Set PPN to 12%
            $this->ppn_percentage = 12;
            // Calculate PPN = DPP Lainnya × 12%
            $this->ppn_amount = $this->dpp_lainnya * 0.12;
        } else {
            $this->dpp_lainnya = 0;
            $this->ppn_percentage = 0;
            $this->ppn_amount = 0;
        }
        
        // Calculate final amount = Amount After Discount + PPN
        $this->final_amount = $amountAfterDiscount + $this->ppn_amount;
        
        return $this;
    }
    // Get all related Purchase Requests
    public function getRelatedPrsAttribute()
    {
        // Case 1: Single PR PO (Legacy or Direct)
        if ($this->purchase_request_id) {
            $pr = PurchaseRequest::find($this->purchase_request_id);
            return $pr ? collect([$pr]) : collect([]);
        }

        // Case 2: Multi PR PO (From Items)
        $prs = collect([]);
        foreach ($this->items as $poItem) {
            if ($poItem->prItem && $poItem->prItem->purchaseRequest) {
                $prs->put($poItem->prItem->purchaseRequest->id, $poItem->prItem->purchaseRequest);
            }
        }
        
        return $prs->values();
    }
}
