<?php

use Illuminate\Support\Facades\Route;
use Modules\QcComplaintSystem\Http\Controllers\QcApprovalInboxController;
use Modules\QcComplaintSystem\Http\Controllers\QcApprovalConfigController;
use Modules\QcComplaintSystem\Http\Controllers\QcFindingController;
use Modules\QcComplaintSystem\Http\Controllers\QcFindingCommentController;

/*
|--------------------------------------------------------------------------
| QC Complaint System Routes
|--------------------------------------------------------------------------
|
| Role access matrix:
|   QC Admin    – full access (config, create, submit, approve/reject, override)
|   QC Officer  – create findings, submit completion (own PIC/creator only)
|   QC Approver – view findings, inbox, approve/reject at assigned level
|
*/

Route::middleware(['auth', 'qc.role:QC Admin,QC Officer,QC Approver'])
    ->prefix('qc')
    ->name('qc.')
    ->group(function () {

        // ── All QC roles ─────────────────────────────────────────────
        Route::get('/', [QcFindingController::class, 'summary'])->name('dashboard');
        Route::get('findings', [QcFindingController::class, 'index'])->name('findings.index');

        // JSON helpers (used by create/edit forms)
        Route::get('api/sub-departments/{departmentId}', [QcFindingController::class, 'getSubDepartments'])
            ->name('api.sub-departments');
        Route::get('api/blocks/{subDepartmentId}', [QcFindingController::class, 'getBlocks'])
            ->name('api.blocks');

        // ── QC Officer + QC Admin ─────────────────────────────────────
        // IMPORTANT: static routes (create) must be declared BEFORE wildcard ({finding})
        Route::middleware('qc.role:QC Admin,QC Officer')->group(function () {
            Route::get('findings/create', [QcFindingController::class, 'create'])->name('findings.create');
            Route::post('findings', [QcFindingController::class, 'store'])->name('findings.store');
        });

        // ── All QC roles – view single finding (after static paths above) ──
        Route::get('findings/{finding}', [QcFindingController::class, 'show'])->name('findings.show');

        // ── QC Officer + QC Admin – edit / submit ─────────────────────
        Route::middleware('qc.role:QC Admin,QC Officer')->group(function () {
            Route::get('findings/{finding}/edit', [QcFindingController::class, 'edit'])->name('findings.edit');
            Route::put('findings/{finding}', [QcFindingController::class, 'update'])->name('findings.update');
            Route::delete('findings/{finding}', [QcFindingController::class, 'destroy'])->name('findings.destroy');
            Route::post('findings/{finding}/submit-completion', [QcFindingController::class, 'submitCompletion'])
                ->name('findings.submit-completion');
        });

        // ── QC Approver + QC Admin ────────────────────────────────────
        Route::middleware('qc.role:QC Admin,QC Approver')->group(function () {
            Route::get('approvals', [QcApprovalInboxController::class, 'index'])->name('approvals.index');
            Route::post('findings/{finding}/approve-completion', [QcFindingController::class, 'approveCompletion'])
                ->name('findings.approve-completion');
            Route::post('findings/{finding}/reject-completion', [QcFindingController::class, 'rejectCompletion'])
                ->name('findings.reject-completion');
        });

        // ── All QC roles – comments ──────────────────────────────────
        Route::post('findings/{finding}/comments', [QcFindingCommentController::class, 'store'])
            ->name('findings.comments.store');
        Route::delete('findings/{finding}/comments/{comment}', [QcFindingCommentController::class, 'destroy'])
            ->name('findings.comments.destroy');
        Route::get('findings/{finding}/comments', [QcFindingCommentController::class, 'show'])
            ->name('findings.comments.show');

        // ── QC Admin only ─────────────────────────────────────────────
        Route::middleware('qc.role:QC Admin')->group(function () {
            Route::get('approval-config', [QcApprovalConfigController::class, 'edit'])->name('approval-config.edit');
            Route::put('approval-config', [QcApprovalConfigController::class, 'update'])->name('approval-config.update');
        });
    });
