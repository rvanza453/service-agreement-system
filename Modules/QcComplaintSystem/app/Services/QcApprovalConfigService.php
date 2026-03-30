<?php

namespace Modules\QcComplaintSystem\Services;

use Modules\QcComplaintSystem\Models\QcApprovalConfig;

class QcApprovalConfigService
{
    public function getActiveConfig(): ?QcApprovalConfig
    {
        return QcApprovalConfig::query()->latest('id')->first();
    }

    public function updateApprovers(array $approverUserIds, int $updatedBy): QcApprovalConfig
    {
        $approverUserIds = array_values(array_unique(array_map('intval', $approverUserIds)));

        $config = $this->getActiveConfig();

        if (!$config) {
            return QcApprovalConfig::create([
                'approver_user_id' => $approverUserIds[0],
                'approver_user_ids' => $approverUserIds,
                'updated_by' => $updatedBy,
            ]);
        }

        $config->update([
            'approver_user_id' => $approverUserIds[0],
            'approver_user_ids' => $approverUserIds,
            'updated_by' => $updatedBy,
        ]);

        return $config->fresh();
    }

    public function canApprove(int $userId): bool
    {
        $config = $this->getActiveConfig();

        if (!$config) {
            return false;
        }

        return in_array($userId, $config->approver_user_ids, true)
            || $config->approver_user_id === $userId;
    }
}
