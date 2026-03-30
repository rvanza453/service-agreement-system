<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemISPO\Http\Controllers\SystemISPOController;
use Modules\SystemISPO\Http\Controllers\IspoController;
use Modules\SystemISPO\Http\Controllers\AdminIspoItemController;

Route::middleware(['auth', 'assigned.role', 'ispo.role'])->group(function () {
    Route::resource('systemispos', SystemISPOController::class)->names('systemispo');
    
    Route::prefix('ispo')->name('ispo.')->group(function () {
        Route::get('/', [IspoController::class, 'index'])->name('index');
        Route::post('/store', [IspoController::class, 'store'])->name('store');
        Route::get('/{id}', [IspoController::class, 'show'])->name('show');
        Route::post('/{id}/entry', [IspoController::class, 'updateEntry'])->name('updateEntry');
        Route::post('/{id}/bulk-update', [IspoController::class, 'bulkUpdate'])->name('bulkUpdate');
        Route::delete('/attachment/{id}', [IspoController::class, 'destroyAttachment'])->name('attachment.destroy');
        Route::get('/history/{entryId}', [IspoController::class, 'getHistory'])->name('history');

        // Admin Routes - Only for Admin
        Route::middleware(['ispo.role:ISPO Admin'])->prefix('admin')->name('admin.')->group(function () {
            Route::get('/items', [AdminIspoItemController::class, 'index'])->name('items.index');
            Route::post('/items', [AdminIspoItemController::class, 'store'])->name('items.store');
            Route::put('/items/{item}', [AdminIspoItemController::class, 'update'])->name('items.update');
            Route::delete('/items/{item}', [AdminIspoItemController::class, 'destroy'])->name('items.destroy');
        });
    });
});
