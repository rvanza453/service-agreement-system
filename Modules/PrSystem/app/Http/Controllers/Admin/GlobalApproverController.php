<?php

namespace Modules\PrSystem\Http\Controllers\Admin;

use Modules\PrSystem\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GlobalApproverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $approvers = \Modules\PrSystem\Models\GlobalApproverConfig::with(['user', 'site'])->orderBy('level')->get();
        // Only show users from HO site (or all users if needed, HO is safer for global approvers usually, but Investor might be from anywhere?)
        // Let's allow all users for flexibility, or keep HO. Current logic keeps HO.
        // The investor might strictly be an external party not in "HO" site in the system? 
        // Checks: $users query filters by HO. 
        // If investor is created as a user, they might be assigned to a specific site or HO. 
        // Safest is to allow ALL users to be picked as Global Approver.
        $users = \Modules\PrSystem\Models\User::orderBy('name')->get(); 
        
        $sites = \Modules\PrSystem\Models\Site::orderBy('name')->get();
        
        return view('prsystem::admin.global_approvers.index', compact('approvers', 'users', 'sites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_name' => 'required|string',
            'level' => 'required|integer', // Removed unique constraint
            'site_id' => 'nullable|exists:sites,id',
        ]);

        \Modules\PrSystem\Models\GlobalApproverConfig::create($request->all());

        return redirect()->route('global-approvers.index')->with('success', 'Global Approver added successfully.');
    }

    public function destroy(\Modules\PrSystem\Models\GlobalApproverConfig $globalApprover)
    {
        $globalApprover->delete();
        return back()->with('success', 'Global Approver removed successfully.');
    }
}
