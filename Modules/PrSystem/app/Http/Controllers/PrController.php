<?php

namespace Modules\PrSystem\Http\Controllers;

use Modules\PrSystem\Models\PurchaseRequest;
use Modules\PrSystem\Models\Department;
use Modules\PrSystem\Models\Product;
use Modules\PrSystem\Services\PrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrController extends Controller
{
    protected $prService;

    public function __construct(PrService $prService)
    {
        $this->prService = $prService;
    }

    public function index(\Illuminate\Http\Request $request)
    {
        // --- Persistent Filter Logic ---
        $filterKeys = ['search', 'status', 'department_id', 'sub_department_id', 'start_date', 'end_date', 'current_approver_id', 'view_mode', 'sort'];
        
        if ($request->has('reset')) {
            // User clicked Reset
            session()->forget('pr_filters');
            return redirect()->route('pr.index');
        } elseif ($request->has('filter_active')) {
            // User submitted a filter form, save exactly what's submitted
            $currentFilters = $request->only($filterKeys);
            session(['pr_filters' => $currentFilters]);
        } else {
            // User just navigated here (e.g. from back button or link)
            if (session()->has('pr_filters')) {
                // Merge session filters into request so all subsequent logic uses them
                $request->merge(session('pr_filters'));
            }
        }

        $user = auth()->user();
        $query = PurchaseRequest::with(['department', 'subDepartment', 'items']);
        // Eager load relationships including approver
        $query->with(['department', 'subDepartment', 'items.job', 'approvals.approver']);

        // --- Core Visibility Logic ---
        // Global Access: Admin, Finance, Global Approver, OR Approver stationed at HO
        $isGlobal = $user->hasRole(['Admin', 'Finance']) 
                || ($user->hasRole('Approver') && $user->site && $user->site->code === 'HO')
                || \Modules\PrSystem\Models\GlobalApproverConfig::where('user_id', $user->id)->exists();

        // Prepare Departments for Filter
        if ($isGlobal) {
            $departments = Department::with('subDepartments')->orderBy('name')->get();
        } elseif ($user->hasRole(['Purchasing', 'Approver'])) {
            // Purchasing & Approvers: Departments in their SITE
            $departments = Department::with('subDepartments')->where('site_id', $user->site_id)->orderBy('name')->get();
        } elseif ($user->department_id) {
             $departments = Department::with('subDepartments')->where('id', $user->department_id)->get();
        } else {
             $departments = collect();
        }

        $viewMode = $request->get('view_mode', 'pr');

        // Helper closure for visibility filter
        $applyVisibility = function($q) use ($isGlobal, $user) {
            if ($isGlobal) {
                return; // No filter
            }
            if ($user->hasRole(['Purchasing', 'Approver'])) {
                // Purchasing & Approvers: See All Departments in their SITE
                $q->whereHas('department', function($d) use ($user) {
                    $d->where('site_id', $user->site_id);
                });
            } else {
                 // Staff: Own PRs
                 $q->where('user_id', $user->id);
            }
        };

        if ($viewMode === 'items') {
            $query = \Modules\PrSystem\Models\PrItem::with(['purchaseRequest', 'product', 'purchaseRequest.department', 'purchaseRequest.subDepartment'])
                 ->whereHas('purchaseRequest', function($q) use ($applyVisibility) {
                     $applyVisibility($q);
                 });

            // ... (Search filters remain same) ...
            // Search Filter
             if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('item_name', 'like', "%{$search}%")
                      ->orWhereHas('purchaseRequest', function($sq) use ($search) {
                           $sq->where('pr_number', 'like', "%{$search}%");
                      });
                });
            }
            
            // ... (Copying existing logical blocks for filters)
            if ($request->filled('status')) {
                $status = $request->status;
                $query->whereHas('purchaseRequest', function($q) use ($status) {
                    if ($status === \Modules\PrSystem\Enums\PrStatus::PENDING->value) {
                        $q->whereIn('status', [\Modules\PrSystem\Enums\PrStatus::PENDING->value, \Modules\PrSystem\Enums\PrStatus::ON_HOLD->value]);
                    } elseif ($status === 'Waiting PO') {
                        // Includes both purely Waiting PO and Partial PO (not fully complete)
                        $q->whereIn('status', ['Approved', 'PO Created'])
                          ->whereHas('items', function ($iq) {
                              $iq->whereDoesntHave('poItems');
                          });
                    } elseif ($status === 'Complete PO') {
                        $q->whereIn('status', ['Approved', 'PO Created'])
                          ->whereDoesntHave('items', function ($iq) {
                              $iq->whereDoesntHave('poItems');
                          });
                    } else {
                        $q->where('status', $status);
                    }
                });
            }

            if ($request->filled('department_id')) {
                 $query->whereHas('purchaseRequest', function($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }

            if ($request->filled('sub_department_id')) {
                 $query->whereHas('purchaseRequest', function($q) use ($request) {
                    $q->where('sub_department_id', $request->sub_department_id);
                });
            }
            
            if ($request->filled('start_date')) {
                 $query->whereHas('purchaseRequest', function($q) use ($request) {
                    $q->whereDate('request_date', '>=', $request->start_date);
                });
            }

            if ($request->filled('end_date')) {
                 $query->whereHas('purchaseRequest', function($q) use ($request) {
                    $q->whereDate('request_date', '<=', $request->end_date);
                });
            }

            // Apply Sort
            $sort = $request->get('sort', 'terbaru');
            if ($sort === 'expired') {
                // Must ensure we only sort those that are approved or PO created, others to the bottom
                // Since this is PR Item view, we join with purchase_requests to sort efficiently
                $query->join('purchase_requests', 'pr_items.purchase_request_id', '=', 'purchase_requests.id')
                      ->select('pr_items.*'); // Keep selecting only pr_items

                // Subquery to get the latest Approved date from pr_approvals
                $approvedAtSubquery = \DB::table('pr_approvals')
                    ->select('approved_at')
                    ->whereColumn('purchase_request_id', 'purchase_requests.id')
                    ->where('status', 'Approved')
                    ->orderByDesc('level')
                    ->limit(1);

                $query->orderByRaw("
                    CASE 
                        WHEN purchase_requests.status IN ('Approved', 'PO Created') THEN 0 
                        ELSE 1 
                    END ASC
                ")->orderByRaw("COALESCE((" . $approvedAtSubquery->toSql() . "), '9999-12-31 23:59:59') ASC")
                ->addBinding($approvedAtSubquery->getBindings(), 'order');

            } elseif ($sort === 'terlama') {
                $query->orderBy('pr_items.created_at', 'asc');
            } else {
                $query->orderBy('pr_items.created_at', 'desc');
            }

            $items = $query->paginate(20);
            $prs = null;
        } else {
            // -- PR VIEW --
            $items = null;
            
            // Apply Visibility
            $applyVisibility($query);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('pr_number', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                if ($request->status === \Modules\PrSystem\Enums\PrStatus::PENDING->value) {
                    $query->whereIn('status', [\Modules\PrSystem\Enums\PrStatus::PENDING->value, \Modules\PrSystem\Enums\PrStatus::ON_HOLD->value]);
                } elseif ($request->status === 'Waiting PO') {
                    // Includes both purely Waiting PO and Partial PO
                    $query->whereIn('status', ['Approved', 'PO Created'])
                          ->whereHas('items', function ($iq) {
                              $iq->whereDoesntHave('poItems');
                          });
                } elseif ($request->status === 'Complete PO') {
                    // ALL items must have a PO
                    $query->whereIn('status', ['Approved', 'PO Created'])
                          ->whereHas('items') // Ensure it has items
                          ->whereDoesntHave('items', function ($iq) {
                              $iq->whereDoesntHave('poItems');
                          });
                } else {
                    $query->where('status', $request->status);
                }
            }

            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->filled('sub_department_id')) {
                $query->where('sub_department_id', $request->sub_department_id);
            }

            if ($request->filled('start_date')) {
                $query->whereDate('request_date', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('request_date', '<=', $request->end_date);
            }

            if ($request->filled('current_approver_id')) {
                $approverId = $request->current_approver_id;
                $query->where(function($q) use ($approverId) {
                    $q->whereIn('status', ['Pending', 'On Hold'])
                      ->whereHas('approvals', function($aq) use ($approverId) {
                          $aq->where('approver_id', $approverId)
                             ->whereIn('status', ['Pending', 'On Hold']);
                      })
                      ->whereDoesntHave('approvals', function($aq) use ($approverId) {
                          $aq->whereIn('status', ['Pending', 'On Hold'])
                             ->whereRaw('level < (
                                 SELECT level FROM pr_approvals 
                                 WHERE purchase_request_id = purchase_requests.id 
                                 AND approver_id = ? 
                                 AND status IN ("Pending", "On Hold")
                                 LIMIT 1
                             )', [$approverId]);
                      });
                });
            }
            
            // Apply Sort
            $sort = $request->get('sort', 'terbaru');
            if ($sort === 'expired') {
                // Subquery to get the latest Approved date from pr_approvals
                $approvedAtSubquery = \DB::table('pr_approvals')
                    ->select('approved_at')
                    ->whereColumn('purchase_request_id', 'purchase_requests.id')
                    ->where('status', 'Approved')
                    ->orderByDesc('level')
                    ->limit(1);

                $query->orderByRaw("
                    CASE 
                        WHEN purchase_requests.status IN ('Approved', 'PO Created') THEN 0 
                        ELSE 1 
                    END ASC
                ")->orderByRaw("COALESCE((" . $approvedAtSubquery->toSql() . "), '9999-12-31 23:59:59') ASC")
                ->addBinding($approvedAtSubquery->getBindings(), 'order');

            } elseif ($sort === 'terlama') {
                $query->orderBy('purchase_requests.created_at', 'asc');
            } else {
                $query->orderBy('purchase_requests.created_at', 'desc');
            }

            $prs = $query->paginate(10);
        }
        
        $approvers = collect();
        if ($viewMode === 'pr') {
            $cacheKey = 'pr_current_approvers_' . $user->id . '_' . ($isGlobal ? 'global' : $user->site_id);
            
            $approvers = \Cache::remember($cacheKey, 300, function() use ($user, $isGlobal) {
                
                $query = \DB::table('purchase_requests as pr')
                    ->join('pr_approvals as pa', 'pr.id', '=', 'pa.purchase_request_id')
                    ->join('users as u', 'pa.approver_id', '=', 'u.id')
                    ->whereIn('pr.status', ['Pending', 'On Hold'])
                    ->whereIn('pa.status', ['Pending', 'On Hold'])
                    ->select('u.id', 'u.name', 'pa.level', 'pr.id as pr_id', 'pa.role_name');
                
                if (!$isGlobal) {
                    if ($user->hasRole(['Purchasing', 'Approver'])) {
                        $query->join('departments as d', 'pr.department_id', '=', 'd.id')
                              ->where('d.site_id', $user->site_id);
                    } else {
                        $query->where('pr.user_id', $user->id);
                    }
                }
                
                $pendingApprovals = $query->get();
                
                // Group by PR to find the actual current approver (lowest pending level)
                $currentApprovers = [];
                $prGroups = $pendingApprovals->groupBy('pr_id');
                
                foreach ($prGroups as $prId => $approvals) {
                    // Get the approval with the lowest level (first in workflow)
                    $lowestLevel = $approvals->min('level');
                    $currentApproval = $approvals->where('level', $lowestLevel)->first();
                    
                    if ($currentApproval && !isset($currentApprovers[$currentApproval->id])) {
                        $roleName = $currentApproval->role_name ?? 'Approver';
                        $currentApprovers[$currentApproval->id] = [
                            'id' => $currentApproval->id,
                            'name' => $roleName . ' (' . $currentApproval->name . ')'
                        ];
                    }
                }
                
                // Convert to collection and sort
                return collect($currentApprovers)
                    ->sortBy('name')
                    ->values()
                    ->map(fn($item) => (object)$item);
            });
        }

        return view('prsystem::pr.index', compact('prs', 'items', 'departments', 'viewMode', 'approvers'));
    }

    public function export(Request $request) 
    {
        $user = auth()->user();
        $viewMode = $request->get('view_mode', 'pr');
        $fileName = 'rekap_pr_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($viewMode, $request, $user) {
            $file = fopen('php://output', 'w');
            
            // Re-apply Visibility Logic
            $isGlobal = $user->hasRole(['Admin', 'Finance']) 
                    || ($user->hasRole('Approver') && $user->site && $user->site->code === 'HO')
                    || \Modules\PrSystem\Models\GlobalApproverConfig::where('user_id', $user->id)->exists();

            $applyVisibility = function($q) use ($isGlobal, $user) {
                if ($isGlobal) return;
                if ($user->hasRole(['Purchasing', 'Approver'])) {
                    $q->whereHas('department', function($d) use ($user) {
                        $d->where('site_id', $user->site_id);
                    });
                } else {
                    $q->where('user_id', $user->id);
                }
            };

            if ($viewMode === 'items') {
                // ITEM EXPORT
                fputcsv($file, [
                    'No. PR', 
                    'Tanggal Request', 
                    'Requester', 
                    'Unit', 
                    'Sub Unit', 
                    'Status PR',
                    'Kode Barang', 
                    'Nama Barang', 
                    'Spesifikasi', 
                    'Kategori',
                    'Qty', 
                    'Satuan', 
                    'Harga Satuan (Est)', 
                    'Total Harga (Est)', 
                    'Job', 
                    'No. PO', 
                    'Link URL', 
                    'Keterangan'
                ]);

                $itemQuery = \Modules\PrSystem\Models\PrItem::with(['purchaseRequest.department', 'purchaseRequest.subDepartment', 'purchaseRequest.user', 'product', 'job', 'poItems.purchaseOrder'])
                     ->whereHas('purchaseRequest', function($q) use ($applyVisibility) {
                         $applyVisibility($q);
                     });

                // Apply Filters
                if ($request->filled('search')) {
                    $search = $request->search;
                    $itemQuery->where(function($q) use ($search) {
                        $q->where('item_name', 'like', "%{$search}%")
                          ->orWhereHas('purchaseRequest', function($sq) use ($search) {
                               $sq->where('pr_number', 'like', "%{$search}%");
                          });
                    });
                }
                
                if ($request->filled('status')) {
                    $status = $request->status;
                    $itemQuery->whereHas('purchaseRequest', function($q) use ($status) {
                        if ($status === \Modules\PrSystem\Enums\PrStatus::PENDING->value) {
                            $q->whereIn('status', [\Modules\PrSystem\Enums\PrStatus::PENDING->value, \Modules\PrSystem\Enums\PrStatus::ON_HOLD->value]);
                        } elseif ($status === 'Waiting PO') {
                            $q->whereIn('status', ['Approved', 'PO Created'])
                              ->whereHas('items', function ($iq) {
                                  $iq->whereDoesntHave('poItems');
                              });
                        } elseif ($status === 'Complete PO') {
                            $q->whereIn('status', ['Approved', 'PO Created'])
                              ->whereHas('items')
                              ->whereDoesntHave('items', function ($iq) {
                                  $iq->whereDoesntHave('poItems');
                              });
                        } else {
                            $q->where('status', $status);
                        }
                    });
                }

                if ($request->filled('department_id')) {
                     $itemQuery->whereHas('purchaseRequest', function($q) use ($request) {
                        $q->where('department_id', $request->department_id);
                    });
                }

                if ($request->filled('sub_department_id')) {
                     $itemQuery->whereHas('purchaseRequest', function($q) use ($request) {
                        $q->where('sub_department_id', $request->sub_department_id);
                    });
                }
                
                if ($request->filled('start_date')) {
                     $itemQuery->whereHas('purchaseRequest', function($q) use ($request) {
                        $q->whereDate('request_date', '>=', $request->start_date);
                    });
                }

                if ($request->filled('end_date')) {
                     $itemQuery->whereHas('purchaseRequest', function($q) use ($request) {
                        $q->whereDate('request_date', '<=', $request->end_date);
                    });
                }

                $itemQuery->orderBy('id')->chunk(500, function($items) use ($file) {
                    foreach ($items as $item) {
                        $pr = $item->purchaseRequest;
                        
                        // Get PO Number (Using helper from PrItem or manual Check)
                        $poNumber = '-';
                        if ($item->poItems->isNotEmpty()) {
                             $poNumbers = $item->poItems->map(fn($pi) => $pi->purchaseOrder->po_number ?? '-')->unique()->implode(', ');
                             $poNumber = $poNumbers ?: '-';
                        }

                        // Determine Category
                        $category = '-';
                        if ($item->product) {
                            $category = $item->product->category ?? '-';
                        } elseif ($item->manual_category) {
                            $category = $item->manual_category;
                        }

                        // NORMALIZE (User Request: Merge All to Sparepart for consistency with Budget)
                        if (in_array($category, ['Material', 'material', 'Consumable', 'consumable', 'Bahan Pembantu'])) {
                            $category = 'Sparepart';
                        }

                        fputcsv($file, [
                            $pr ? $pr->pr_number : '-',
                            $pr ? $pr->request_date->format('d/m/Y') : '-',
                            $pr && $pr->user ? $pr->user->name : '-',
                            $pr && $pr->department ? $pr->department->name : '-',
                            $pr && $pr->subDepartment ? $pr->subDepartment->name : '-',
                            $pr ? $pr->status : '-',
                            $item->product ? $item->product->code : ($item->manual_category ? 'Manual' : '-'),
                            $item->item_name,
                            $item->specification ?? '-',
                            $category,
                            $item->quantity,
                            $item->unit,
                            $item->price_estimation,
                            $item->subtotal,
                            $item->job ? $item->job->code . ' - ' . $item->job->name : '-',
                            $poNumber,
                            $item->url_link ?? '-',
                            $item->remarks
                        ]);
                    }
                });

            } else {
                fputcsv($file, [
                    'No. PR', 
                    'Tanggal Request', 
                    'Requester', 
                    'Unit', 
                    'Sub Unit', 
                    'Job',
                    'Status', 
                    'Posisi Approval',
                    'Jumlah Item', 
                    'Total Estimasi', 
                    'Keterangan'
                ]);
                
                $query = PurchaseRequest::with(['department', 'subDepartment', 'items.job', 'user', 'approvals']);
                $applyVisibility($query);

                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('pr_number', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                }

                if ($request->filled('status')) {
                    if ($request->status === \Modules\PrSystem\Enums\PrStatus::PENDING->value) {
                        $query->whereIn('status', [\Modules\PrSystem\Enums\PrStatus::PENDING->value, \Modules\PrSystem\Enums\PrStatus::ON_HOLD->value]);
                    } else {
                        $query->where('status', $request->status);
                    }
                }

                if ($request->filled('department_id')) {
                    $query->where('department_id', $request->department_id);
                }

                if ($request->filled('sub_department_id')) {
                    $query->where('sub_department_id', $request->sub_department_id);
                }

                if ($request->filled('start_date')) {
                    $query->whereDate('request_date', '>=', $request->start_date);
                }

                if ($request->filled('end_date')) {
                    $query->whereDate('request_date', '<=', $request->end_date);
                }

                $query->orderBy('created_at', 'desc')->chunk(500, function($prs) use ($file) {
                    foreach ($prs as $pr) {
                        // Determine Approval Position
                        $approvalPos = '-';
                        if ($pr->status === \Modules\PrSystem\Enums\PrStatus::PENDING->value || $pr->status === \Modules\PrSystem\Enums\PrStatus::ON_HOLD->value) {
                             $nextApproval = $pr->approvals->whereIn('status', ['Pending', 'On Hold'])->sortBy('level')->first();
                             if ($nextApproval) {
                                  // Can show Role or User if assigned
                                  $approvalPos = $nextApproval->role; 
                                  if ($nextApproval->user_id && $nextApproval->approver) {
                                      $approvalPos .= " (" . $nextApproval->approver->name . ")";
                                  }
                             }
                        } elseif ($pr->status === \Modules\PrSystem\Enums\PrStatus::APPROVED->value) {
                            $approvalPos = 'Completed';
                        }

                        // Collect Jobs
                        $jobs = $pr->items->map(function($item) {
                            return $item->job ? ($item->job->code . ' - ' . $item->job->name) : null;
                        })->filter()->unique()->implode(', ');

                        fputcsv($file, [
                            $pr->pr_number,
                            $pr->request_date->format('d/m/Y'),
                            $pr->user->name ?? '-',
                            $pr->department->name ?? '-',
                            $pr->subDepartment->name ?? '-',
                            $jobs ?: '-',
                            $pr->status,
                            $approvalPos,
                            $pr->items->count(),
                            $pr->final_total, 
                            $pr->description
                        ]);
                    }
                });
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        // Filter departments: Admin sees all, Staff sees only their own
        if (auth()->user()->hasRole('Admin')) {
            $departments = Department::with(['site', 'subDepartments'])->orderBy('name')->get();
        } else {
            // Check if user has a department assigned
            $userDeptId = auth()->user()->department_id;
            if ($userDeptId) {
                // Fetch only the user's department with its sub-departments
                $departments = Department::with(['site', 'subDepartments'])
                                ->where('id', $userDeptId)
                                ->get();
            } else {
                // Fallback if user has no department (shouldn't happen for staff theoretically)
                $departments = collect(); 
            }
        }
        
        $year = date('Y');

        if ($departments->isNotEmpty()) {
            $departments->load(['subDepartments' => function($q) use ($year) {
                 $q->whereHas('budgets', function($b) use ($year) {
                     $b->where('year', $year)
                       ->where('amount', '>', 0);
                 });
            }]);
        }

        // Eager load products with their sites and warehouse stocks for the frontend
        $siteIds = $departments->pluck('site_id')->unique()->filter()->toArray();
        $products = collect(); 

        if (!empty($siteIds)) {
            $products = \Modules\PrSystem\Models\Product::with(['sites', 'stocks'])->whereHas('sites', function($q) use ($siteIds) {
                $q->whereIn('sites.id', $siteIds);
            })->whereNotNull('category')->orderBy('name')->get();
        }

        $categories = config('options.product_categories');
        
        return view('prsystem::pr.create', compact('departments', 'products', 'categories'));
    }

    public function getProductsBySite($siteId)
    {
        $products = \Modules\PrSystem\Models\Product::whereHas('sites', function($query) use ($siteId) {
            $query->where('sites.id', $siteId);
        })
        ->whereNotNull('category')
        ->orderBy('name')
        ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'sub_department_id' => 'nullable|exists:sub_departments,id', 
            'request_date' => 'required|date',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'nullable', 
                function ($attribute, $value, $fail) {
                    if ($value === 'manual') {
                        $fail('Input barang baru di tolak, mohon cari apakah barang benar benar tidak ada. apabila tidak ada silahkan request ke tim IT secara manual');
                        return;
                    }
                    if (!empty($value) && !\Modules\PrSystem\Models\Product::where('id', $value)->exists()) {
                         $fail('Selected product is invalid.');
                    }
                }
            ],
            // New Validation for Job
            'items.*.job_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $dept = Department::find($request->department_id);
                    if ($dept && $dept->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA) {
                        if (empty($value)) {
                            $fail('Job / Pekerjaan harus dipilih untuk unit ini.');
                        } elseif (!\Modules\PrSystem\Models\Job::where('id', $value)->exists()) {
                            $fail('Selected job is invalid.');
                        }
                    }
                }
            ],
            'items.*.item_name' => 'required|string', 
            'items.*.specification' => 'nullable|string',
            'items.*.remarks' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string',
            'items.*.price_estimation' => 'required|numeric|min:0',
            'items.*.manual_category' => 'nullable|string',
            'items.*.url_link' => 'nullable|string|url', 
        ]);
        
        // Custom Validation for Manual Category (Only for Station Budget Type)
        $dept = Department::find($request->department_id);
        if ($dept->budget_type === \Modules\PrSystem\Enums\BudgetingType::STATION) {
            foreach ($request->items as $index => $item) {
                 $pid = $item['product_id'] ?? null;
                 if (($pid === 'manual' || empty($pid)) && empty($item['manual_category'])) {
                     return back()->withErrors(["items.{$index}.manual_category" => "Category is required for manual items."])->withInput();
                 }
            }
        }

        // Process items
        $items = collect($request->items)->map(function($item) {
             if (isset($item['product_id']) && $item['product_id'] === 'manual') {
                 $item['product_id'] = null;
             } elseif (!empty($item['product_id'])) {
                 // Enforce Catalog Price if Product ID exists
                 $product = \Modules\PrSystem\Models\Product::find($item['product_id']);
                 if ($product) {
                     $item['price_estimation'] = $product->price_estimation;
                 }
             }
             return $item;
        })->toArray();

        // Budget Checking Logic
        $year = date('Y', strtotime($request->request_date));
        $subDeptId = $request->sub_department_id;
        $warnings = [];

        if ($dept->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA) {
            // Group by Job (Since Job is now the budget unit)
            $itemsByJob = [];
            foreach ($items as $item) {
                if (empty($item['job_id'])) continue;
                $job = \Modules\PrSystem\Models\Job::find($item['job_id']);
                if (!$job) continue;
                
                $jobId = $job->id;
                // Combine Code and Name for Label
                $label = ($job->code ?? '') . ' - ' . $job->name; 

                if (!isset($itemsByJob[$jobId])) {
                     $itemsByJob[$jobId] = ['amount' => 0, 'name' => $label];
                }
                $itemsByJob[$jobId]['amount'] += ($item['price_estimation'] * $item['quantity']);
            }

            foreach ($itemsByJob as $jobId => $data) {
                $amountNeeded = $data['amount'];
                
                $budgetQuery = \Modules\PrSystem\Models\Budget::where('job_id', $jobId)
                            ->where('year', $year);
                
                if ($subDeptId) {
                    $budgetQuery->where('sub_department_id', $subDeptId);
                }
                            
                $budget = $budgetQuery->first();

                if (!$budget) {
                     // If subDept is skipped, we might not want to error strictly if budget doesn't exist?
                     // Or we error saying "No budget for Job X".
                     return back()->withInput()->withErrors(['budget' => "No budget configured for Job '{$data['name']}' (Year: {$year})."]);
                }

                // Check Usage (Consumption-based)
                $usedAmount = $budget->used_amount;
                $limit = $budget->amount; 

                if (($usedAmount + $amountNeeded) > $budget->amount) {
                    $remaining = $budget->amount - $usedAmount;
                    $warnings[] = "Budget Exceeded for Job '{$data['name']}'. Limit: ".number_format($budget->amount).". Used: ".number_format($usedAmount).". Request: ".number_format($amountNeeded).". Remaining: ".number_format($remaining);
                }
            }

        } else {
            // STATION Budget Type (Existing Logic)
            $itemsByCategory = [];
            foreach ($items as $item) {
                $cat = 'Uncategorized';
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if ($product && $product->category) {
                        $cat = $product->category;
                    }
                } elseif (!empty($item['manual_category'])) {
                    $cat = $item['manual_category'];
                } else {
                    $cat = 'Lain-lain'; 
                }

                // NORMALIZE CATEGORIES (User Request: Merge All to Sparepart)
                // Material, Consumable, Bahan Pembantu -> Sparepart
                if (in_array($cat, ['Material', 'material', 'Consumable', 'consumable', 'Bahan Pembantu', 'bahan pembantu','Lain-lain', 'lain-lain',''])) {
                    $cat = 'Sparepart';
                }

                if (!isset($itemsByCategory[$cat])) {
                    $itemsByCategory[$cat] = 0;
                }
                $itemsByCategory[$cat] += ($item['price_estimation'] * $item['quantity']);
            }

            foreach ($itemsByCategory as $category => $amountNeeded) {
                // Clean category string
                $category = trim($category);
                $budgetCategory = $category; // Already normalized
                
                $budget = \Modules\PrSystem\Models\Budget::where('sub_department_id', $subDeptId)
                            ->where('category', $budgetCategory)
                            ->where('year', $year)
                            ->where(function($query) {
                                $query->whereNull('job_id')
                                      ->orWhere('job_id', 0);
                            })
                            ->first();
                
                if (!$budget) {
                    if ($category !== 'Uncategorized') {
                         return back()->withInput()->withErrors(['budget' => "No budget configured for category '{$category}' in this Sub Department (Year: {$year}). Please check Master Budget."]);
                    }
                    continue; 
                }

                $usedAmount = $budget->used_amount;
                $limit = $budget->amount;

                if ($limit > 0 && ($usedAmount + $amountNeeded) > $limit) {
                    $remaining = $limit - $usedAmount;
                    $warnings[] = "Budget Exceeded for '{$category}'. Limit: ".number_format($limit).". Used: ".number_format($usedAmount).". Request: ".number_format($amountNeeded).". Remaining: ".number_format($remaining);
                } elseif ($limit <= 0) {
                    $warnings[] = "Category '{$category}' has 0 budget allocated.";
                }
            }
        }
        
        if (!empty($warnings)) {
             return back()->withInput()->withErrors(['budget' => implode('<br>', $warnings)]);
        }

        $prData = $request->only('department_id', 'sub_department_id', 'request_date');
        $prData['description'] = $request->description ?? '-';

        
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $extension = $file->getClientOriginalExtension();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            
            $year = date('Y', strtotime($request->request_date));
            $month = date('m', strtotime($request->request_date));
            
            $tempPrNumber = 'TEMP_' . uniqid();
            $fileName = $tempPrNumber . '_' . \Illuminate\Support\Str::slug($originalName) . '.' . $extension;
            
            $folderPath = "attachments/pr/{$year}/{$month}";
            $attachmentPath = $file->storeAs($folderPath, $fileName, 'public');
            $prData['attachment_path'] = $attachmentPath;
        }

        $pr = $this->prService->createPr(
            $prData,
            $items
        );
        
        if ($attachmentPath && $pr->pr_number) {
            $oldPath = storage_path('app/public/' . $attachmentPath);
            $year = date('Y', strtotime($request->request_date));
            $month = date('m', strtotime($request->request_date));
            $extension = pathinfo($attachmentPath, PATHINFO_EXTENSION);
            $originalName = pathinfo($request->file('attachment')->getClientOriginalName(), PATHINFO_FILENAME);
            
            // Sanitize PR Number for filename (replace / with _)
            $safePrNumber = str_replace(['/', '\\'], '_', $pr->pr_number);
            
            $newFileName = $safePrNumber . '_' . \Illuminate\Support\Str::slug($originalName) . '.' . $extension;
            $newPath = "attachments/pr/{$year}/{$month}/{$newFileName}";
            $newFullPath = storage_path('app/public/' . $newPath);
            
            $newDir = dirname($newFullPath);
            if (!file_exists($newDir)) {
                mkdir($newDir, 0755, true);
            }
            
            if (file_exists($oldPath)) {
                rename($oldPath, $newFullPath);
                $pr->attachment_path = $newPath;
                $pr->save();
            }
        }

        \Modules\PrSystem\Helpers\ActivityLogger::log('created', 'Created Purchase Request: ' . $pr->pr_number, $pr);



        return redirect()->route('pr.index')->with('success', 'PR Submitted successfully.');
    }

    public function show(PurchaseRequest $pr)
    {
        $pr->load('items.product', 'approvals.approver', 'department.site', 'items.job'); 
        
        // Calculate budget status for this PR
        $year = $pr->request_date->format('Y');
        $subDeptId = $pr->sub_department_id;
        $budgetWarnings = [];

        if ($pr->department->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA) {
             // Logic for Job Budget Warning in Show View
             $itemsByJob = [];
             foreach ($pr->items as $item) {
                 if ($item->job) {
                     $jobId = $item->job_id;
                     $key = ($item->job->code ?? '') . ' - ' . $item->job->name;
                     if (!isset($itemsByJob[$jobId])) $itemsByJob[$jobId] = ['amount'=>0, 'name'=>$key];
                     // Use getFinalQuantity for approved PRs
                     $qty = ($pr->status === \Modules\PrSystem\Enums\PrStatus::APPROVED->value) 
                            ? $item->getFinalQuantity() 
                            : $item->quantity;
                     $itemsByJob[$jobId]['amount'] += $qty * $item->price_estimation;
                 }
             }

             foreach ($itemsByJob as $jobId => $data) {
                // Find Budget
                $budget = \Modules\PrSystem\Models\Budget::where('sub_department_id', $subDeptId)
                            ->where('job_id', $jobId)
                            ->where('year', $year)
                            ->first();

                 $limit = $budget ? $budget->amount : 0;

                 // Calculate Usage (Consumption)
                 $otherUsed = $budget ? $budget->used_amount : 0;

                 $totalProjected = $otherUsed + $data['amount'];
                if ($totalProjected > $limit) {
                    $budgetWarnings[] = "Budget <strong>{$data['name']}</strong> akan melebihi limit! (Limit: ".number_format($limit).", Terpakai+Request: ".number_format($totalProjected).")";
                }
             }

        } else {
            // Existing Station Logic
            $itemsByCategory = [];
            foreach ($pr->items as $item) {
                $cat = 'Uncategorized';
                if ($item->product && $item->product->category) {
                    $cat = $item->product->category;
                } elseif ($item->manual_category) {
                    $cat = $item->manual_category;
                } else {
                     $cat = 'Lain-lain';
                }

                // NORMALIZE
                if (in_array($cat, ['Material', 'material', 'Consumable', 'consumable', 'Bahan Pembantu'])) {
                    $cat = 'Sparepart';
                }

                if (!isset($itemsByCategory[$cat])) $itemsByCategory[$cat] = 0;
                // Use getFinalQuantity for approved PRs
                $qty = ($pr->status === \Modules\PrSystem\Enums\PrStatus::APPROVED->value) 
                       ? $item->getFinalQuantity() 
                       : $item->quantity;
                $itemsByCategory[$cat] += $qty * $item->price_estimation;
            }
    
            foreach ($itemsByCategory as $cat => $amount) {
                $budget = \Modules\PrSystem\Models\Budget::where('sub_department_id', $subDeptId)
                            ->where('category', $cat)
                            ->where('year', $year)
                            ->first();
                
                $limit = $budget ? $budget->amount : 0;
                
                $otherUsed = $budget ? $budget->used_amount : 0;
                                
                $totalProjected = $otherUsed + $amount;
                
                if ($totalProjected > $limit) {
                    $budgetWarnings[] = "Budget <strong>{$cat}</strong> akan melebihi limit! (Limit: ".number_format($limit).", Terpakai+Request: ".number_format($totalProjected).")";
                }
            }
        }

        // --- Budget Info Logic (Mirrors PrPdfController) ---
        $budgetInfo = [
            'total' => 0,
            'actual' => 0,
            'current' => 0,
            'saldo' => 0
        ];

        $isJobCoa = $pr->department->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA;
        
        if ($isJobCoa) {
             // Logic for Job Based PR (Single Job per PR constraint assumed)
            $firstItem = $pr->items->first();
            $jobId = $firstItem ? $firstItem->job_id : null;
            
            if ($jobId) {
                $budget = \Modules\PrSystem\Models\Budget::where('sub_department_id', $subDeptId)
                            ->where('job_id', $jobId)
                            ->where('year', $year)
                            ->first();
                
                $budgetInfo['total'] = $budget ? $budget->amount : 0;
                $budgetInfo['actual'] = $budget ? $budget->used_amount : 0;
            }
        } else {
             // Logic for Station Based PR (Focused on Sparepart Only)
            $cat = 'Sparepart'; // Hardcoded as per user request
            
            $budget = \Modules\PrSystem\Models\Budget::where('sub_department_id', $subDeptId)
                        ->where('category', $cat)
                        ->where('year', $year)
                        ->first();

            $limit = $budget ? $budget->amount : 0;
            $budgetInfo['total'] += $limit;
            
            $otherUsed = $budget ? $budget->used_amount : 0;
            $budgetInfo['actual'] += $otherUsed;
        }

        // Calculate current request based on displayed items
        // Use getFinalQuantity() for approved PRs to account for HO adjustments
        $budgetInfo['current'] = $pr->items->sum(function($item) use ($pr) {
             $qty = ($pr->status === \Modules\PrSystem\Enums\PrStatus::APPROVED->value) 
                    ? $item->getFinalQuantity() 
                    : $item->quantity;
             return $qty * $item->price_estimation; 
        });

        // RE-CALCULATE Current for correct 'Saldo' projection akin to PDF
        $budgetInfo['saldo'] = $budgetInfo['total'] - ($budgetInfo['actual'] + $budgetInfo['current']);

        // --- Fetch Stock for Items logic (RESTORED) ---
        // Check if Department has a linked Warehouse
        $warehouseId = $pr->department->warehouse_id;
        
        foreach ($pr->items as $item) {
            $item->current_stock = 0; // Default
            
            if ($warehouseId && $item->product_id) {
                $stock = \Modules\PrSystem\Models\WarehouseStock::where('warehouse_id', $warehouseId)
                            ->where('product_id', $item->product_id)
                            ->value('quantity');
                
                $item->current_stock = $stock ?? 0;
            }
        }

        return view('prsystem::pr.show', compact('pr', 'budgetWarnings', 'budgetInfo'));
    }

    public function getBudgetStatus($subDepartmentId)
    {
        $year = date('Y'); // Current year
        $budgets = \Modules\PrSystem\Models\Budget::where('sub_department_id', $subDepartmentId)
                    ->where('year', $year)
                    ->get();
        
        // We need to know the Dept Budget Type, but here we just have subDeptId.
        $subDept = \Modules\PrSystem\Models\SubDepartment::find($subDepartmentId);
        if (!$subDept) return response()->json([]);
        $isJobCoa = $subDept->department->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA;

        $status = [];
        
        if ($isJobCoa) {
             foreach ($budgets as $budget) {
                 if (!$budget->job) continue;
                 $key = $budget->job_id;
                 $label = ($budget->job->code ?? '') . ' - ' . $budget->job->name;
                 // Usage
                 // Usage (Consumption)
                 $usedAmount = $budget->used_amount;
                 
                 $status[$label] = [ // Use Label as Key specifically for text display if needed, or ID
                    'limit' => $budget->amount,
                    'used' => $usedAmount,
                    'remaining' => $budget->amount - $usedAmount
                 ];
             }
        } else {
            $categories = config('options.product_categories');
            $budgetsByKey = $budgets->keyBy('category');
            
            foreach ($categories as $cat) {
                $budgetAmount = $budgetsByKey[$cat]->amount ?? 0;
                $usedAmount = $budgetsByKey[$cat]->used_amount ?? 0;
    
                $status[$cat] = [
                    'limit' => $budgetAmount,
                    'used' => $usedAmount,
                    'remaining' => $budgetAmount - $usedAmount
                ];
            }
        }

        return response()->json($status);
    }
    
    public function getJobs($subDepartmentId)
    {
        $year = date('Y');

        $jobsByDirectBudget = DB::table('jobs')
            ->join('budgets', 'jobs.id', '=', 'budgets.job_id')
            ->where('budgets.sub_department_id', $subDepartmentId)
            ->where('budgets.year', $year)
            ->where('budgets.amount', '>', 0)
            ->select('jobs.id', 'jobs.code', 'jobs.name');
    
        $jobsByCoaBudget = DB::table('jobs')
            ->join('budgets', 'jobs.job_coa_id', '=', 'budgets.coa_id')
            ->where('budgets.sub_department_id', $subDepartmentId)
            ->where('budgets.year', $year)
            ->where('budgets.amount', '>', 0)
            ->select('jobs.id', 'jobs.code', 'jobs.name');
    
        $jobs = $jobsByDirectBudget
            ->union($jobsByCoaBudget)
            ->orderBy('code')
            ->get();
    
        return response()->json($jobs);
    }
    
    public function getJobsByDepartment(\Modules\PrSystem\Models\Department $department)
    {
        $year = date('Y');

        // Fetch Job IDs that have an active budget for this department (either directly or via sub-departments)
        $budgetJobIds = \Modules\PrSystem\Models\Budget::query()
            ->where('year', $year)
            ->where('amount', '>', 0)
            ->where(function($q) use ($department) {
                $q->where('department_id', $department->id)
                  ->orWhereHas('subDepartment', function($sq) use ($department) {
                      $sq->where('department_id', $department->id);
                  });
            })
            ->pluck('job_id')
            ->unique();
        
        $jobs = \Modules\PrSystem\Models\Job::whereIn('id', $budgetJobIds)
                    ->orderBy('code')
                    ->get();
                    
        return response()->json($jobs);
    }

    private function getJobsBySite($siteId)
    {
        $jobs = \Modules\PrSystem\Models\Job::where('site_id', $siteId)
                ->orderBy('code')
                ->get()
                ->map(function($job) {
                    return [
                        'id' => $job->id,
                        'name' => $job->name,
                        'code' => $job->code,
                        'label' => ($job->code ? $job->code . ' - ' : '') . $job->name
                    ];
                });
                
        return response()->json($jobs);
    }


    
    public function downloadAttachment(PurchaseRequest $purchaseRequest)
    {
        if (!$purchaseRequest->attachment_path) {
            return back()->with('error', 'File attachment tidak ditemukan.');
        }
        
        $filePath = storage_path('app/public/' . $purchaseRequest->attachment_path);
        
        if (!file_exists($filePath)) {
            return back()->with('error', 'File tidak ditemukan di server. Path: ' . $purchaseRequest->attachment_path);
        }
        
        $extension = pathinfo($purchaseRequest->attachment_path, PATHINFO_EXTENSION);
        $safePrNumber = str_replace(['/', '\\'], '_', $purchaseRequest->pr_number);
        $downloadName = $safePrNumber . '_attachment.' . $extension;
        
        return response()->download($filePath, $downloadName, [
            'Content-Type' => mime_content_type($filePath),
            'Content-Disposition' => 'attachment; filename="' . $downloadName . '"'
        ]);
    }

    public function replyToHold(Request $request, PurchaseRequest $pr)
    {
        $user = auth()->user();

        if ($pr->user_id != $user->id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk membalas PR ini.');
        }

        if ($pr->status !== 'On Hold') {
            return back()->with('error', 'PR tidak dalam status On Hold.');
        }

        $request->validate([
            'hold_reply' => 'required|string|max:1000'
        ]);

        $holdApproval = $pr->approvals()
            ->where('status', 'On Hold')
            ->orderBy('level', 'desc')
            ->first();

        if (!$holdApproval) {
            return back()->with('error', 'Approval On Hold tidak ditemukan.');
        }

        $holdApproval->update([
            'hold_reply' => $request->hold_reply,
            'replied_at' => now()
        ]);

        \Cache::forget('pr_current_approvers_*');

        return back()->with('success', 'Balasan berhasil dikirim. Approver akan mereview kembali PR Anda.');
    }

    public function fullApprove(Request $request, PurchaseRequest $pr, \Modules\PrSystem\Services\ApprovalService $approvalService)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $password = $request->input('admin_password');
        if ($password !== config('prsystem.app.admin_verification_password', config('app.admin_verification_password'))) {
            return back()->with('error', 'Password verifikasi salah!');
        }

        if ($pr->status === \Modules\PrSystem\Enums\PrStatus::APPROVED->value || $pr->status === \Modules\PrSystem\Enums\PrStatus::REJECTED->value) {
             return back()->with('error', 'PR is already finalized.');
        }

        try {
            $approvalService->fullApprove($pr, auth()->id());
            
            \Modules\PrSystem\Helpers\ActivityLogger::log('full-approved', 'Super Admin Full Approved PR: ' . $pr->pr_number, $pr);
            
            return redirect()->back()->with('success', 'PR has been fully approved by Super Admin.');
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error('Full Approve Error: ' . $e->getMessage());
             return back()->with('error', 'Failed to approve PR: ' . $e->getMessage());
        }
    }
}
