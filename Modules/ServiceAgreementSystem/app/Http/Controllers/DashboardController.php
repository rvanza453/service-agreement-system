<?php

namespace Modules\ServiceAgreementSystem\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ServiceAgreementSystem\Models\UspkSubmission;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $stats = [
            'total_uspk' => UspkSubmission::count(),
            'draft' => UspkSubmission::where('status', 'draft')->count(),
            'submitted' => UspkSubmission::where('status', 'submitted')->count(),
            'in_review' => UspkSubmission::where('status', 'in_review')->count(),
            'approved' => UspkSubmission::where('status', 'approved')->count(),
            'rejected' => UspkSubmission::where('status', 'rejected')->count(),
        ];

        $recentUspk = UspkSubmission::with(['department', 'submitter'])
            ->latest()
            ->limit(5)
            ->get();

        return view('serviceagreementsystem::dashboard', compact('stats', 'recentUspk'));
    }
}
