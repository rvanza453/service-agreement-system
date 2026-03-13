<?php

use Illuminate\Support\Facades\Route;
use Modules\ServiceAgreementSystem\Http\Controllers\Auth\LoginController;
use Modules\ServiceAgreementSystem\Http\Controllers\DashboardController;
use Modules\ServiceAgreementSystem\Http\Controllers\ContractorController;
use Modules\ServiceAgreementSystem\Http\Controllers\UspkSubmissionController;
use Modules\ServiceAgreementSystem\Http\Controllers\UspkApprovalController;

// Auth routes
Route::prefix('sas')->name('sas.')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Authenticated routes
Route::middleware(['auth'])->prefix('sas')->name('sas.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Kontraktor CRUD
    Route::resource('contractors', ContractorController::class);

    // USPK Submissions
    Route::resource('uspk', UspkSubmissionController::class);
    Route::post('uspk/{uspk}/submit', [UspkSubmissionController::class, 'submit'])->name('uspk.submit');

    // USPK Approvals
    Route::post('uspk/{uspk}/approve', [UspkApprovalController::class, 'approve'])->name('uspk.approve');
    Route::post('uspk/{uspk}/reject', [UspkApprovalController::class, 'reject'])->name('uspk.reject');

    // API endpoints for cascade dropdowns
    Route::get('api/sub-departments/{departmentId}', [UspkSubmissionController::class, 'getSubDepartments'])->name('api.sub-departments');
    Route::get('api/blocks/{subDepartmentId}', [UspkSubmissionController::class, 'getBlocks'])->name('api.blocks');
    Route::get('api/budget-activities/{blockId}', [UspkSubmissionController::class, 'getBudgetActivities'])->name('api.budget-activities');
});
