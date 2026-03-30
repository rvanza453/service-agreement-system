<?php

namespace Modules\PrSystem\Http\Controllers;

use Illuminate\Http\Request;

class CapexController extends Controller
{
    private function userHasPrRole(string|array $roles): bool
    {
        $user = auth()->user();

        return $user->hasModuleRole('pr', $roles) || $user->hasRole($roles);
    }

    private function isPrAdmin(): bool
    {
        return $this->userHasPrRole('Admin');
    }

    public function index()
    {
        $query = \Modules\PrSystem\Models\CapexRequest::with(['user', 'department', 'capexBudget', 'approvals']);
        
        // Filter: Admin sees all, User sees own
        if (!$this->userHasPrRole(['Admin', 'Approver'])) {
            $query->where('user_id', auth()->id());
        }
        
        // TODO: Approver Logic? Currently ApprovalController handles inbox. 
        // This list is mainly for "My Requests" or "All Requests (Admin)"
        
        $capexRequests = $query->latest()->paginate(10);
        
        return view('prsystem::capex.index', compact('capexRequests'));
    }

    public function create()
    {
        $isAdmin = $this->isPrAdmin();

        if ($isAdmin) {
            // Admin: can create for any department
            $departments = \Modules\PrSystem\Models\Department::all();
            $budgets = \Modules\PrSystem\Models\CapexBudget::with(['department', 'capexAsset'])
                        ->where('fiscal_year', date('Y'))
                        ->where('is_active', true)
                        ->where('remaining_amount', '>', 0)
                        ->get();
            $userDept = null;
        } else {
            // Regular user: locked to own department
            $userDept = auth()->user()->department;
            $departments = null;

            if (!$userDept) {
                return redirect()->back()->with('error', 'Anda tidak memiliki department. Hubungi Admin.');
            }

            $budgets = \Modules\PrSystem\Models\CapexBudget::with(['department', 'capexAsset'])
                        ->where('department_id', $userDept->id)
                        ->where('fiscal_year', date('Y'))
                        ->where('is_active', true)
                        ->where('remaining_amount', '>', 0)
                        ->get();
        }

        return view('prsystem::capex.create', compact('userDept', 'budgets', 'departments', 'isAdmin'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'capex_budget_id' => 'required|exists:capex_budgets,id',
            'quantity'        => 'required|integer|min:1',
            'price'           => 'required|numeric|min:0',
            'type'            => 'required|string',
            'code_budget_ditanam' => 'boolean',
            'description'     => 'required|string',
            'supporting_document' => 'nullable|file|mimes:pdf|max:10240',
            'questionnaire'   => 'required|array|size:6',
            'questionnaire.*' => 'required|string',
        ]);

        $supportingDocumentPath = null;
        if ($request->hasFile('supporting_document')) {
            $supportingDocumentPath = $request->file('supporting_document')->store('capex_supporting_documents', 'public');
        }

        $isAdmin = $this->isPrAdmin();

        if ($isAdmin) {
            // Admin can submit for any department; department_id comes from form
            $request->validate(['department_id' => 'required|exists:departments,id']);
            $effectiveDeptId = (int) $request->department_id;
        } else {
            // Non-admin: must be locked to their own department
            $userDept = auth()->user()->department;
            if (!$userDept) {
                return back()->with('error', 'Anda tidak memiliki department. Hubungi Admin.');
            }
            $effectiveDeptId = $userDept->id;
        }

        $budget = \Modules\PrSystem\Models\CapexBudget::findOrFail($validated['capex_budget_id']);

        // Strict check: Budget must belong to the effective department (cast to int to prevent type mismatch)
        if ((int) $budget->department_id !== (int) $effectiveDeptId) {
            return back()->with('error', 'Budget yang dipilih bukan milik department yang dipilih. Pengajuan ditolak.');
        }

        if (!$budget->is_active || $budget->fiscal_year != date('Y')) {
            return back()->with('error', 'Budget yang dipilih sudah tidak aktif atau bukan untuk tahun ini.');
        }

        $totalAmount = $validated['quantity'] * $validated['price'];
        $isBudgeted  = (bool) $budget->is_budgeted;

        if ($isBudgeted) {
            if ($budget->remaining_amount < $totalAmount) {
                $limitText = number_format($budget->amount + $budget->pta_amount, 0);
                $ptaText = $budget->pta_amount > 0 ? ' (termasuk PTA Rp ' . number_format($budget->pta_amount, 0) . ')' : '';
                return back()->with('error', 'Jumlah total permintaan (Rp ' . number_format($totalAmount, 0) . ') melebihi sisa anggaran yang tersedia (Rp ' . number_format($budget->remaining_amount, 0) . ') dari total limit Rp ' . $limitText . $ptaText);
            }
            if (isset($budget->remaining_quantity) && $budget->remaining_quantity < $validated['quantity']) {
                return back()->with('error', 'Jumlah unit (' . $validated['quantity'] . ') melebihi sisa jumlah anggaran (' . $budget->remaining_quantity . ')');
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($validated, $budget, $totalAmount, $effectiveDeptId, $isBudgeted, $supportingDocumentPath) {
            $capex = \Modules\PrSystem\Models\CapexRequest::create([
                'user_id'             => auth()->id(),
                'department_id'       => $effectiveDeptId,
                'capex_budget_id'     => $validated['capex_budget_id'],
                'quantity'            => $validated['quantity'],
                'price'               => $validated['price'],
                'amount'              => $totalAmount,
                'type'                => $validated['type'],
                'code_budget_ditanam' => $isBudgeted, 
                'description'         => $validated['description'],
                'supporting_document_path' => $supportingDocumentPath,
                'questionnaire_answers' => $validated['questionnaire'],
                'status'              => 'Pending',
                'current_step'        => 1
            ]);

            // Only deduct budget if the budget is marked as budgeted
            if ($isBudgeted) {
                $budget->decrement('remaining_amount', $totalAmount);
                if (isset($budget->remaining_quantity)) {
                    $budget->decrement('remaining_quantity', $validated['quantity']);
                }
            }

            // Initiate First Approval Step
            $this->initiateApprovalStep($capex);
        });

        return redirect()->route('pr.dashboard')->with('success', 'Capex Request berhasil diajukan!');
    }

    public function show(\Modules\PrSystem\Models\CapexRequest $capex)
    {
        $capex->load(['capexBudget.capexAsset', 'approvals.approver', 'user', 'department']);
        
        // Find current config for the current step
        $currentConfig = \Modules\PrSystem\Models\CapexColumnConfig::where('department_id', $capex->department_id)
                            ->where('column_index', $capex->current_step)
                            ->first();

        // Check if user can approve
        $user = auth()->user();
        $canApprove = false;
        
        if (in_array($capex->status, ['Pending', 'On Hold'])) {
            $currentStep = $capex->current_step;
            
            // Admin can always approve any pending step
            if ($this->isPrAdmin()) {
                $canApprove = true;
                
                // Ensure approval record exists for this step (create on-the-fly if missing)
                $approval = $capex->approvals->where('column_index', $currentStep)->first();
                if (!$approval) {
                    \Modules\PrSystem\Models\CapexApproval::create([
                        'capex_request_id' => $capex->id,
                        'column_index'     => $currentStep,
                        'approver_id'      => null,
                        'status'           => 'Pending',
                    ]);
                    // Reload to get fresh state
                    $capex->load('approvals.approver');
                }
            } else {
                $approval = $capex->approvals->where('column_index', $currentStep)->first();
                
                if ($approval && in_array($approval->status, ['Pending', 'On Hold'])) {
                    // Check config for this step
                    $config = \Modules\PrSystem\Models\CapexColumnConfig::where('department_id', $capex->department_id)
                                ->where('column_index', $currentStep)
                                ->first();
                    
                    if ($config) {
                        if ($config->approver_user_id) {
                            // Specific User Assigned -> Strict Check
                            if ($config->approver_user_id == $user->id) {
                                $canApprove = true;
                            }
                        }
                    }
                }
            }
        }
        // Check if Admin can mark wet signature
        $canMarkSigned = false;
        if (in_array($capex->status, ['Pending', 'On Hold']) && $currentConfig && !$currentConfig->is_digital) {
            if ($this->isPrAdmin() || auth()->user()->can('mark capex signed')) {
                $canMarkSigned = true;
            }
        }

        // Get all configs for this department to show in timeline
    $departmentConfigs = \Modules\PrSystem\Models\CapexColumnConfig::where('department_id', $capex->department_id)
                        ->with('approver')
                        ->orderBy('column_index')
                        ->get()
                        ->keyBy('column_index');

    return view('prsystem::capex.show', compact('capex', 'currentConfig', 'canApprove', 'canMarkSigned', 'departmentConfigs'));
    }
    
    private function initiateApprovalStep(\Modules\PrSystem\Models\CapexRequest $capex)
    {
        $nextStep = $capex->current_step;

        // If past step 5, auto-approve the Capex
        if ($nextStep > 5) {
            $capex->update(['status' => 'Approved']);
            return;
        }

        // Check if approval for this step already exists
        $existing = $capex->approvals()->where('column_index', $nextStep)->first();
        if ($existing) {
            return; // Already exists, don't duplicate
        }

        // Find config for this step
        $config = \Modules\PrSystem\Models\CapexColumnConfig::where('department_id', $capex->department_id)
                    ->where('column_index', $nextStep)
                    ->first();

        $approverId = null;
        if ($config) {
            if ($config->approver_user_id) {
                $approverId = $config->approver_user_id;
            }
        }

        // Create next approval step record
        \Modules\PrSystem\Models\CapexApproval::create([
            'capex_request_id' => $capex->id,
            'column_index'     => $nextStep,
            'approver_id'      => $approverId,
            'status'           => 'Pending'
        ]);
    }
    
    public function approve(\Illuminate\Http\Request $request, \Modules\PrSystem\Models\CapexRequest $capex)
    {
         // Logic to approve current step and move to next
         $approval = $capex->approvals()->where('column_index', $capex->current_step)->whereIn('status', ['Pending', 'On Hold'])->first();
         
         if (!$approval) {
             // Redundancy check: maybe already approved?
             return back()->with('error', 'This step is already processed or not pending.');
         }
         
         // STRICT CHECK: Ensure user is authorized for *this specific step*
         // Admin override is allowed, otherwise strict config check
            if (!$this->isPrAdmin()) {
            $config = \Modules\PrSystem\Models\CapexColumnConfig::where('department_id', $capex->department_id)
                        ->where('column_index', $capex->current_step)
                        ->first();
            
            $isAuthorized = false;
            if ($config) {
                if ($config->approver_user_id) {
                    // Specific User Assigned -> Strict Check
                    if ($config->approver_user_id == auth()->id()) {
                        $isAuthorized = true;
                    }
                }
            }
            
            if (!$isAuthorized) {
                return back()->with('error', 'You are not authorized to approve this step.');
            }
         }

         // Determine who is recorded as the approver
         $recordedApproverId = auth()->id();
         if ($this->isPrAdmin()) {
             // If Admin, try to record the ACTUAL assigned user so PDF shows correct name
             $stepConfig = \Modules\PrSystem\Models\CapexColumnConfig::where('department_id', $capex->department_id)
                            ->where('column_index', $capex->current_step)
                            ->first();
             if ($stepConfig && $stepConfig->approver_user_id) {
                 $recordedApproverId = $stepConfig->approver_user_id;
             }
         }

         $approval->update([
             'status' => 'Approved',
             'approver_id' => $recordedApproverId,
             'remarks' => $request->remarks,
             'signed_at' => now()
         ]);
         
         // Reset capex status to Pending (clears any On Hold state from this step)
         $capex->update(['status' => 'Pending']);

         $capex->increment('current_step');
         $this->initiateApprovalStep($capex);
         
         return back()->with('success', 'Step Approved');
    }

    public function reject(\Illuminate\Http\Request $request, \Modules\PrSystem\Models\CapexRequest $capex)
    {
        $request->validate(['remarks' => 'required|string']);

        if (!$this->isPrAdmin()) {
            $config = \Modules\PrSystem\Models\CapexColumnConfig::where('department_id', $capex->department_id)
                ->where('column_index', $capex->current_step)
                ->first();

            if (!$config || (int) $config->approver_user_id !== (int) auth()->id()) {
                return back()->with('error', 'You are not authorized to reject this step.');
            }
        }

        $approval = $capex->approvals()->where('column_index', $capex->current_step)->whereIn('status', ['Pending', 'On Hold'])->first();
         
        if (!$approval) {
             return back()->with('error', 'This step is not pending.');
        }

        // Update Approval Record
        $approval->update([
             'status' => 'Rejected',
             'approver_id' => auth()->id(),
             'remarks' => $request->remarks,
             'signed_at' => now()
        ]);

        // Update Capex Status (STOP PROCESS)
        $capex->update(['status' => 'Rejected']);

        // Refund Budget if it was budgeted
        if ($capex->code_budget_ditanam && $capex->capexBudget) {
            $capex->capexBudget->increment('remaining_amount', $capex->amount);
            if (isset($capex->capexBudget->remaining_quantity)) {
                $capex->capexBudget->increment('remaining_quantity', $capex->quantity);
            }
        }

        return back()->with('success', 'Capex Request Rejected and Budget has been refunded.');
    }

    public function hold(\Illuminate\Http\Request $request, \Modules\PrSystem\Models\CapexRequest $capex)
    {
        $request->validate(['remarks' => 'required|string']);

        if (!$this->isPrAdmin()) {
            $config = \Modules\PrSystem\Models\CapexColumnConfig::where('department_id', $capex->department_id)
                ->where('column_index', $capex->current_step)
                ->first();

            if (!$config || (int) $config->approver_user_id !== (int) auth()->id()) {
                return back()->with('error', 'You are not authorized to hold this step.');
            }
        }

        $approval = $capex->approvals()->where('column_index', $capex->current_step)->whereIn('status', ['Pending', 'On Hold'])->first();
         
        if (!$approval) {
             return back()->with('error', 'This step is not pending.');
        }

        // Update Approval Record
        $approval->update([
             'status' => 'On Hold',
             'approver_id' => auth()->id(), // Mark who held it
             'remarks' => $request->remarks,
             'signed_at' => now()
        ]);

        // Update Capex Status
        $capex->update(['status' => 'On Hold']);

        return back()->with('success', 'Capex Request placed On Hold.');
    }
    
    public function markSigned(\Illuminate\Http\Request $request, \Modules\PrSystem\Models\CapexRequest $capex)
    {
        // Admin manually marking a wet signature step as done
        $approval = $capex->approvals()->where('column_index', $capex->current_step)->where('status', 'Pending')->firstOrFail();
         
        $approval->update([
             'status' => 'Approved',
             'approver_id' => auth()->id(), // Admin who verified
             'remarks' => 'Manual Verification of Wet Signature: ' . $request->remarks,
             'signed_at' => now()
        ]);
         
        $capex->increment('current_step');
        $this->initiateApprovalStep($capex);
         
        return back()->with('success', 'Wet Signature Verified');
    }
    public function print(\Modules\PrSystem\Models\CapexRequest $capex)
    {
        if ($capex->current_step < 6 && $capex->status !== 'Approved') {
        }
        
        // Eager load relationships to prevent N+1 queries during PDF generation
        $capex->load(['user', 'department', 'capexBudget.capexAsset']);
        
        $approvals = $capex->approvals()->with('approver')->orderBy('column_index')->get();
        $fileName = 'Capex-' . str_replace('/', '-', $capex->capex_number) . '.pdf';

        // Calculate capex sebelumnya in controller to avoid class resolution issues in view
        $capexSebelumnya = 0;
        if ($capex->code_budget_ditanam && $capex->capexBudget) {
            $capexSebelumnya = \Modules\PrSystem\Models\CapexRequest::where('capex_budget_id', $capex->capex_budget_id)
                            ->where('id', '<', $capex->id)
                            ->where('status', '!=', 'Rejected')
                            ->sum('amount');
        }

        // Prefer facade when available, otherwise use Dompdf core (Laravel 12 compatible).
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('prsystem::pdf.capex_export', compact('capex', 'approvals', 'capexSebelumnya'));
            return $pdf->stream($fileName);
        }

        if (class_exists(\Dompdf\Dompdf::class)) {
            $html = view('prsystem::pdf.capex_export', compact('capex', 'approvals', 'capexSebelumnya'))->render();
            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            ]);
        }

        abort(500, 'PDF engine is not installed. Please install dompdf/dompdf or barryvdh/laravel-dompdf.');
    }

    public function upload(\Illuminate\Http\Request $request, \Modules\PrSystem\Models\CapexRequest $capex)
    {
        $request->validate([
            'signed_file' => 'required|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->file('signed_file')) {
            $path = $request->file('signed_file')->store('capex_signed', 'public');
            $capex->update(['signed_file_path' => $path]);
            
            return back()->with('success', 'Signed Document Uploaded Successfully');
        }

        return back()->with('error', 'File upload failed');
    }

    public function verify(\Illuminate\Http\Request $request, \Modules\PrSystem\Models\CapexRequest $capex)
    {
        // Only Admin can verify
        if (!$this->isPrAdmin()) {
            abort(403);
        }

        if (!$capex->signed_file_path) {
            return back()->with('error', 'No signed document found to verify.');
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($capex) {
            // 1. Mark Capex as Verified & Final Approved (if not already)
            $capex->update([
                'is_verified' => true,
                'status' => 'Approved', // Ensure final status
            ]);

            // 2. Auto-Create PR
            // Generate PR Number using standard service logic
            $prNumber = \Modules\PrSystem\Services\PrService::generatePrNumber($capex->department_id, now());

            $pr = \Modules\PrSystem\Models\PurchaseRequest::create([
                'pr_number'    => $prNumber,
                'user_id'      => $capex->user_id,
                'department_id' => $capex->department_id,
                'status'       => 'Pending',
                'request_date' => now(),
                'description'  => 'Auto-generated from Capex: ' . $capex->capex_number,
                'total_estimated_cost' => $capex->amount,
            ]);
            
            // Create PR Item
            \Modules\PrSystem\Models\PrItem::create([
                'purchase_request_id' => $pr->id,
                'item_name'           => '[CAPEX] ' . $capex->capexBudget->capexAsset->name,
                'quantity'            => $capex->quantity,
                'unit'                => $capex->department->name,
                'price_estimation'    => $capex->price,
                'subtotal'            => $capex->amount,
                'specification'       => 'Refer to Capex #' . $capex->capex_number,
                'remarks'             => $capex->description,
            ]);
            
            // Link PR to Capex
            $capex->update(['pr_id' => $pr->id]);

            // 3. Generate PR Approvals
            $prService = new \Modules\PrSystem\Services\PrService();
            $prService->startApprovals($pr);

            // 4. Full-approve PR in the same verify action
            $approvalService = new \Modules\PrSystem\Services\ApprovalService();
            $approvalService->fullApprove($pr, auth()->id());
        });

        return back()->with('success', 'Capex Verified, PR generated, and fully approved!');
    }
}

