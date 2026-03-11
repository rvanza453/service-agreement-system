<?php

use Illuminate\Support\Facades\Route;
use Modules\ServiceAgreementSystem\Http\Controllers\ServiceAgreementSystemController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('serviceagreementsystems', ServiceAgreementSystemController::class)->names('serviceagreementsystem');
});
