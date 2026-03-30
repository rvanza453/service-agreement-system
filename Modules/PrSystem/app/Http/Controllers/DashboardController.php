<?php

namespace Modules\PrSystem\Http\Controllers;

use Illuminate\Http\Request;
use Modules\PrSystem\Models\PurchaseRequest;
use Modules\PrSystem\Enums\PrStatus;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isHO = false;
        
        // Check if HO User (by Site Code 'HO' or GlobalApproverConfig)
        if ($user->site && $user->site->code === 'HO') {
            $isHO = true;
        } elseif (\Modules\PrSystem\Models\GlobalApproverConfig::where('user_id', $user->id)->exists()) {
            $isHO = true;
        } elseif ($user->hasRole('admin')) {
            $isHO = true;
        }

        $isApprover = $user->hasRole('Approver');
        $isPurchasing = $user->hasRole('Purchasing');

        // 1. Stats Query
        $statsQuery = PurchaseRequest::query();

        if (!$isHO) {
            if ($isApprover || $isPurchasing) {
                // Approver & Purchasing: Show all PRs from their site
                $statsQuery->whereHas('user', function($q) use ($user) {
                    $q->where('site_id', $user->site_id);
                });
            } else {
                // Regular User: Show only their own PRs
                $statsQuery->where('user_id', $user->id);
            }
        }
        // HO sees all, no filter needed

        // Calculate pending approvals for current user (sequential logic)
        $pendingApprovalCount = 0;
        if ($isApprover || $isHO) {
            $query = \Modules\PrSystem\Models\PrApproval::whereIn('status', ['Pending', 'On Hold'])
                ->with(['purchaseRequest.approvals']);
            
            if (!$isHO) {
                $query->where('approver_id', $user->id);
            }
            
            $approvals = $query->get();
            
            $pendingApprovalCount = $approvals->filter(function ($approval) {
                if (!$approval->purchaseRequest) return false;

                $allPreviousApproved = $approval->purchaseRequest->approvals
                    ->filter(function ($other) use ($approval) {
                        return $other->level < $approval->level;
                    })
                    ->every(function ($other) {
                        return $other->status === \Modules\PrSystem\Enums\PrStatus::APPROVED->value;
                    });

                return $allPreviousApproved && $approval->status === \Modules\PrSystem\Enums\PrStatus::PENDING->value;
            })->count();
        }

        // Waiting PO: Approved PRs that do NOT have any Purchase Orders yet (checking via items)
        $waitingPoCount = (clone $statsQuery)->where('status', PrStatus::APPROVED->value)
            ->whereDoesntHave('items.poItems')
            ->count();

        // PO Completed: POs that are status 'Completed'
        $poCompletedQuery = \Modules\PrSystem\Models\PurchaseOrder::where('status', 'Completed');
        
        if (!$isHO) {
            if ($isApprover) {
                 $poCompletedQuery->whereHas('items.prItem.purchaseRequest.department', function($q) use ($user) {
                    $q->where('site_id', $user->site_id);
                 });
            } else {
                $poCompletedQuery->whereHas('items.prItem.purchaseRequest', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }
        }
        $poCompletedCount = $poCompletedQuery->count();

        $stats = [
            'pending_approval' => $pendingApprovalCount,
            'waiting_po' => $waitingPoCount,
            'rejected' => (clone $statsQuery)->where('status', PrStatus::REJECTED->value)->count(),
            'po_completed' => $poCompletedCount,
        ];
            
        // 2. Budget Chart (Budget used per department)
        $chartQuery = PurchaseRequest::join('departments', 'purchase_requests.department_id', '=', 'departments.id')
            ->select('departments.name', DB::raw('SUM(total_estimated_cost) as total'))
            ->groupBy('departments.name');

        if (!$isHO) {
            // Both Approver and Regular User see charts only for their site's departments
            $chartQuery->where('departments.site_id', $user->site_id);
        }

        $budgetChart = $chartQuery->get();
            
        // 3. Budget Summary Calculation
        $currentYear = date('Y');
        $deptQuery = \Modules\PrSystem\Models\Department::with(['subDepartments.budgets' => function($q) use ($currentYear, $isHO) {
             $q->where('year', $currentYear);
        }, 'budgets' => function($q) use ($currentYear) {
             $q->where('year', $currentYear);
        }]);

        if (!$isHO) {
            // Filter departments by user's site
            $deptQuery->where('site_id', $user->site_id);
        }

        $departments = $deptQuery->get();

        $departmentBudgets = $departments->map(function ($dept) use ($currentYear, $isHO) {
            
            if ($dept->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA) {
                $validBudgets = $dept->budgets->filter(function($b) {
                    return !is_null($b->job_id);
                });
            } else {
                $validBudgets = $dept->subDepartments->flatMap(function($sub) use ($dept) {
                    return $sub->budgets->filter(function($b) use ($dept) {
                         return !is_null($b->category) || !is_null($b->job_id);
                    });
                });
            }

            // Calculate Allocated
            $allocated = $validBudgets->sum('amount');

            // Calculate Used Budget 
            $used = $validBudgets->sum('used_amount');

            $remaining = $allocated - $used;

            $dept->calculated_budget = $allocated;
            $dept->used_budget = $used;
            $dept->remaining_budget = $remaining;
            
            return $dept;
        });

        return view('prsystem::dashboard', compact('stats', 'budgetChart', 'departmentBudgets'));
    }
}
