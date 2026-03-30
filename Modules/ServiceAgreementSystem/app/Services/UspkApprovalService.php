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
    public function approve(UspkSubmission $submission, int $userId, ?string $comment = null, ?int $selectedTenderId = null): UspkApproval
    {
        $user = \App\Models\User::find($userId);
        $isSuperAdmin = $user->hasRole('Super Admin');

        // Cari pending approval yang sesuai dengan level terkecil yang belum di-approve
        $approval = $submission->approvals()
            ->where('status', UspkApproval::STATUS_PENDING)
            ->orderBy('level', 'asc')
            ->first();

        if (!$approval) {
            throw new \Exception('Sudah tidak ada tahap yang perlu di-approve.');
        }

        // Cek apakah user yang login berhak di step tersebut
        $step = \Modules\ServiceAgreementSystem\Models\UspkApprovalSchemaStep::where('schema_id', $approval->schema_id)
            ->where('level', $approval->level)
            ->first();

        // Super Admin bisa override siapa saja
        if (!$isSuperAdmin && (!$step || $step->user_id !== $userId)) {
            throw new \Exception('Anda tidak memiliki otorisasi untuk melakukan approval pada tahap ini.');
        }

        $approval->update([
            'status' => UspkApproval::STATUS_APPROVED,
            'user_id' => $userId, // Simpan siapa yang menekan tombol
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

        // Jika approver memilih kontraktor pemenang
        if ($selectedTenderId) {
            // Uncheck semua tender untuk USPK ini
            $submission->tenders()->update(['is_selected' => false]);
            
            // Set tender yang dipilih menjadi true
            $submission->tenders()->where('id', $selectedTenderId)->update(['is_selected' => true]);

            Log::info('Contractor selected during USPK Approval', [
                'uspk_id' => $submission->id,
                'tender_id' => $selectedTenderId,
                'approver_id' => $userId
            ]);
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
        $user = \App\Models\User::find($userId);
        $isSuperAdmin = $user->hasRole('Super Admin');

        $approval = $submission->approvals()
            ->where('status', UspkApproval::STATUS_PENDING)
            ->orderBy('level', 'asc')
            ->first();

        if (!$approval) {
            throw new \Exception('Sudah tidak ada tahap yang bisa ditolak.');
        }

        $step = \Modules\ServiceAgreementSystem\Models\UspkApprovalSchemaStep::where('schema_id', $approval->schema_id)
            ->where('level', $approval->level)
            ->first();

        if (!$isSuperAdmin && (!$step || $step->user_id !== $userId)) {
            throw new \Exception('Anda tidak memiliki otorisasi untuk menolak pada tahap ini.');
        }

        $approval->update([
            'status' => UspkApproval::STATUS_REJECTED,
            'user_id' => $userId,
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
