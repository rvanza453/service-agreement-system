<?php

namespace Modules\PrSystem\Http\Controllers;

use Modules\PrSystem\Models\PurchaseOrder;
use Modules\PrSystem\Models\PurchaseRequest;
use Modules\PrSystem\Models\PoItem;
use Modules\PrSystem\Models\PrItem;
use Modules\PrSystem\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = PurchaseOrder::with(['purchaseRequest', 'items.prItem.purchaseRequest']);
        
        // --- VISIBILITY LOGIC ---
        $isGlobal = $user->hasRole(['Admin', 'Finance']) 
                || ($user->hasRole('Approver') && $user->site && $user->site->code === 'HO')
                || \Modules\PrSystem\Models\GlobalApproverConfig::where('user_id', $user->id)->exists();

        if ($isGlobal) {
            // Global View (Admin, Finance, HO Approvers)
        } elseif ($user->hasRole(['Purchasing', 'Approver'])) {
            // Site Scope
            $siteId = $user->site_id;
            // Filter POs that have items satisfying the site condition
            $query->whereHas('items.prItem.purchaseRequest.department', function($d) use ($siteId) {
                $d->where('site_id', $siteId);
            });
        } elseif ($user->hasRole('Finance')) {
            // Finance sees all (Unrestricted)
        } else {
            // Staff / Others -> Own POs (via PR)
            $query->whereHas('items.prItem.purchaseRequest', function($pr) use ($user) {
                $pr->where('user_id', $user->id);
            });
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%")
                  ->orWhere('pr_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('po_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('po_date', '<=', $request->end_date);
        }

        $pos = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('prsystem::po.index', compact('pos'));
    }


    public function create(Request $request)
    {
        // Try to get items from Current Request (POST) OR Old Session Input (Validation fail redirect - GET)
        $itemIds = $request->input('items', $request->input('selected_items')); 
        
        // If empty, try to fallback to specific old input structure if applicable (e.g. items array keys)
        if (empty($itemIds) && old('items')) {
            $oldItems = old('items');
            $itemIds = array_map(function($item) {
                return $item['pr_item_id'] ?? null;
            }, $oldItems);
            $itemIds = array_filter($itemIds); // Clean nulls
        }

        if (empty($itemIds)) {
            return redirect()->back()->with('error', 'Pilih minimal satu item untuk dibuatkan PO.');
        }

        // Load items with details
        $selectedItems = PrItem::with(['purchaseRequest', 'product'])
            ->whereIn('id', $itemIds)
            ->get();

        if ($selectedItems->isEmpty()) {
            return redirect()->back()->with('error', 'Item tidak ditemukan.');
        }

        // Validate approval
        foreach ($selectedItems as $item) {
            if ($item->purchaseRequest->status !== 'Approved') {
                return redirect()->back()->with('error', 'Item dari PR ' . $item->purchaseRequest->pr_number . ' belum disetujui.');
            }
            
            // Check Expiration
            if ($item->purchaseRequest->isExpired()) {
                 return redirect()->back()->with('error', 'Item dari PR ' . $item->purchaseRequest->pr_number . ' sudah kadaluarsa (lebih dari 14 hari sejak disetujui). Silakan ajukan PR ulang.');
            }

            // Populate final quantity accessor
            $item->final_quantity = $item->getFinalQuantity();
        }

        // Check for existing POs
        $itemsWithPo = $selectedItems->filter(function($item) {
            return $item->hasPoGenerated();
        });

        if ($itemsWithPo->count() > 0) {
            $names = $itemsWithPo->pluck('item_name')->join(', ');
            return redirect()->back()
                ->with('error', 'Item berikut sudah memiliki PO: ' . $names);
        }

        // Generate PR Info Strings
        $prs = $selectedItems->groupBy('purchase_request_id');
        $prInfo = [];
        $dates = [];

        foreach($prs as $prId => $items) {
            $pr = $items->first()->purchaseRequest;
            $count = $items->count();
            $prInfo[] = $pr->pr_number;
            if ($pr->request_date) {
                $dates[] = $pr->request_date;
            }
        }

        $prNumberString = implode(', ', $prInfo);
        
        // Format dates
        $uniqueDates = collect($dates)->map->format('d/m/Y')->unique()->values();
        $prDateString = $uniqueDates->join(', ');
        
        $firstPr = $selectedItems->first()->purchaseRequest;

        // Fetch Vendors for Dropdown
        $vendors = \Modules\PrSystem\Models\Vendor::where('status', '!=', 'Di Ajukan')->orderBy('name')->get();

        return view('prsystem::po.create', compact('selectedItems', 'prNumberString', 'prDateString', 'firstPr', 'vendors'));
    }

    public function store(Request $request)
    {
        // Sanitize inputs (convert comma to dot for decimals)
        if ($request->has('discount_percentage')) {
            $request->merge(['discount_percentage' => str_replace(',', '.', $request->discount_percentage)]);
        }
        
        if ($request->has('items')) {
            $items = $request->items;
            foreach ($items as $key => $item) {
                if (isset($item['unit_price'])) {
                    $items[$key]['unit_price'] = str_replace(',', '.', $item['unit_price']);
                }
            }
            $request->merge(['items' => $items]);
        }

        $request->validate([
            'po_number' => 'required|string',
            'vendor_name' => 'required|string|max:255',
            'vendor_address' => 'required|string',
            'vendor_phone' => 'required|string|max:50',
            'vendor_postal_code' => 'nullable|string|max:20',
            'vendor_contact_person' => 'nullable|string|max:255',
            'vendor_contact_phone' => 'nullable|string|max:50',
            'vendor_email' => 'nullable|email|max:255',
            'delivery_date' => 'nullable|date',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.pr_item_id' => 'required|exists:pr_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.new_product_code' => 'nullable|string|max:255',
            'items.*.new_product_category' => 'nullable|string|max:255',
            'pr_number_string' => 'required|string',
            'pr_date_string' => 'nullable|string',
            'use_vat' => 'nullable',
        ]);

        // Vendor Logic
        $vendorId = null;
        if ($request->vendor_id === 'new') {
            // Create New Vendor
            $newVendor = \Modules\PrSystem\Models\Vendor::create([
                'name' => $request->vendor_name,
                'address' => $request->vendor_address,
                'phone' => $request->vendor_phone,
                'location' => 'Unknown',
                'category' => 'General',
                'pic_name' => $request->vendor_contact_person,
                'email' => $request->vendor_email,
                'status' => 'Di Ajukan', // Pending Status
            ]);
            $vendorId = $newVendor->id;
        } else {
            $vendorId = $request->vendor_id;
        }

        // Create PO
        try {
            $po = PurchaseOrder::create([
                'purchase_request_id' => null, // Multi-PR support
                'po_number' => $request->po_number,
                'po_date' => now(),
                'delivery_date' => $request->delivery_date,
                'pr_number' => $request->pr_number_string,
                'pr_date' => $request->pr_date_string ? \Carbon\Carbon::createFromFormat('d/m/Y', explode(',', $request->pr_date_string)[0]) : null, 
                'status' => 'Issued',
                'vendor_id' => $vendorId, // Link to Vendor
                'vendor_name' => $request->vendor_name,
                'vendor_address' => $request->vendor_address,
                'vendor_phone' => $request->vendor_phone,
                'vendor_postal_code' => $request->vendor_postal_code,
                'vendor_contact_person' => $request->vendor_contact_person,
                'vendor_contact_phone' => $request->vendor_contact_phone,
                'vendor_email' => $request->vendor_email,
                'discount_percentage' => $request->discount_percentage ?? 0,
                'notes' => $request->notes,
                'use_vat' => $request->has('use_vat'), // Checkbox handling
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) { // Duplicate Entry code for MySQL
                return redirect()->back()->withInput()->with('error', 'Gagal membuat PO. Nomor PO "' . $request->po_number . '" sudah terdaftar. Harap gunakan nomor lain.');
            }
            throw $e;
        }

        // Create PO Items
        foreach ($request->items as $itemData) {
            $prItem = PrItem::find($itemData['pr_item_id']);

            // Handle Product Creation or Linking
            if (isset($itemData['new_product_code']) && !empty($itemData['new_product_code']) && $prItem && !$prItem->product_id) {
                // Check if product with this code already exists
                $existingProduct = Product::where('code', $itemData['new_product_code'])->first();
                
                if ($existingProduct) {
                    // Product already exists, just link it
                    $prItem->update(['product_id' => $existingProduct->id]);
                } else {
                    // Create new product
                    $newProduct = Product::create([
                        'code' => $itemData['new_product_code'],
                        'name' => $prItem->item_name,
                        'category' => $itemData['new_product_category'] ?? null,
                        'unit' => $itemData['unit'],
                        'price_estimation' => $itemData['unit_price'],
                        'min_stock' => 0,
                    ]);
                    
                    // Link PR Item to the new Product
                    $prItem->update(['product_id' => $newProduct->id]);
                }
            }

            PoItem::create([
                'purchase_order_id' => $po->id,
                'pr_item_id' => $itemData['pr_item_id'],
                'quantity' => $itemData['quantity'],
                'unit' => $itemData['unit'],
                'unit_price' => $itemData['unit_price'],
            ]);
        }

        // Calculate totals automatically using new formula
        $po->calculateTotals();
        $po->save();

        \Modules\PrSystem\Helpers\ActivityLogger::log('created', 'Created Purchase Order: ' . $po->po_number, $po);

        return redirect()->route('po.show', $po)
            ->with('success', 'PO berhasil dibuat!');
    }

    public function show(PurchaseOrder $po)
    {
        $po->load([
            'items.prItem.product',
            'items.prItem.job',
            'purchaseRequest.department',
            'purchaseRequest.subDepartment',
            'vendor'
        ]);

        return view('prsystem::po.show', compact('po'));
    }

    public function edit(PurchaseOrder $po)
    {
        $po->load(['items.prItem']);
        return view('prsystem::po.edit', compact('po'));
    }

    public function update(Request $request, PurchaseOrder $po)
    {
        // Sanitize inputs (convert comma to dot for decimals)
        if ($request->has('discount_percentage')) {
            $request->merge(['discount_percentage' => str_replace(',', '.', $request->discount_percentage)]);
        }

        if ($request->has('items')) {
            $items = $request->items;
            foreach ($items as $key => $item) {
                if (isset($item['unit_price'])) {
                    $items[$key]['unit_price'] = str_replace(',', '.', $item['unit_price']);
                }
            }
            $request->merge(['items' => $items]);
        }

        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'vendor_address' => 'required|string',
            'vendor_phone' => 'required|string|max:50',
            'vendor_postal_code' => 'nullable|string|max:20',
            'vendor_contact_person' => 'nullable|string|max:255',
            'vendor_contact_phone' => 'nullable|string|max:50',
            'vendor_email' => 'nullable|email|max:255',
            'delivery_date' => 'nullable|date',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'status' => 'required|in:Issued,Completed,Cancelled',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:po_items,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'use_vat' => 'nullable',
        ]);

        $po->update([
            'vendor_name' => $request->vendor_name,
            'vendor_address' => $request->vendor_address,
            'vendor_phone' => $request->vendor_phone,
            'vendor_postal_code' => $request->vendor_postal_code,
            'vendor_contact_person' => $request->vendor_contact_person,
            'vendor_contact_phone' => $request->vendor_contact_phone,
            'vendor_email' => $request->vendor_email,
            'delivery_date' => $request->delivery_date,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'notes' => $request->notes,
            'status' => $request->status,
            'use_vat' => $request->has('use_vat'),
        ]);

        // Update PO Items prices
        foreach ($request->items as $itemData) {
            $poItem = PoItem::findOrFail($itemData['id']);
            $poItem->update([
                'unit_price' => $itemData['unit_price'],
            ]);
        }

        // Recalculate totals
        $po->calculateTotals();
        $po->save();

        \Modules\PrSystem\Helpers\ActivityLogger::log('updated', 'Updated Purchase Order: ' . $po->po_number . ' (Status: ' . $po->status . ')', $po);

        return redirect()->route('po.show', $po)
            ->with('success', 'PO berhasil diupdate!');
    }

    public function destroy(PurchaseOrder $po)
    {
        // Only allow deletion if status is not Completed
        if ($po->status === 'Completed') {
            return redirect()->route('po.index')
                ->with('error', 'PO yang sudah Completed tidak bisa dihapus.');
        }

        $po_number = $po->po_number;
        $po->delete();

        \Modules\PrSystem\Helpers\ActivityLogger::log('deleted', 'Deleted Purchase Order: ' . $po_number);

        return redirect()->route('po.index')
            ->with('success', 'PO berhasil dihapus!');
    }
}
