<?php

namespace Modules\PrSystem\Http\Controllers\Admin;

use Modules\PrSystem\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\PrSystem\Models\WarehouseStock;
use Modules\PrSystem\Models\StockMovement;
use Modules\PrSystem\Models\Budget;
use Modules\PrSystem\Helpers\ActivityLogger;

class SystemResetController extends Controller
{
    /**
     * Show the reset warehouse data form.
     */
    public function showResetWarehouse()
    {
        return view('prsystem::admin.system.reset-warehouse');
    }

    /**
     * Process the reset warehouse data request.
     */
    public function resetWarehouse(Request $request)
    {
        $request->validate([
            'admin_password' => 'required|string',
        ]);

        // Verify password against standard config
        $password = $request->input('admin_password');
        if ($password !== config('prsystem.app.admin_verification_password', config('app.admin_verification_password'))) {
            return back()->with('error', 'Password verifikasi salah!');
        }

        try {
            // 1. Reset all warehouse stocks
            WarehouseStock::truncate();

            // 2. Reset all stock movements
            StockMovement::truncate();

            // 3. Reset used amount in budgets
            Budget::query()->update(['used_amount' => 0]);

            ActivityLogger::log('reset-system', 'Admin reset warehouse stock, movements, and budget usages.');

            return redirect()->route('pr.dashboard')->with('success', 'Data Warehouse, Movement, dan Budget Used Amount berhasil direset!');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mereset data: ' . $e->getMessage());
        }
    }
}
