<?php

namespace Modules\ServiceAgreementSystem\Services;

use Illuminate\Support\Facades\Log;
use Modules\ServiceAgreementSystem\Models\UspkApproval;
use Modules\ServiceAgreementSystem\Models\UspkSubmission;

class UspkApprovalService
{
    /**
     * Approve USPK
     */
    public function approve(UspkSubmission $submission, int $userId, ?string $comment = null): UspkApproval
    {
        $approval = $submission->approvals()
            ->where('user_id', $userId)
            ->where('status', UspkApproval::STATUS_PENDING)
            ->firstOrFail();

        $approval->update([
            'status' => UspkApproval::STATUS_APPROVED,
            'comment' => $comment,
            'approved_at' => now(),
        ]);

        // Cek apakah semua approval sudah approved
        $allApproved = $submission->approvals()
            ->where('status', '!=', UspkApproval::STATUS_APPROVED)
            ->doesntExist();

        if ($allApproved) {
            $submission->update(['status' => UspkSubmission::STATUS_APPROVED]);
        } else {
            $submission->update(['status' => UspkSubmission::STATUS_IN_REVIEW]);
        }

        Log::info('USPK Approved', [
            'uspk_id' => $submission->id,
            'approver_id' => $userId,
            'level' => $approval->level,
        ]);

        return $approval;
    }

    /**
     * Reject USPK
     */
    public function reject(UspkSubmission $submission, int $userId, ?string $comment = null): UspkApproval
    {
        $approval = $submission->approvals()
            ->where('user_id', $userId)
            ->where('status', UspkApproval::STATUS_PENDING)
            ->firstOrFail();

        $approval->update([
            'status' => UspkApproval::STATUS_REJECTED,
            'comment' => $comment,
            'approved_at' => now(),
        ]);

        $submission->update(['status' => UspkSubmission::STATUS_REJECTED]);

        Log::info('USPK Rejected', [
            'uspk_id' => $submission->id,
            'approver_id' => $userId,
        ]);

        return $approval;
    }
}
