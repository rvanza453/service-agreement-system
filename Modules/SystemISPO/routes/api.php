<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemISPO\Http\Controllers\SystemISPOController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('systemispos', SystemISPOController::class)->names('systemispo');
});
