<?php

namespace Modules\PrSystem\Http\Controllers\Admin;

use Modules\PrSystem\Http\Controllers\Controller;
use Modules\PrSystem\Models\Department;
use Modules\PrSystem\Models\Site;
use Modules\PrSystem\Models\SubDepartment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterDepartmentController extends Controller
{
    public function index(Request $request)
    {
        $site_id = $request->site_id;
        
        if ($site_id) {
            $site = Site::findOrFail($site_id);
            $departments = Department::where('site_id', $site_id)->with(['subDepartments'])->orderBy('name')->get();
            return view('prsystem::admin.master_departments.index', compact('departments', 'site'));
        }

        $sites = Site::withCount('departments')->orderBy('name')->get();
        return view('prsystem::admin.master_departments.index', compact('sites'));
    }

    public function create()
    {
        $sites = Site::all();
        $warehouses = \Modules\PrSystem\Models\Warehouse::all();
        return view('prsystem::admin.master_departments.create', compact('sites', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'coa' => ['required', 'string', 'max:50', Rule::unique('departments')->where(function ($query) use ($request) {
                return $query->where('site_id', $request->site_id);
            })],
        ]);

        Department::create($validated);

        return back()->with('success', 'Department created successfully.');
    }

    public function edit(Department $master_department)
    {
        $department = $master_department; 
        $sites = Site::all();
        $warehouses = \Modules\PrSystem\Models\Warehouse::all();
        $department->load('subDepartments'); 

        return view('prsystem::admin.master_departments.edit', compact('department', 'sites', 'warehouses'));
    }

    public function update(Request $request, Department $master_department)
    {
        $department = $master_department;
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id', // Added site_id update capability
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'coa' => ['required', 'string', 'max:50', Rule::unique('departments')->ignore($department->id)->where(function ($query) use ($department) {
                return $query->where('site_id', $department->site_id);
            })],
        ]);

        $department->update($validated);

        return back()->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $master_department)
    {
        $master_department->delete();
        return redirect()->route('master-departments.index')->with('success', 'Department deleted successfully.');
    }
}
