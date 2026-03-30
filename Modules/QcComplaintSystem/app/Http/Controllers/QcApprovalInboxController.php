<?php

namespace Modules\QcComplaintSystem\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\QcComplaintSystem\Services\QcFindingService;

class QcApprovalInboxController extends Controller
{
    public function __construct(
        protected QcFindingService $findingService
    ) {}

    public function index(): View
    {
        return view('qccomplaintsystem::approvals.index', [
            'approvals' => $this->findingService->pendingApprovalsForUser((int) auth()->id()),
        ]);
    }
}
