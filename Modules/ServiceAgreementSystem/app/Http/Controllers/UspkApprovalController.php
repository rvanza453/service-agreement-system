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

    public function approve(Request $request, UspkSubmission $uspk)
    {
        $request->validate(['comment' => 'nullable|string']);

        $this->approvalService->approve($uspk, auth()->id(), $request->comment);

        return redirect()->route('sas.uspk.show', $uspk)->with('success', 'USPK berhasil di-approve.');
    }

    public function reject(Request $request, UspkSubmission $uspk)
    {
        $request->validate(['comment' => 'required|string']);

        $this->approvalService->reject($uspk, auth()->id(), $request->comment);

        return redirect()->route('sas.uspk.show', $uspk)->with('success', 'USPK berhasil di-reject.');
    }
}
