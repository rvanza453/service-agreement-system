<?php

namespace Modules\ServiceAgreementSystem\Repositories;

use Modules\ServiceAgreementSystem\Models\UspkSubmission;

class UspkSubmissionRepository
{
    public function getAll(?string $status = null, ?int $userId = null)
    {
        $query = UspkSubmission::with(['department', 'subDepartment', 'block', 'job', 'submitter', 'tenders.contractor'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($userId) {
            $query->where('submitted_by', $userId);
        }

        return $query->paginate(15);
    }

    public function findById(int $id): UspkSubmission
    {
        return UspkSubmission::with([
            'department',
            'subDepartment',
            'block',
            'job',
            'budgetActivity',
            'submitter',
            'tenders.contractor',
            'approvals.approver',
        ])->findOrFail($id);
    }

    public function create(array $data): UspkSubmission
    {
        return UspkSubmission::create($data);
    }

    public function update(UspkSubmission $submission, array $data): UspkSubmission
    {
        $submission->update($data);
        return $submission->fresh();
    }

    public function delete(UspkSubmission $submission): void
    {
        $submission->delete();
    }
}
