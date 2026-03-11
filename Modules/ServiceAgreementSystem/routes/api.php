<?php

use Illuminate\Support\Facades\Route;
use Modules\ServiceAgreementSystem\Http\Controllers\ServiceAgreementSystemController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('serviceagreementsystems', ServiceAgreementSystemController::class)->names('serviceagreementsystem');
});
