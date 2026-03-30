<?php

namespace Modules\ServiceAgreementSystem\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ServiceAgreementSystem\Models\UspkSubmission;
use Modules\ServiceAgreementSystem\Services\UspkApprovalService;

class UspkApprovalController extends Controller
{
    public function __construct(
        protected UspkApprovalService $approvalService
    ) {}

    public function index()
    {
        $userId = auth()->id();
        // Fetch USPK submissions that have a pending approval step for the current user
        $pendingUspks = UspkSubmission::whereHas('approvals', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('status', 'pending');
        })
        ->with(['department', 'subDepartment', 'block', 'submitter'])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return view('serviceagreementsystem::uspk-approval.index', compact('pendingUspks'));
    }

    public function approve(Request $request, UspkSubmission $uspk)
    {
        $request->validate([
            'comment' => 'nullable|string',
            'selected_tender_id' => 'nullable|exists:uspk_tenders,id'
        ]);

        $this->approvalService->approve($uspk, auth()->id(), $request->comment, $request->selected_tender_id);

        return redirect()->route('sas.uspk.show', $uspk)->with('success', 'USPK berhasil di-approve.');
    }

    public function reject(Request $request, UspkSubmission $uspk)
    {
        $request->validate(['comment' => 'required|string']);

        $this->approvalService->reject($uspk, auth()->id(), $request->comment);

        return redirect()->route('sas.uspk.show', $uspk)->with('success', 'USPK berhasil di-reject.');
    }
}
