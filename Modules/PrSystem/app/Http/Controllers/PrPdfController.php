<?php

namespace Modules\PrSystem\Http\Controllers;

use Modules\PrSystem\Models\PurchaseRequest;
use Illuminate\Http\Request;

class PrPdfController extends Controller
{
    public function export(PurchaseRequest $purchaseRequest)
    {
        // Check if PR is fully approved
        if ($purchaseRequest->status !== 'Approved') {
            abort(403, 'PR belum fully approved. Export PDF hanya tersedia untuk PR yang sudah disetujui semua.');
        }

        $purchaseRequest->load([
            'user',
            'department.site',
            'subDepartment',
            'items.product',
            'items.job',
            'approvals' => function ($query) {
                $query->where('status', 'Approved')
                      ->with('approver')
                      ->orderBy('level');
            }
        ]);
        
        $year = $purchaseRequest->request_date->format('Y');
        $subDeptId = $purchaseRequest->sub_department_id;
        $dept = $purchaseRequest->department;

        $isJobCoa = $dept->budget_type === \Modules\PrSystem\Enums\BudgetingType::JOB_COA;
        $subDept = $purchaseRequest->subDepartment;
        $subDeptName = $subDept ? ($subDept->coa ? $subDept->coa . ' - ' : '') . $subDept->name : '-';
        
        $jobName = ($dept->name ?? '-') . ' / ' . $subDeptName;
        
        $totalBudget = 0;
        $totalActual = 0;

        if ($isJobCoa) {
            // Logic for Job Based PR
            // Assuming single job per PR as enforced in Create
            $firstItem = $purchaseRequest->items->first();
            $jobId = $firstItem ? $firstItem->job_id : null;
            
            if ($jobId) {
                $job = \Modules\PrSystem\Models\Job::find($jobId);
                if ($job) {
                    $jobName .= ' / ' . ($job->code ? $job->code . ' - ' : '') . $job->name;
                    
                    // Specific Job Budget
                    $budget = \Modules\PrSystem\Models\Budget::where('sub_department_id', $subDeptId)
                                ->where('job_id', $jobId)
                                ->where('year', $year)
                                ->first();
                    
                    $totalBudget = $budget ? $budget->amount : 0;
                    
                    // FIXED: Use Budget->used_amount (Inventory Out) instead of summing PRs
                    $totalActual = $budget ? $budget->used_amount : 0;
                }
            }
        } else {
            
            $budgets = \Modules\PrSystem\Models\Budget::where('sub_department_id', $subDeptId)
                        ->where('year', $year)
                        ->get();

            $totalBudget = $budgets->sum('amount');
            
            $totalActual = $budgets->sum('used_amount');
        }
        
        $currentRequest = $purchaseRequest->items->sum(function($item) {
            return $item->getFinalQuantity() * $item->price_estimation;
        });
        
        $saldo = $totalBudget - ($totalActual + $currentRequest);

        $viewData = [
            'pr' => $purchaseRequest,
            'approvals' => $purchaseRequest->approvals,
            'jobName' => $jobName,
            'budgetInfo' => [
                'total' => $totalBudget,
                'actual' => $totalActual,
                'current' => $currentRequest,
                'saldo' => $saldo,
            ],
        ];

        // Download PDF with safe filename (replace / with _)
        $safeFilename = str_replace('/', '_', $purchaseRequest->pr_number);
        $fileName = "PR_{$safeFilename}.pdf";

        // Prefer Barryvdh facade when installed.
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('prsystem::pdf.pr_export', $viewData);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download($fileName);
        }

        // Fallback to Dompdf core package.
        if (class_exists(\Dompdf\Dompdf::class)) {
            $html = view('prsystem::pdf.pr_export', $viewData)->render();
            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        }

        abort(500, 'PDF engine is not installed. Please install dompdf/dompdf or barryvdh/laravel-dompdf.');
    }
}
