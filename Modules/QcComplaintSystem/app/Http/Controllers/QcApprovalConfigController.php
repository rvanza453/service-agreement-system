<?php

namespace Modules\QcComplaintSystem\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Modules\QcComplaintSystem\Http\Requests\UpdateQcApprovalConfigRequest;
use Modules\QcComplaintSystem\Services\QcApprovalConfigService;

class QcApprovalConfigController extends Controller
{
    public function __construct(
        protected QcApprovalConfigService $configService
    ) {}

    public function edit()
    {
        return view('qccomplaintsystem::approval-config.edit', [
            'config' => $this->configService->getActiveConfig(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateQcApprovalConfigRequest $request)
    {
        $this->configService->updateApprovers(
            $request->validated()['approver_user_ids'],
            (int) auth()->id()
        );

        return redirect()->route('qc.approval-config.edit')
            ->with('success', 'Konfigurasi approver QC berhasil diperbarui.');
    }
}
