<?php

namespace Modules\PrSystem\Http\Controllers;

use Illuminate\Http\Request;
use Modules\PrSystem\Models\Warehouse;
use Modules\PrSystem\Models\Product;
use Modules\PrSystem\Models\StockMovement;
use Modules\PrSystem\Models\WarehouseStock;
use Modules\PrSystem\Models\Job;
use Modules\PrSystem\Models\SubDepartment;
use Modules\PrSystem\Models\Budget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryImportController extends Controller
{
    public function formOut()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        // Load departments for manual override (Sorted by Name)
        $departments = \Modules\PrSystem\Models\Department::orderBy('name')->get();
        return view('prsystem::inventory.import_out', compact('warehouses', 'departments'));
    }

    public function resetData()
    {
        try {
            // 1. Truncate Inventory (Stock levels)
            // Note: Truncate is a DDL operation and cannot be rolled back in MySQL.
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('warehouse_stocks')->truncate();
            DB::table('stock_movements')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // 2. Reset Budget Used Amounts (Keep the budget limits, just reset usage)
            DB::table('budgets')->update(['used_amount' => 0]);

            return "DATA RESET SUCCESSFUL! \n\n - Warehouse Stocks: TRUNCATED \n - Stock Movements: TRUNCATED \n - Budget Used Amount: RESET TO 0";

        } catch (\Exception $e) {
            return "RESET FAILED: " . $e->getMessage();
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $file = $request->file('file');
        // Default Warehouse selected by user
        $defaultWarehouse = Warehouse::with('site')->findOrFail($request->warehouse_id);
        
        // Determine if this Site follows "Traksi" logic (KDE, MJE, PKS)
        // Check both Site Name and Warehouse Name
        $siteName = strtoupper($defaultWarehouse->site->name ?? '');
        $warehouseNameUpper = strtoupper($defaultWarehouse->name);
        
        $isTraksiUnit = \Illuminate\Support\Str::contains($siteName, ['KDE', 'MJE', 'PKS']) 
                     || \Illuminate\Support\Str::contains($warehouseNameUpper, ['KDE', 'MJE', 'PKS']);
        
        \Illuminate\Support\Facades\Log::info("Starting Import via Web. Target Warehouse: {$defaultWarehouse->name}, Site: {$siteName}, IsTraksi: " . ($isTraksiUnit ? 'Yes' : 'No'));

        if (($handle = fopen($file->getRealPath(), 'r')) !== FALSE) {
            // Fix for Mac/Legacy line endings
            ini_set('auto_detect_line_endings', true);

            // DETECT DELIMITER
            // Read first line to check delimiter
            $firstLine = fgets($handle);
            rewind($handle); // Go back to start
            $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
            
            \Illuminate\Support\Facades\Log::info("Detected Delimiter: '$delimiter'");

            // Get Header
            $header = fgetcsv($handle, 1000, $delimiter);
            // DB Transaction
            DB::beginTransaction();
            try {
                $rowNumber = 1;
                $processed = 0;
                $stockUpdated = 0;
                $budgetUpdated = 0;

                // Increase length limit to avoid splitting long lines
                while (($data = fgetcsv($handle, 4096, $delimiter)) !== FALSE) {
                    $rowNumber++;
                    
                    // Skip empty rows or rows with insufficient columns
                    // We need at least up to column 16 (Price OUT) so count should be >= 17 ideally, or just check specific indices
                    if (count($data) < 12) {
                        continue;
                    }

                    // Parse Date
                    $dateRaw = trim($data[0]);
                    try {
                        $date = Carbon::parse($dateRaw);
                    } catch (\Exception $e) {
                         try {
                             $date = Carbon::createFromFormat('d/m/Y', $dateRaw);
                         } catch (\Exception $ex) {
                             \Illuminate\Support\Facades\Log::warning("Row $rowNumber skipped. Invalid Date: '$dateRaw'");
                             continue; 
                         }
                    }

                    // Map Product - ADJUSTED INDICES (E & J Removed)
                    // Index 7: ITEM ID (Old 8)
                    // Index 8: ITEM NAME (Old 10)
                    // Index 9: UNIT ID (Old 11)
                    $itemCode = trim($data[7] ?? '');
                    $itemName = trim($data[8] ?? '');
                    $unitName = trim($data[9] ?? '');
                    
                    if (empty($itemCode)) {
                        continue;
                    }

                    // Find or Create Product
                    $product = Product::firstOrCreate(
                        ['code' => $itemCode],
                        [
                            'name' => $itemName,
                            'unit' => $unitName,
                            'price_estimation' => 0,
                            'min_stock' => 0
                        ]
                    );
                    
                    // Auto-link Product to Warehouse Site
                    if ($defaultWarehouse->site_id && !$product->sites()->where('site_id', $defaultWarehouse->site_id)->exists()) {
                        $product->sites()->attach($defaultWarehouse->site_id);
                        \Illuminate\Support\Facades\Log::info("Product {$product->code} linked to Site ID: {$defaultWarehouse->site_id}");
                    }

    // Determine Module
    $module = strtoupper(trim($data[2]));

    $qty = 0;
    $price = 0;
    $type = '';
    $subDepartmentId = null;
    $jobId = null;
    $movementRemarks = "Import: " . ($data[1] ?? '-');
    
    if ($module === 'STOCK-OUT') {
        $type = 'OUT';
        
        // Qty OUT (Col 14 - Index 13)
        $qtyRaw = trim($data[13] ?? '0');
        $qty = (float) str_replace(',', '', $qtyRaw);
        
        // Price OUT (Col 15 - Index 14)
        $priceRaw = trim($data[14] ?? '0');
        $price = (float) str_replace(['Rp', ','], '', $priceRaw);

        if ($qty <= 0) {
            continue;
        }
        
        // Update Price Estimation if available
        if ($price > 0) {
            $product->update(['price_estimation' => $price]);
        }
        
        // Create Stock Movement
        $movement = StockMovement::create([
            'warehouse_id' => $defaultWarehouse->id,
            'product_id' => $product->id,
            'date' => $date,
            'type' => 'OUT',
            'quantity' => $qty,
            'price' => $price,
            'description' => "Imported Stock OUT (Ref: {$data[1]})",
            'doc_id' => $data[1] ?? null,
            'job_id' => null,
            'sub_department_id' => null,
        ]);

        // Start Deduct Stock
        $stock = WarehouseStock::firstOrCreate(
            ['warehouse_id' => $defaultWarehouse->id, 'product_id' => $product->id],
            ['quantity' => 0]
        );
        $stock->decrement('quantity', $qty);
        $stockUpdated++;

        // BUDGET LOGIC
        $coaFull = trim($data[3]);
        if (!empty($coaFull)) {
            $segmentsToKeep = 4; // Standard: x.x.x.xx

            // COA berawalan '1' selalu baca 5 segmen (x.x.x.xx.xxx) — berlaku semua site termasuk SAE
            if (str_starts_with($coaFull, '1')) {
                $segmentsToKeep = 5;
            }

            // Extract Code
            $parts = explode('.', $coaFull);
            if (count($parts) >= $segmentsToKeep) {
                $codeToCheck = implode('.', array_slice($parts, 0, $segmentsToKeep));
                
                // Aggressive Sanitize (Remove UTF-8 BOM, non-breaking spaces, control chars)
                $codeToCheck = preg_replace('/[\x00-\x1F\x7F]/u', '', $codeToCheck); 
            
                if ($request->filled('override_department_id')) {
                    $linkedDeptIds = [(int)$request->override_department_id];
                } else {
                    // Auto-detect from Warehouse links
                    $linkedDeptIds = $defaultWarehouse->departments()->pluck('id')->toArray();
                }

                // Determine Budget Type from Department
                $department = null;
                if (!empty($linkedDeptIds)) {
                    $department = \Modules\PrSystem\Models\Department::whereIn('id', $linkedDeptIds)->first();
                }

                $budgetDeducted = false;

                // ========================================
                // ROUTE 1: PKS (STATION-BASED BUDGETING)
                // ========================================
                if ($department && $department->budget_type === \Modules\PrSystem\Enums\BudgetingType::STATION) {
                    // For PKS: COA comes from SubDepartment code
                    $subDeptQuery = SubDepartment::where('coa', $codeToCheck)
                        ->whereHas('department', function($q) use ($defaultWarehouse, $linkedDeptIds) {
                            $q->where('site_id', $defaultWarehouse->site_id);
                            if (!empty($linkedDeptIds)) {
                                $q->whereIn('id', $linkedDeptIds);
                            }
                        });

                    // Fallback for Traksi (COA starts with 1)
                    if (!$subDeptQuery->exists() && str_starts_with($coaFull, '1')) {
                        $subDeptQuery = SubDepartment::where('coa', $codeToCheck)
                            ->whereHas('department', function($q) use ($defaultWarehouse) {
                                $q->where('site_id', $defaultWarehouse->site_id);
                            });
                    }

                    $subDept = $subDeptQuery->first();

                    if ($subDept) {
                        $movement->update(['sub_department_id' => $subDept->id]);

                        // PKS Budget: ALWAYS FORCE 'Sparepart'
                        // Use firstOrCreate to ensure we target/create the correct category
                        $budget = Budget::firstOrCreate(
                            [
                                'sub_department_id' => $subDept->id,
                                'year' => $date->year,
                                'category' => 'Sparepart' // Force distinct category
                            ],
                            [
                                'department_id' => $subDept->department_id,
                                'amount' => 0,
                                'used_amount' => 0
                            ]
                        );

                        if ($budget) {
                            $cost = $price * $qty;
                            if ($cost > 0) {
                                $budget->increment('used_amount', $cost);
                            }
                            $budgetDeducted = true;
                            $budgetUpdated++;
                        }
                    }
                }
                
                // ========================================
                // ROUTE 2: JOB COA-BASED BUDGETING (Default)
                // ========================================
                if (!$budgetDeducted) {
                    // Priority 1: JOB Budget
                    // A. Strict Scope (Warehouse Linked Departments)
                    $jobQuery = Job::where('code', $codeToCheck)
                        ->where('site_id', $defaultWarehouse->site_id);
                    
                    if (!empty($linkedDeptIds)) {
                        // Special Logic for SAE or similar departments that use Global/Site-Wide Jobs
                        if ($department && $department->name === 'SAE') {
                             $jobQuery->where(function($q) use ($linkedDeptIds) {
                                 $q->whereIn('department_id', $linkedDeptIds)
                                   ->orWhereNull('department_id');
                             });
                        } else {
                             // Default Strict Logic
                             $jobQuery->whereIn('department_id', $linkedDeptIds);
                        }
                    }

                    $job = $jobQuery->first();

                    // B. Fallback for Traksi/RO (COA starts with 1 or 6) if not found in strict scope
                    if (!$job && (str_starts_with($codeToCheck, '1') || str_starts_with($codeToCheck, '6'))) {
                        $traksiRoDepts = \Modules\PrSystem\Models\Department::whereIn('name', ['Traksi', 'RO'])
                            ->where('site_id', $defaultWarehouse->site_id)
                            ->pluck('id')
                            ->toArray();
                        
                        if (!empty($traksiRoDepts)) {
                            $job = Job::where('code', $codeToCheck)
                                ->where('site_id', $defaultWarehouse->site_id)
                                ->whereIn('department_id', $traksiRoDepts)
                                ->first();
                        }
                    }

                    if ($job) {
                        $movement->update(['job_id' => $job->id]);

                        // Deduct from Job Budget (Create if not exists)
                        $budget = Budget::firstOrCreate(
                            [
                                'job_id' => $job->id,
                                'year' => $date->year
                            ],
                            [
                                'department_id' => $job->department_id,
                                'amount' => 0,
                                'used_amount' => 0,
                                'category' => 'Tahunan'
                            ]
                        );
                        
                        if ($budget) {
                            $cost = $price * $qty;
                            if ($cost > 0) {
                                $budget->increment('used_amount', $cost);
                                
                                // MIRRORING LOGIC: Only for code 5.3.1.04
                                if ($job->code === '5.3.1.04') {
                                    $sipilDept = \Modules\PrSystem\Models\Department::where('name', 'Sipil')
                                        ->where('site_id', $defaultWarehouse->site_id)
                                        ->first();
                                    
                                    if ($sipilDept) {
                                        // Find or Create Sipil's budget for this Job/COA
                                        $sipilBudget = Budget::firstOrCreate(
                                            [
                                                'department_id' => $sipilDept->id,
                                                'job_id' => $job->id, // FIXED: was job_coa_id
                                                'year' => $date->year
                                            ],
                                            [
                                                'amount' => 0,
                                                'used_amount' => 0,
                                                'category' => 'Tahunan'
                                            ]
                                        );
                                        
                                        if ($sipilBudget) {
                                            $sipilBudget->increment('used_amount', $cost);
                                            \Illuminate\Support\Facades\Log::info("Mirrored $cost to Sipil Budget (Job: {$job->code})");
                                        }
                                    }
                                }
                            }
                            $budgetDeducted = true;
                            $budgetUpdated++;
                        }
                    } 
                    
                    // Priority 2: STATION (SubDept) Budget if Job failed
                    if (!$budgetDeducted) {
                        // Try to find SubDepartment by COA
                        $subDeptQuery = SubDepartment::where('coa', $codeToCheck)
                            ->whereHas('department', function($q) use ($defaultWarehouse, $linkedDeptIds) {
                                $q->where('site_id', $defaultWarehouse->site_id);
                                // Scope to Warehouse Departments
                                if (!empty($linkedDeptIds)) {
                                    $q->whereIn('id', $linkedDeptIds);
                                }
                            });

                        $subDept = $subDeptQuery->first();

                        if ($subDept) {
                            $movement->update(['sub_department_id' => $subDept->id]);

                            // Find Budget for this SubDept (Create if not exists)
                            $budget = Budget::firstOrCreate(
                                [
                                    'sub_department_id' => $subDept->id,
                                    'year' => $date->year
                                ],
                                [
                                    'department_id' => $subDept->department_id,
                                    'amount' => 0,
                                    'used_amount' => 0,
                                    'category' => 'Tahunan'
                                ]
                            );

                            if ($budget) {
                                $cost = $price * $qty;
                                if ($cost > 0) {
                                    $budget->increment('used_amount', $cost);
                                }
                                $budgetDeducted = true;
                                $budgetUpdated++;
                            }
                        }
                    }
                }
            }
        }


    } elseif ($module === 'GOOD-RECEIVED') {
        $type = 'IN';
        
        // Qty IN (Col 11 - Index 10)
        $qtyRaw = trim($data[10] ?? '0');
        $qty = (float) str_replace(',', '', $qtyRaw);
        
        // Price IN (Col 12 - Index 11)
        $priceRaw = trim($data[11] ?? '0');
        $price = (float) str_replace(['Rp', ','], '', $priceRaw);

        if ($qty <= 0) {
            continue;
        }

        // STOCK LOGIC
        $stock = WarehouseStock::firstOrCreate(
            ['warehouse_id' => $defaultWarehouse->id, 'product_id' => $product->id],
            ['quantity' => 0]
        );
        $stock->increment('quantity', $qty);
        $stockUpdated++;

        // Update Price
        if ($price > 0) {
            $product->update(['price_estimation' => $price]);
        }

        // Create Stock Movement for IN
        StockMovement::create([
            'warehouse_id' => $defaultWarehouse->id,
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => 'IN',
            'quantity' => $qty,
            'date' => $date,
            'remarks' => $movementRemarks,
            'sub_department_id' => null,
            'job_id' => null,
            'price' => $price,
            'reference_number' => $data[1] ?? null,
        ]);

    } else {
        continue;
    }

    $processed++;
                    $processed++;
                }
                
                fclose($handle);
                DB::commit();

                \Modules\PrSystem\Helpers\ActivityLogger::log('imported', "Imported Stock Data. Processed: $processed. StockUpd: $stockUpdated. BudgetUpd: $budgetUpdated", $defaultWarehouse);

                return redirect()->route('inventory.index')->with('success', "Import processed successfully. Rows: $processed. Stock Updates: $stockUpdated. Budget Updates: $budgetUpdated. Check logs for details.");
                
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Error in processing row ' . $rowNumber . ': ' . $e->getMessage());
            }
        }
        
        return back()->with('error', 'Could not open file.');
    }

    public function importKdeInventory()
    {
        // 1. Open CSV
        $path = base_path('inventory_kde_final.csv');
        if (!file_exists($path)) {
            // Fallback for hosting potentially
            $path = public_path('inventory_kde_final.csv');
            if (!file_exists($path)) {
                 return "File not found: inventory_kde_final.csv (Checked base_path and public_path)";
            }
        }

        DB::beginTransaction();
        try {
            $handle = fopen($path, 'r');
            // Skip Header
            fgetcsv($handle, 1000, ',');

            $count = 0;
            $updated = 0;
            $created = 0;

            while (($data = fgetcsv($handle, 8192, ',')) !== FALSE) {
                if (count($data) < 5) continue;

                $warehouseName = trim($data[0] ?? '');
                $itemCode = trim($data[1] ?? '');
                $itemName = trim($data[2] ?? '');
                $unit = trim($data[3] ?? '');
                $qtyRaw = trim($data[4] ?? '0');
                // Remove thousands separator before casting: "362,131.00" → 362131.0
                $qty = (float) str_replace(',', '', $qtyRaw);
                $priceRaw = trim($data[5] ?? '0'); // "35,077,471" or "NaN" or "∞"
                $price = 0;
                $priceClean = str_replace([',', '"'], '', $priceRaw);
                
                if (is_numeric($priceClean)) {
                    $price = (float) $priceClean;
                }

                // 2. Resolve Warehouse
                $warehouse = Warehouse::where('name', $warehouseName)->first();
                if (!$warehouse) {
                     $site = \Modules\PrSystem\Models\Site::first();
                     $siteId = $site ? $site->id : 1;
                     $warehouse = Warehouse::create([
                         'name' => $warehouseName,
                         'site_id' => $siteId
                     ]);
                }

                // 3. Resolve Product
                $product = Product::where('code', $itemCode)->first();
                if (!$product) {
                    $product = Product::create([
                        'code' => $itemCode,
                        'name' => $itemName,
                        'unit' => $unit,
                        'price_estimation' => $price,
                        'min_stock' => 0,
                    ]);
                    $created++;
                } else {
                    if ($price > 0) {
                        $product->update(['price_estimation' => $price]);
                    }
                    $updated++;
                }

                // 4. Update Stock
                $stock = WarehouseStock::updateOrCreate(
                    ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
                    ['quantity' => $qty]
                );

                $count++;
            }

            fclose($handle);
            DB::commit();

            return "Import Success! Processed: $count rows. Created Products: $created. Updated Products: $updated.";

        } catch (\Exception $e) {
            DB::rollBack();
            return "Error: " . $e->getMessage() . " Line: " . $e->getLine();
        }
    }
}
