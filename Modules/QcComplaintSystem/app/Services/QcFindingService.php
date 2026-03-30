<?php

namespace Modules\QcComplaintSystem\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\QcComplaintSystem\Models\QcFinding;
use Modules\QcComplaintSystem\Models\QcFindingApprovalStep;
use Modules\QcComplaintSystem\Repositories\QcFindingRepository;

class QcFindingService
{
    public function __construct(
        protected QcFindingRepository $repository,
        protected QcApprovalConfigService $approvalConfigService
    ) {}

    public function paginate(array $filters = [])
    {
        return $this->repository->paginate($filters);
    }

    public function statusCounts(array $filters = []): array
    {
        return $this->repository->statusCounts($filters);
    }

    public function categoryBreakdown(array $filters = []): array
    {
        return $this->repository->categoryBreakdown($filters);
    }

    public function summaryBySite(array $filters = [])
    {
        return $this->repository->summaryBySite($filters);
    }

    public function summaryByDepartment(array $filters = [])
    {
        return $this->repository->summaryByDepartment($filters);
    }

    public function findById(int $id): QcFinding
    {
        return $this->repository->findById($id);
    }

    public function pendingApprovalsForUser(int $userId): LengthAwarePaginator
    {
        $query = QcFindingApprovalStep::query()
            ->with([
                'approver:id,name',
                'finding.department:id,name',
                'finding.subDepartment:id,name',
                'finding.block:id,name',
                'finding.reporter:id,name',
                'finding.approvalSteps',
            ])
            ->where('approver_user_id', $userId)
            ->where('status', QcFindingApprovalStep::STATUS_PENDING)
            ->whereHas('finding', function ($builder) {
                $builder->where('status', QcFinding::STATUS_IN_REVIEW);
            })
            ->orderBy('created_at');

        return $query->get()
            ->filter(fn (QcFindingApprovalStep $step) => (int) ($step->finding?->currentPendingApprovalStep()?->id ?? 0) === (int) $step->id)
            ->values()
            ->pipe(function ($collection) {
                $page = request()->integer('page', 1);
                $perPage = 12;
                $total = $collection->count();
                $items = $collection->slice(($page - 1) * $perPage, $perPage)->values();

                return new \Illuminate\Pagination\LengthAwarePaginator(
                    $items,
                    $total,
                    $perPage,
                    $page,
                    ['path' => request()->url(), 'query' => request()->query()]
                );
            });
    }

    public function create(array $data, int $createdBy): QcFinding
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $payload = $this->normalizeFindingPayload($data, $createdBy);
            $payload['finding_number'] = $this->generateFindingNumber();
            
            $attachments = $data['finding_attachments'] ?? [];
            if (!is_array($attachments)) {
                $attachments = [$attachments];
            }
            $payload['finding_attachments'] = $this->storeFindingAttachments($attachments);

            return $this->repository->create($payload);
        });
    }

    public function update(QcFinding $finding, array $data, int $updatedBy): QcFinding
    {
        if (!$finding->isOpen()) {
            throw new \RuntimeException('Temuan yang sudah closed tidak dapat diubah.');
        }

        return DB::transaction(function () use ($finding, $data, $updatedBy) {
            $payload = $this->normalizeFindingPayload($data, $updatedBy, false);

            if (!empty($data['finding_attachments'])) {
                $attachments = is_array($data['finding_attachments']) ? $data['finding_attachments'] : [$data['finding_attachments']];
                $payload['finding_attachments'] = $this->storeFindingAttachments(
                    $attachments,
                    $finding->finding_attachments ?? []
                );
            }

            return $this->repository->update($finding, $payload);
        });
    }

    public function submitCompletion(QcFinding $finding, array $data, int $userId): QcFinding
    {
        if (!$finding->isOpen()) {
            throw new \RuntimeException('Temuan sudah closed.');
        }

        if (!$this->userCanSubmitCompletion($finding, $userId)) {
            throw new \RuntimeException('Hanya PIC yang ditunjuk atau pembuat temuan yang dapat submit penyelesaian.');
        }

        return DB::transaction(function () use ($finding, $data, $userId) {
            $approverUserIds = $this->approvalConfigService->getActiveConfig()?->approver_user_ids ?? [];
            $approverUserIds = array_values(array_unique(array_map('intval', $approverUserIds)));

            if (empty($approverUserIds)) {
                throw new \RuntimeException('Konfigurasi approver belum diatur. Silakan isi Approval Config terlebih dahulu.');
            }

            foreach ($finding->completionEvidences as $evidence) {
                Storage::disk('public')->delete($evidence->file_path);
            }
            $finding->completionEvidences()->delete();

            foreach (($data['completion_files'] ?? []) as $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }

                $storedPath = $file->store('qc-findings/completions', 'public');
                $finding->completionEvidences()->create([
                    'file_path' => $storedPath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => (int) $file->getSize(),
                    'uploaded_by' => $userId,
                ]);
            }

            $updated = $this->repository->update($finding, [
                'completion_note' => $data['completion_note'],
                'completion_photo_path' => null,
                'completion_submitted_by' => $userId,
                'completion_submitted_at' => now(),
                'completion_approved_by' => null,
                'completion_approved_at' => null,
                'completion_approval_note' => null,
                'completion_rejected_note' => null,
                'needs_resubmission' => false,
                'status' => QcFinding::STATUS_IN_REVIEW,
                'closed_at' => null,
                'updated_by' => $userId,
            ]);

            $updated->approvalSteps()->delete();

            foreach ($approverUserIds as $index => $approverUserId) {
                $updated->approvalSteps()->create([
                    'level' => $index + 1,
                    'approver_user_id' => $approverUserId,
                    'status' => QcFindingApprovalStep::STATUS_PENDING,
                ]);
            }

            return $updated->fresh(['approvalSteps.approver', 'approvalSteps.actor']);
        });
    }

    public function approveCompletion(QcFinding $finding, int $approverUserId, ?string $approvalNote = null): QcFinding
    {
        if (!$finding->hasPendingCompletionApproval()) {
            throw new \RuntimeException('Tidak ada penyelesaian yang menunggu approval.');
        }

        $finding->loadMissing('approvalSteps');
        $currentStep = $finding->currentPendingApprovalStep();

        if (!$currentStep) {
            throw new \RuntimeException('Tahapan approval tidak ditemukan untuk temuan ini.');
        }

        if (!$this->canApproveStep($finding, $currentStep, $approverUserId)) {
            throw new \RuntimeException('Anda tidak memiliki otorisasi approval untuk temuan ini.');
        }

        $currentStep->update([
            'status' => QcFindingApprovalStep::STATUS_APPROVED,
            'note' => $approvalNote,
            'acted_by' => $approverUserId,
            'acted_at' => now(),
        ]);

        $nextStep = $finding->approvalSteps()->pending()->orderBy('level')->first();

        if ($nextStep) {
            return $this->repository->update($finding, [
                'status' => QcFinding::STATUS_IN_REVIEW,
                'updated_by' => $approverUserId,
                'completion_rejected_note' => null,
            ]);
        }

        return $this->repository->update($finding, [
            'completion_approved_by' => $approverUserId,
            'completion_approved_at' => now(),
            'completion_approval_note' => $approvalNote ?: 'Semua level approval telah disetujui.',
            'completion_rejected_note' => null,
            'needs_resubmission' => false,
            'status' => QcFinding::STATUS_CLOSED,
            'closed_at' => now(),
            'updated_by' => $approverUserId,
        ]);
    }

    public function rejectCompletion(QcFinding $finding, int $approverUserId, string $rejectedNote): QcFinding
    {
        if (!$finding->hasPendingCompletionApproval()) {
            throw new \RuntimeException('Tidak ada penyelesaian yang menunggu approval.');
        }

        $finding->loadMissing('approvalSteps');
        $currentStep = $finding->currentPendingApprovalStep();

        if (!$currentStep) {
            throw new \RuntimeException('Tahapan approval tidak ditemukan untuk temuan ini.');
        }

        if (!$this->canApproveStep($finding, $currentStep, $approverUserId)) {
            throw new \RuntimeException('Anda tidak memiliki otorisasi approval untuk temuan ini.');
        }

        $currentStep->update([
            'status' => QcFindingApprovalStep::STATUS_REJECTED,
            'note' => $rejectedNote,
            'acted_by' => $approverUserId,
            'acted_at' => now(),
        ]);

        return $this->repository->update($finding, [
            'completion_approved_by' => null,
            'completion_approved_at' => null,
            'completion_approval_note' => null,
            'completion_rejected_note' => $rejectedNote,
            'needs_resubmission' => true,
            'completion_submitted_by' => null,
            'completion_submitted_at' => null,
            'status' => QcFinding::STATUS_OPEN,
            'closed_at' => null,
            'updated_by' => $approverUserId,
        ]);
    }

    public function userCanSubmitCompletion(QcFinding $finding, int $userId): bool
    {
        $user = User::find($userId);

        // Only Officers or Admins may submit completion evidence
        if (!$user || !$user->hasModuleRole('qc', ['QC Admin', 'QC Officer'])) {
            return false;
        }

        // Admin can submit for any finding; Officer only for findings they own as PIC/creator
        if ($user->hasModuleRole('qc', 'QC Admin')) {
            return true;
        }

        $picUserIds = array_values(array_unique(array_filter(array_map('intval', (array) ($finding->pic_user_ids ?? [])))));

        if ((int) ($finding->pic_user_id ?? 0) > 0) {
            $picUserIds[] = (int) $finding->pic_user_id;
        }

        return in_array($userId, $picUserIds, true) || $finding->created_by === $userId;
    }

    public function userCanApprove(int $userId): bool
    {
        return $this->canApprove($userId);
    }

    public function userCanApproveFinding(QcFinding $finding, int $userId): bool
    {
        $finding->loadMissing('approvalSteps');
        $currentStep = $finding->currentPendingApprovalStep();

        if (!$currentStep) {
            return false;
        }

        return $this->canApproveStep($finding, $currentStep, $userId);
    }

    private function normalizeFindingPayload(array $data, int $actorId, bool $isCreate = true): array
    {
        $actor = User::query()->find($actorId);

        $kategori = $data['kategori'] ?? null;
        $subKategori = $data['sub_kategori'] ?? null;
        $kategoriCode = null;

        if ($kategori) {
            $hierarchy = QcFinding::categoryHierarchy();
            if (isset($hierarchy[$kategori])) {
                $categoryData = $hierarchy[$kategori];
                $kategoriCode = $categoryData['code'];

                if ($subKategori && isset($categoryData['subs'][$subKategori])) {
                    $kategoriCode = $categoryData['subs'][$subKategori]['code'];
                } else {
                    $subKategori = null;
                }
            } else {
                $kategori = null;
                $subKategori = null;
            }
        }

        $selectedPicIds = array_values(array_unique(array_filter(array_map('intval', (array) ($data['pic_user_ids'] ?? [])))));
        if (empty($selectedPicIds) && !empty($data['pic_user_id'])) {
            $selectedPicIds = [(int) $data['pic_user_id']];
        }

        $payload = [
            'finding_date' => $data['finding_date'] ?? now()->toDateString(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'source_type' => $data['source_type'],
            'department_id' => $data['department_id'],
            'sub_department_id' => $data['sub_department_id'],
            'block_id' => $data['block_id'],
            'location' => $data['location'] ?? null,
            'urgency' => $data['urgency'],
            'kategori' => $kategori,
            'sub_kategori' => $subKategori,
            'kategori_code' => $kategoriCode,
            'reporter_user_id' => $actorId,
            'reporter_name' => $actor?->name,
            // Keep single PIC for backward compatibility and store full PIC list for new flow.
            'pic_user_id' => $selectedPicIds[0] ?? null,
            'pic_user_ids' => !empty($selectedPicIds) ? $selectedPicIds : null,
            'updated_by' => $actorId,
        ];

        if ($isCreate) {
            $payload['status'] = QcFinding::STATUS_OPEN;
            $payload['created_by'] = $data['created_by'] ?? $actorId;
        }

        return $payload;
    }

    private function generateFindingNumber(): string
    {
        $prefix = 'QCF-' . now()->format('Ym');
        $lastFinding = QcFinding::query()
            ->where('finding_number', 'like', $prefix . '-%')
            ->latest('id')
            ->first();

        $lastSequence = 0;
        if ($lastFinding) {
            $parts = explode('-', $lastFinding->finding_number);
            $lastSequence = (int) end($parts);
        }

        return sprintf('%s-%04d', $prefix, $lastSequence + 1);
    }

    private function storeFindingAttachments(array $files, array $oldPaths = []): array
    {
        // If no new files uploaded, keep the old ones (if any)
        if (empty($files) || !collect($files)->contains(fn($f) => $f instanceof UploadedFile)) {
            return $oldPaths;
        }

        // Delete old files
        foreach ($oldPaths as $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $newPaths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $newPaths[] = $file->store('qc-findings/reporting', 'public');
            }
        }

        return $newPaths;
    }

    private function canApprove(int $userId): bool
    {
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        // QC Admin overrides all — can approve at any level
        if ($user->hasModuleRole('qc', 'QC Admin')) {
            return true;
        }

        // QC Approver must also be explicitly listed in the approval config
        if ($user->hasModuleRole('qc', 'QC Approver')) {
            return $this->approvalConfigService->canApprove($userId);
        }

        return false;
    }

    private function canApproveStep(QcFinding $finding, QcFindingApprovalStep $step, int $userId): bool
    {
        $isCurrentLevel = (int) ($finding->currentPendingApprovalStep()?->id ?? 0) === (int) $step->id;

        if (!$isCurrentLevel) {
            return false;
        }

        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        // QC Admin may approve at any level regardless of assignment
        if ($user->hasModuleRole('qc', 'QC Admin')) {
            return true;
        }

        // QC Approver may only approve the step they are explicitly assigned to
        if ($user->hasModuleRole('qc', 'QC Approver')) {
            return (int) $step->approver_user_id === $userId;
        }

        return false;
    }
}
