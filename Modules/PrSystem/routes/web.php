<?php

use Modules\PrSystem\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Modules\PrSystem\Http\Controllers\PrController;
use Modules\PrSystem\Http\Controllers\ApprovalController;
use Modules\PrSystem\Http\Controllers\InventoryImportController;
use Modules\PrSystem\Http\Controllers\Auth\EmailVerificationNotificationController;
use Modules\PrSystem\Http\Controllers\Auth\PasswordController;

// Global admin resources reused by multiple modules.
Route::middleware(['auth', 'assigned.role', 'role:Admin'])->group(function () {
    Route::resource('departments', \Modules\PrSystem\Http\Controllers\Admin\DepartmentController::class);
    Route::resource('master-departments', \Modules\PrSystem\Http\Controllers\Admin\MasterDepartmentController::class);
    Route::resource('sub-departments', \Modules\PrSystem\Http\Controllers\Admin\SubDepartmentController::class);
    Route::resource('users', \Modules\PrSystem\Http\Controllers\Admin\UserController::class);
    Route::resource('sites', \Modules\PrSystem\Http\Controllers\Admin\SiteController::class);

    Route::post('/users/{user}/impersonate', [\Modules\PrSystem\Http\Controllers\Admin\UserController::class, 'impersonate'])->name('users.impersonate');
    Route::get('/admin/activity-logs', [\Modules\PrSystem\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
});

// Keep leave impersonation globally available while impersonating across modules.
Route::middleware(['auth', 'assigned.role'])->group(function () {
    Route::post('/users/leave-impersonate', [\Modules\PrSystem\Http\Controllers\Admin\UserController::class, 'leaveImpersonate'])
        ->name('users.leave-impersonate');
});

Route::get('/pr-dashboard', [\Modules\PrSystem\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'assigned.role', 'pr.role'])
    ->name('pr.dashboard');

Route::middleware(['auth', 'assigned.role', 'pr.role'])->group(function () {
    // Keep profile utility endpoints available after modular migration.
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/employment', [ProfileController::class, 'updateEmployment'])->name('profile.update-employment');

    // Signature routes
    Route::post('/profile/signature', [ProfileController::class, 'uploadSignature'])->name('profile.signature.upload');
    Route::delete('/profile/signature', [ProfileController::class, 'deleteSignature'])->name('profile.signature.delete');

    Route::get('/pr/export', [PrController::class, 'export'])->name('pr.export');
    Route::resource('pr', PrController::class);
    Route::post('/pr/{pr}/reply-hold', [PrController::class, 'replyToHold'])->name('pr.replyHold');
    Route::get('/pr/{purchaseRequest}/export-pdf', [\Modules\PrSystem\Http\Controllers\PrPdfController::class, 'export'])->name('pr.export.pdf');
    Route::get('/pr/{purchaseRequest}/attachment/download', [PrController::class, 'downloadAttachment'])->name('pr.attachment.download');
    Route::get('/api/budget/{subDepartment}', [PrController::class, 'getBudgetStatus'])->name('api.budget.status');
    Route::get('/api/sub-department/{subDepartment}/jobs', [PrController::class, 'getJobs'])->name('api.jobs');
    Route::get('/api/department/{department}/jobs', [PrController::class, 'getJobsByDepartment'])->name('api.department.jobs');
    Route::get('/api/sites/{site}/departments', [\Modules\PrSystem\Http\Controllers\Admin\DepartmentController::class, 'getDepartmentsBySite'])->name('api.sites.departments');

    // --- PO READ ROUTES (All Authenticated Users) ---
    Route::get('/po', [\Modules\PrSystem\Http\Controllers\PoController::class, 'index'])->name('po.index');
    Route::get('/po/{po}', [\Modules\PrSystem\Http\Controllers\PoController::class, 'show'])
        ->where('po', '[0-9]+')
        ->name('po.show');
    Route::get('/po/{po}/export-pdf', [\Modules\PrSystem\Http\Controllers\PoPdfController::class, 'export'])->name('po.export.pdf');

    // FETCH PRODUCT BY SITES
    Route::get('/api/sites/{site}/products', [\Modules\PrSystem\Http\Controllers\PrController::class, 'getProductsBySite'])->name('api.site.products');

    // --- ADMIN ROUTES (Full Access) ---
    Route::middleware(['pr.role:Admin'])->group(function () {
        Route::resource('global-approvers', \Modules\PrSystem\Http\Controllers\Admin\GlobalApproverController::class);

        // System Reset
        Route::get('/system/reset-warehouse', [\Modules\PrSystem\Http\Controllers\Admin\SystemResetController::class, 'showResetWarehouse'])->name('system.reset-warehouse');
        Route::post('/system/reset-warehouse', [\Modules\PrSystem\Http\Controllers\Admin\SystemResetController::class, 'resetWarehouse'])->name('system.reset-warehouse.post');

        // Full Approve PR
        Route::post('/pr/{pr}/full-approve', [\Modules\PrSystem\Http\Controllers\PrController::class, 'fullApprove'])->name('pr.full-approve');

        // Inventory master/import actions (Admin only)
        Route::get('/inventory/create', [\Modules\PrSystem\Http\Controllers\InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/inventory', [\Modules\PrSystem\Http\Controllers\InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/inventory-import/kde-script', [\Modules\PrSystem\Http\Controllers\InventoryImportController::class, 'importKdeInventory'])->name('inventory.import.kde');
        Route::get('/inventory-import/out', [\Modules\PrSystem\Http\Controllers\InventoryImportController::class, 'formOut'])->name('inventory.import.out');
        Route::post('/inventory-import/out', [\Modules\PrSystem\Http\Controllers\InventoryImportController::class, 'store'])->name('inventory.import.out.process');
        Route::get('/inventory/{warehouse}/edit', [\Modules\PrSystem\Http\Controllers\InventoryController::class, 'edit'])->name('inventory.edit');
        Route::put('/inventory/{warehouse}', [\Modules\PrSystem\Http\Controllers\InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{warehouse}', [\Modules\PrSystem\Http\Controllers\InventoryController::class, 'destroy'])->name('inventory.destroy');

        // Product & Vendor Write Access (Admin Only)
        Route::get('products/export', [\Modules\PrSystem\Http\Controllers\Admin\ProductController::class, 'export'])->name('products.export');
        Route::resource('products', \Modules\PrSystem\Http\Controllers\Admin\ProductController::class)->except(['index', 'show']);
        Route::resource('vendors', \Modules\PrSystem\Http\Controllers\Admin\VendorController::class)->except(['index', 'show']);

        Route::resource('jobs', \Modules\PrSystem\Http\Controllers\Admin\JobController::class);
        Route::post('jobs/{job}/mappings', [\Modules\PrSystem\Http\Controllers\Admin\JobController::class, 'updateMappings'])->name('jobs.mappings.update');
        Route::get('/admin/budgets', [\Modules\PrSystem\Http\Controllers\Admin\BudgetController::class, 'index'])->name('admin.budgets.index');
        Route::get('/admin/budgets/{subDepartment}/edit', [\Modules\PrSystem\Http\Controllers\Admin\BudgetController::class, 'edit'])->name('admin.budgets.edit');
        Route::put('/admin/budgets/{subDepartment}', [\Modules\PrSystem\Http\Controllers\Admin\BudgetController::class, 'update'])->name('admin.budgets.update');
        Route::get('/admin/budgets/department/{department}/edit', [\Modules\PrSystem\Http\Controllers\Admin\BudgetController::class, 'editDepartment'])->name('admin.budgets.edit-department');
        Route::put('/admin/budgets/department/{department}', [\Modules\PrSystem\Http\Controllers\Admin\BudgetController::class, 'updateDepartment'])->name('admin.budgets.update-department');

    });

    // --- BUDGET MONITORING (Admin & Approver) ---
    Route::middleware(['pr.role:Admin,Approver'])->group(function () {
        Route::get('/admin/budgets/monitoring', [\Modules\PrSystem\Http\Controllers\Admin\BudgetController::class, 'monitoring'])->name('admin.budgets.monitoring');
        Route::get('/admin/budgets/{budget}/details', [\Modules\PrSystem\Http\Controllers\Admin\BudgetController::class, 'usageDetails'])->name('admin.budgets.details');
    });

    // --- PURCHASING ROUTES (PO & Inventory) ---
    Route::middleware(['pr.role:Purchasing,Admin,Warehouse'])->group(function () {
        Route::get('/pr/{purchaseRequest}/po/select-items', [\Modules\PrSystem\Http\Controllers\PoController::class, 'selectItems'])->name('po.select-items');
        Route::match(['get', 'post'], '/po/create', [\Modules\PrSystem\Http\Controllers\PoController::class, 'create'])->name('po.create');
        Route::post('/po', [\Modules\PrSystem\Http\Controllers\PoController::class, 'store'])->name('po.store');

        // PO Cart
        Route::get('/po/cart', [\Modules\PrSystem\Http\Controllers\PoCartController::class, 'index'])->name('po.cart');
        Route::get('/po/cart/data', [\Modules\PrSystem\Http\Controllers\PoCartController::class, 'getData'])->name('po.cart.data');
        Route::post('/po/cart/add', [\Modules\PrSystem\Http\Controllers\PoCartController::class, 'store'])->name('po.cart.add');
        Route::post('/po/cart/remove', [\Modules\PrSystem\Http\Controllers\PoCartController::class, 'remove'])->name('po.cart.remove');
        Route::post('/po/cart/clear', [\Modules\PrSystem\Http\Controllers\PoCartController::class, 'clear'])->name('po.cart.clear');

        // Inventory operational movement
        Route::get('/inventory/{warehouse}/movement/{type}', [\Modules\PrSystem\Http\Controllers\InventoryController::class, 'createMovement'])->name('inventory.movement');
        Route::post('/inventory/{warehouse}/movement', [\Modules\PrSystem\Http\Controllers\InventoryController::class, 'storeMovement'])->name('inventory.store-movement');
    });

    // --- PO EDIT/DELETE ROUTES ---
    Route::middleware(['pr.role:Admin,Warehouse'])->group(function () {
        Route::get('/po/{po}/edit', [\Modules\PrSystem\Http\Controllers\PoController::class, 'edit'])->name('po.edit');
        Route::put('/po/{po}', [\Modules\PrSystem\Http\Controllers\PoController::class, 'update'])->name('po.update');
        Route::delete('/po/{po}', [\Modules\PrSystem\Http\Controllers\PoController::class, 'destroy'])->name('po.destroy');
    });

    // --- GENERIC READ-ONLY VIEWS (Inventory, Products, Vendors) ---
    Route::middleware(['pr.role:Purchasing,Admin,Warehouse'])->group(function () {
        Route::get('/inventory', [\Modules\PrSystem\Http\Controllers\InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/{warehouse}/history', [\Modules\PrSystem\Http\Controllers\InventoryController::class, 'history'])->name('inventory.history');
        Route::get('/inventory/{warehouse}', [\Modules\PrSystem\Http\Controllers\InventoryController::class, 'show'])->name('inventory.show');

        Route::get('/products', [\Modules\PrSystem\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [\Modules\PrSystem\Http\Controllers\Admin\ProductController::class, 'show'])->name('products.show');

        Route::get('/vendors', [\Modules\PrSystem\Http\Controllers\Admin\VendorController::class, 'index'])->name('vendors.index');
        Route::get('/vendors/{vendor}', [\Modules\PrSystem\Http\Controllers\Admin\VendorController::class, 'show'])->name('vendors.show');
    });

    // --- APPROVER ROUTES ---
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approval.index');
    Route::post('/approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
    Route::post('/approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
    Route::post('/approvals/{approval}/hold', [ApprovalController::class, 'hold'])->name('approval.hold');

    Route::middleware(['pr.role:Admin'])->group(function () {
        Route::post('/approvals/{approval}/revert', [ApprovalController::class, 'revert'])->name('approval.revert');
    });

    // --- CAPEX ROUTES ---
    Route::resource('capex', \Modules\PrSystem\Http\Controllers\CapexController::class);
    Route::post('/capex/{capex}/approve', [\Modules\PrSystem\Http\Controllers\CapexController::class, 'approve'])->name('capex.approve');
    Route::post('/capex/{capex}/reject', [\Modules\PrSystem\Http\Controllers\CapexController::class, 'reject'])->name('capex.reject');
    Route::post('/capex/{capex}/hold', [\Modules\PrSystem\Http\Controllers\CapexController::class, 'hold'])->name('capex.hold');
    Route::post('/capex/{capex}/mark-signed', [\Modules\PrSystem\Http\Controllers\CapexController::class, 'markSigned'])->name('capex.mark-signed');
    Route::get('/capex/{capex}/print', [\Modules\PrSystem\Http\Controllers\CapexController::class, 'print'])->name('capex.print');
    Route::post('/capex/{capex}/upload', [\Modules\PrSystem\Http\Controllers\CapexController::class, 'upload'])->name('capex.upload');
    Route::post('/capex/{capex}/verify', [\Modules\PrSystem\Http\Controllers\CapexController::class, 'verify'])->name('capex.verify');

    Route::middleware(['pr.role:Admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('capex/assets', \Modules\PrSystem\Http\Controllers\CapexAssetController::class)->names('capex.assets');
        Route::resource('capex/budgets', \Modules\PrSystem\Http\Controllers\CapexBudgetController::class)->names('capex.budgets');
        Route::post('capex/budgets/{budget}/pta', [\Modules\PrSystem\Http\Controllers\CapexBudgetController::class, 'addPta'])->name('capex.budgets.pta');
        Route::get('capex/config', [\Modules\PrSystem\Http\Controllers\CapexConfigController::class, 'index'])->name('capex.config.index');
        Route::get('capex/config/{department}/edit', [\Modules\PrSystem\Http\Controllers\CapexConfigController::class, 'edit'])->name('capex.config.edit');
        Route::put('capex/config/{department}', [\Modules\PrSystem\Http\Controllers\CapexConfigController::class, 'update'])->name('capex.config.update');
    });
});