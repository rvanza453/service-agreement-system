<?php

namespace Modules\PrSystem\Http\Controllers\Admin;

use Modules\PrSystem\Http\Controllers\Controller;
use Modules\PrSystem\Models\Budget;
use Modules\PrSystem\Models\SubDepartment;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $site_id = $request->site_id;
        $department_id = $request->department_id;
        $year = date('Y');

        if ($department_id) {
            $department = \Modules\PrSystem\Models\Department::with('site')->findOrFail($department_id);
            
            // If checking a specific department
            if ($department->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA) {
                return redirect()->route('admin.budgets.edit-department', $department);
            }

            $subDepartments = SubDepartment::where('department_id', $department_id)
                ->with(['budgets' => function($q) use ($year) {
                    $q->where('year', $year)->whereNotNull('category');
                }])
                ->get();
            return view('prsystem::admin.budget.index', compact('subDepartments', 'department', 'year'));
        }

        if ($site_id) {
            $site = \Modules\PrSystem\Models\Site::findOrFail($site_id);
            $departments = \Modules\PrSystem\Models\Department::where('site_id', $site_id)
                ->with(['budgets' => function($q) use ($year) {
                    $q->where('year', $year);
                }, 'subDepartments.budgets' => function($q) use ($year) {
                    $q->where('year', $year);
                }])
                ->get();
            
            // Calculate total budget per department
            $departments->each(function($dept) use ($year) {
                if ($dept->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA) {
                    $dept->total_budget = $dept->budgets->sum('amount');
                } else {
                    $dept->total_budget = $dept->subDepartments->flatMap->budgets->sum('amount');
                }
            });

            return view('prsystem::admin.budget.index', compact('departments', 'site', 'year'));
        }

        $sites = \Modules\PrSystem\Models\Site::with(['departments', 'departments.budgets' => function($q) use ($year) {
                $q->where('year', $year);
            }, 'departments.subDepartments.budgets' => function($q) use ($year) {
                $q->where('year', $year);
            }])
            ->get();

        $sites->each(function($site) {
            $site->total_budget = $site->departments->sum(function($dept) {
                if ($dept->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA) {
                    return $dept->budgets->sum('amount');
                } else {
                    return $dept->subDepartments->flatMap->budgets->sum('amount');
                }
            });
            $site->dept_count = $site->departments->count();
        });

        return view('prsystem::admin.budget.index', compact('sites', 'year'));
    }

    public function edit(SubDepartment $subDepartment)
    {
        $subDepartment->load(['budgets', 'department']);
        $isJobCoa = $subDepartment->department->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA;
        
        // Block editing if it should be department level now
        if ($isJobCoa) {
             return redirect()->route('admin.budgets.edit-department', $subDepartment->department_id);
        }

        $year = date('Y');
        
        // Clean up incompatible budgets
        Budget::where('sub_department_id', $subDepartment->id)
            ->where('year', $year)
            ->whereNotNull('job_id')
            ->delete();
        
        // Reload budgets
        $subDepartment->load('budgets');
        
        $categories = config('options.product_categories');
        $existingBudgets = $subDepartment->budgets->pluck('amount', 'category')->toArray();
        $jobs = [];

        return view('prsystem::admin.budget.edit', compact('subDepartment', 'categories', 'existingBudgets', 'isJobCoa', 'jobs'));
    }

    public function update(Request $request, SubDepartment $subDepartment)
    {
        $request->validate([
            'budgets' => 'array',
            'budgets.*' => 'nullable|numeric|min:0',
        ]);

        $year = date('Y');
        $subDepartment->load('department');
        
        // Key is Category Name
        foreach ($request->budgets as $category => $amount) {
            $budget = Budget::firstOrNew([
                'sub_department_id' => $subDepartment->id,
                'category' => $category,
                'year' => $year
            ]);
            
            $budget->amount = $amount ?? 0;
            $budget->job_id = null;
            $budget->department_id = null;
            $budget->save();
        }

        return back()
            ->with('success', 'Budget updated successfully.');
    }

    public function editDepartment(\Modules\PrSystem\Models\Department $department)
    {
        if ($department->budget_type !== \Modules\PrSystem\Enums\BudgetingType::JOB_COA) {
            return redirect()->back()->with('error', 'This department does not use Unit/Job budgeting.');
        }

        $department->load(['budgets']);
        $year = date('Y');

        // Fetch Jobs: Global (Dept JS NULL) OR Specific to this Dept
        $jobs = \Modules\PrSystem\Models\Job::where('site_id', $department->site_id)
            ->where(function($q) use ($department) {
                $q->whereNull('department_id')
                  ->orWhere('department_id', $department->id);
            })
            ->orderBy('code')
            ->get();

        $department->load(['budgets' => function($q) use ($year) {
            $q->where('year', $year);
        }]);

        $existingBudgets = $department->budgets->pluck('amount', 'job_id')->toArray();
        $isJobCoa = true;

        return view('prsystem::admin.budget.edit', compact('department', 'existingBudgets', 'isJobCoa', 'jobs'));
    }

    public function updateDepartment(Request $request, \Modules\PrSystem\Models\Department $department)
    {
        $request->validate([
            'budgets' => 'array',
            'budgets.*' => 'nullable|numeric|min:0',
        ]);

        $year = date('Y');

        // Key is Job ID
        foreach ($request->budgets as $jobId => $amount) {
            $budget = Budget::firstOrNew([
                'department_id' => $department->id,
                'job_id' => $jobId,
                'year' => $year
            ]);
            
            $budget->amount = $amount ?? 0;
            $budget->category = null;
            $budget->sub_department_id = null;
            $budget->save();
        }

        return back()
            ->with('success', 'Department budget updated successfully.');
    }

    public function monitoring(Request $request)
    {
        $year = $request->input('year', date('Y'));
        
        // Access Control Logic
        $user = auth()->user();
        $isHO = $user->hasRole('Admin') || \Modules\PrSystem\Models\GlobalApproverConfig::where('user_id', $user->id)->exists();
        
        if (!$isHO && $user->site_id) {
            // Unit Approver: Restricted to their Site
            $request->merge(['site_id' => $user->site_id]);
            $site_id = $user->site_id;
            $sites = \Modules\PrSystem\Models\Site::where('id', $site_id)->get();
        } else {
            // HO/Admin: Can see all
            $site_id = $request->input('site_id');
            $sites = \Modules\PrSystem\Models\Site::all();
        }

        $department_id = $request->input('department_id');

        $query = Budget::with(['subDepartment', 'job', 'department', 'subDepartment.department.site', 'department.site'])
            ->where('year', $year)
            ->where(function ($q) {
                // Show if Budget exists OR if it has been Used (even if budget is 0)
                $q->where('amount', '>', 0)
                  ->orWhere('used_amount', '>', 0);
            });

        if ($site_id) {
            $query->where(function($q) use ($site_id) {
                $q->whereHas('department', function($subQ) use ($site_id) {
                    $subQ->where('site_id', $site_id);
                })->orWhereHas('subDepartment.department', function($subQ) use ($site_id) {
                    $subQ->where('site_id', $site_id);
                });
            });
        }

        if ($department_id) {
            $query->where(function($q) use ($department_id) {
                $q->where('department_id', $department_id)
                  ->orWhereHas('subDepartment', function($subQ) use ($department_id) {
                      $subQ->where('department_id', $department_id);
                  });
            });
        }

        $budgets = $query->orderBy('updated_at', 'desc')->get(); // Get all for now, or paginate

        // Prepare Chart Data
        $chartData = [
            'labels' => [],
            'budget' => [],
            'used' => [],
            'level' => 'site', // site, department, entity
            'ids' => [] // For drilldown linking
        ];

        if (!$site_id) {
            // Level 1: Per Site
            $chartData['level'] = 'site';
            $grouped = $budgets->groupBy(function($b) {
                return $b->department->site->name ?? $b->subDepartment->department->site->name ?? 'Unknown';
            });
            
            foreach ($grouped as $name => $items) {
                // Get ID for drilldown
                $first = $items->first();
                $id = $first->department->site->id ?? $first->subDepartment->department->site->id ?? null;
                
                if ($id) {
                    $chartData['labels'][] = $name;
                    $chartData['budget'][] = $items->sum('amount');
                    $chartData['used'][] = $items->sum('used_amount');
                    $chartData['ids'][] = $id;
                }
            }
        } elseif (!$department_id) {
            // Level 2: Per Department (in specific Site)
            $chartData['level'] = 'department';
            $grouped = $budgets->groupBy(function($b) {
                return $b->department->name ?? $b->subDepartment->department->name ?? 'Unknown';
            });

            foreach ($grouped as $name => $items) {
                $first = $items->first();
                $id = $first->department_id ?? $first->subDepartment->department_id ?? null;

                if ($id) {
                    $chartData['labels'][] = $name;
                    $chartData['budget'][] = $items->sum('amount');
                    $chartData['used'][] = $items->sum('used_amount');
                    $chartData['ids'][] = $id;
                }
            }
        } else {
            // Level 3: Per Entity (Job/Station) in specific Dept
            $chartData['level'] = 'entity';
            foreach ($budgets as $b) {
                $name = '-';
                if ($b->job) {
                    // Job: COA - Name
                    $name = $b->job->code . ' - ' . $b->job->name;
                } elseif ($b->subDepartment) {
                    // Station: COA (SubDept Code) - Name
                    $name = $b->subDepartment->coa . ' - ' . $b->subDepartment->name;
                }
                
                $chartData['labels'][] = $name;
                $chartData['budget'][] = $b->amount;
                $chartData['used'][] = $b->used_amount;
                $chartData['ids'][] = null; // No drilldown
            }
        }

        $departments = \Modules\PrSystem\Models\Department::all();

        return view('prsystem::admin.budget.monitoring', compact('budgets', 'sites', 'departments', 'year', 'site_id', 'department_id', 'chartData'));
    }

    public function usageDetails(Budget $budget)
    {
        // Find StockMovements related to this budget
        $movements = collect();

        if ($budget->job_id) {
            $movements = \Modules\PrSystem\Models\StockMovement::where('job_id', $budget->job_id)
                ->whereYear('date', $budget->year)
                ->with(['product', 'warehouse'])
                ->orderBy('date', 'desc')
                ->get();
        } elseif ($budget->sub_department_id) {
            // For Station budgets, we match SubDept ID
            // User requested to treat all items as 'Sparepart' / show all usage for the station
            
            $movements = \Modules\PrSystem\Models\StockMovement::where('sub_department_id', $budget->sub_department_id)
                ->whereYear('date', $budget->year)
                ->with(['product', 'warehouse'])
                ->orderBy('date', 'desc')
                ->get();
        }

        return response()->json([
            'html' => view('prsystem::admin.budget.components.usage_detail_modal', compact('movements', 'budget'))->render()
        ]);
    }
}
