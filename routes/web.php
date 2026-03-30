<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\GlobalManagementController;
use App\Http\Controllers\ModuleHubController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('modules.index')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    // Backward-compatible SAS login aliases used by legacy views.
    Route::get('/sas/login', [LoginController::class, 'showLoginForm'])->name('sas.login');
    Route::post('/sas/login', [LoginController::class, 'login'])->name('sas.login.submit');
});

Route::middleware(['auth', 'assigned.role'])->group(function () {
    Route::get('/modules', [ModuleHubController::class, 'index'])->name('modules.index');

    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/management', [GlobalManagementController::class, 'index'])->name('management.dashboard');

        // Backward-compatible aliases to keep old account URLs working,
        // while user management is now centralized at users.* routes.
        Route::get('/accounts', fn () => redirect()->route('users.index'))->name('accounts.index');
        Route::get('/accounts/create', fn () => redirect()->route('users.create'))->name('accounts.create');
        Route::get('/accounts/{account}/edit', fn (User $account) => redirect()->route('users.edit', $account))->name('accounts.edit');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Backward-compatible SAS logout alias used by legacy layouts.
    Route::post('/sas/logout', [LoginController::class, 'logout'])->name('sas.logout');
});