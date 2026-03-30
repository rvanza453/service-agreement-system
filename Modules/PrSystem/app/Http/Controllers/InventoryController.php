<?php

namespace Modules\PrSystem\Http\Controllers;

use Modules\PrSystem\Models\Product;
use Modules\PrSystem\Models\Site;
use Modules\PrSystem\Models\StockMovement;
use Modules\PrSystem\Models\Warehouse;
use Modules\PrSystem\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InventoryController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::with(['site'])->withCount('stocks')->get();
        return view('prsystem::inventory.index', compact('warehouses'));
    }

    public function create()
    {
        $sites = Site::all();
        return view('prsystem::inventory.create', compact('sites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'site_id' => 'required|exists:sites,id',
        ]);

        Warehouse::create($request->only('name', 'site_id'));

        return redirect()->route('inventory.index')->with('success', 'Gudang berhasil dibuat.');
    }

    public function edit(Warehouse $warehouse)
    {
        $sites = Site::all();
        return view('prsystem::inventory.create', compact('warehouse', 'sites'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'site_id' => 'required|exists:sites,id',
        ]);

        $warehouse->update($request->only('name', 'site_id'));

        return redirect()->route('inventory.index')->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy(Request $request, Warehouse $warehouse)
    {
        // Accept current user password; keep optional fallback to system verification password.
        $password = $request->input('admin_password');
        $isUserPasswordValid = $password && Hash::check($password, (string) auth()->user()?->password);
        $systemVerificationPassword = config('prsystem.app.admin_verification_password', config('app.admin_verification_password'));
        $isSystemPasswordValid = !empty($systemVerificationPassword) && hash_equals((string) $systemVerificationPassword, (string) $password);

        if (!$isUserPasswordValid && !$isSystemPasswordValid) {
            return back()->with('error', 'Password verifikasi salah!');
        }

        if ($warehouse->stocks()->where('quantity', '>', 0)->exists()) {
             return redirect()->route('inventory.index')->with('error', 'Gudang tidak bisa dihapus karena masih menampung stok.');
        }

        $name = $warehouse->name;
        $warehouse->delete();
        \Modules\PrSystem\Helpers\ActivityLogger::log('deleted', 'Deleted Warehouse: ' . $name);
        return redirect()->route('inventory.index')->with('success', 'Warehouse deleted successfully.');
    }

    public function show(Request $request, Warehouse $warehouse)
    {
        $query = $warehouse->stocks()->with('product');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        $stocks = $query->paginate(20)->withQueryString();
        $movements = $warehouse->movements()->with(['product', 'user'])->latest('date')->limit(20)->get();
        
        return view('prsystem::inventory.show', compact('warehouse', 'stocks', 'movements'));
    }

    public function createMovement(Warehouse $warehouse, $type)
    {
        if (!in_array($type, ['IN', 'OUT'])) {
            abort(404);
        }
        
        $products = Product::orderBy('code')->get();
        // Use eager loading for subdepartments to know their parent department's budget type
        $departments = \Modules\PrSystem\Models\Department::with('subDepartments')->orderBy('name')->get(); 
        
        // Filter Jobs by the Warehouse's Site
        $jobs = \Modules\PrSystem\Models\Job::where('site_id', $warehouse->site_id)
                    ->orderBy('code')
                    ->get();
        
        return view('prsystem::inventory.movement', compact('warehouse', 'type', 'products', 'departments', 'jobs'));
    }

    public function storeMovement(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'type' => 'required|in:IN,OUT',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.remarks' => 'nullable|string',
            'items.*.sub_department_id' => 'required_if:type,OUT|nullable|exists:sub_departments,id',
            'items.*.sub_department_id' => 'required_if:type,OUT|nullable|exists:sub_departments,id',
            'items.*.job_id' => 'nullable|exists:jobs,id',
            'items.*.price' => 'required_if:type,IN|nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $warehouse) {
                foreach ($request->items as $item) {
                    $quantity = $item['quantity'];
                    
                    // Adjust stock based on type
                    $stock = WarehouseStock::firstOrCreate(
                        ['warehouse_id' => $warehouse->id, 'product_id' => $item['product_id']],
                        ['quantity' => 0]
                    );

                    if ($request->type === 'OUT') {
                        if ($stock->quantity < $quantity) {
                             $prodName = Product::find($item['product_id'])->name ?? 'Unknown';
                             throw new \Exception("Stok tidak mencukupi untuk item: {$prodName} (Sisa: {$stock->quantity}, Diminta: {$quantity})");
                        }
                        $stock->decrement('quantity', $quantity);
                    } else {
                        $stock->increment('quantity', $quantity);
                    }

                    // Record Movement & WAC
                    $product = Product::find($item['product_id']);
                    $movementPrice = 0;

                    if ($request->type === 'IN') {
                        // Last Price Logic
                        // 1. Incoming Data
                        $incomingQty = $quantity;
                        $incomingPrice = $item['price'];

                        // 2. Update Product Price to the Latest Incoming Price
                        // Update product price estimation based on latest incoming price
                        $product->price_estimation = $incomingPrice;
                        $product->save();

                        $movementPrice = $incomingPrice;
                    } else {
                        // OUT: Use current system price
                        $movementPrice = $product->price_estimation ?? 0;
                    }

                    StockMovement::create([
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $item['product_id'],
                        'user_id' => auth()->id(),
                        'type' => $request->type,
                        'quantity' => $quantity,
                        'date' => $request->date,
                        'remarks' => $item['remarks'] ?? null,
                        'sub_department_id' => $request->type === 'OUT' ? ($item['sub_department_id'] ?? null) : null,
                        'job_id' => $request->type === 'OUT' ? ($item['job_id'] ?? null) : null,
                        'price' => $movementPrice,
                    ]);

                    // Budget Deduction Logic (Increment Used Amount)
                    // Note: using $movementPrice (which is system price for OUT)
                    $cost = ($movementPrice) * $quantity;
                    if ($request->type === 'OUT' && $cost > 0) {
                         $subDept = \Modules\PrSystem\Models\SubDepartment::find($item['sub_department_id'] ?? null);
                         
                         if ($subDept) {
                             $year = date('Y', strtotime($request->date));
                             $budget = null;

                             // Check Department Budget Type
                             if ($subDept->department->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA) {
                                  $jobId = $item['job_id'] ?? null;
                                  if ($jobId) {
                                      // Job Budget: Link to Department, not SubDepartment
                                      $budget = \Modules\PrSystem\Models\Budget::firstOrCreate(
                                          [
                                              'department_id' => $subDept->department_id,
                                              'job_id' => $jobId,
                                              'year' => $year,
                                          ],
                                          [
                                              'amount' => 0,
                                              'category' => null,
                                              'sub_department_id' => null,
                                              'used_amount' => 0
                                          ]
                                      );
                                  }
                             } else {
                                  // STATION: Force 'Sparepart' as requested by user
                                  $budgetCategory = 'Sparepart';

                                  $budget = \Modules\PrSystem\Models\Budget::firstOrCreate(
                                      [
                                          'sub_department_id' => $subDept->id,
                                          'category' => $budgetCategory,
                                          'year' => $year
                                      ],
                                      [
                                          'amount' => 0,
                                          'job_id' => null,
                                          'used_amount' => 0
                                      ]
                                  );
                             }

                             if ($budget) {
                                 $budget->increment('used_amount', $cost);
                             }
                        }
                    }
                }
            });
            
            \Modules\PrSystem\Helpers\ActivityLogger::log('movement', 'Recorded Inventory Movement: ' . $request->type . ' (' . count($request->items) . ' items)', $warehouse);


        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('inventory.show', $warehouse)->with('success', 'Pergerakan stok berhasil dicatat.');
    }

    public function history(Request $request, Warehouse $warehouse)
    {
        $query = $warehouse->movements()->with(['product', 'user', 'subDepartment.department', 'job']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $movements = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->paginate(20)->withQueryString();
        $products = Product::orderBy('code')->get();

        return view('prsystem::inventory.history', compact('warehouse', 'movements', 'products'));
    }
}
