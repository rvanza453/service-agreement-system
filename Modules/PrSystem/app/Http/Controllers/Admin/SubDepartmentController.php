<?php

namespace Modules\PrSystem\Http\Controllers\Admin;

use Modules\PrSystem\Http\Controllers\Controller;
use Modules\PrSystem\Models\SubDepartment;
use Modules\PrSystem\Models\Department;
use Illuminate\Http\Request;

class SubDepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subDepartments = SubDepartment::with('department')->orderBy('name')->get();
        return view('prsystem::admin.sub_departments.index', compact('subDepartments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('prsystem::admin.sub_departments.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'coa' => 'nullable|string|max:50',
        ]);

        SubDepartment::create($request->all());

        if ($request->has('redirect_back')) {
            return back()->with('success', 'Sub Department created successfully.');
        }

        return back()
            ->with('success', 'Sub Department created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubDepartment $subDepartment)
    {
        $departments = Department::orderBy('name')->get();
        return view('prsystem::admin.sub_departments.edit', compact('subDepartment', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubDepartment $subDepartment)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'coa' => 'nullable|string|max:50',
        ]);

        $subDepartment->update($request->all());

        return back()
            ->with('success', 'Sub Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubDepartment $subDepartment)
    {
        $subDepartment->delete();
        return back()->with('success', 'Sub Department deleted successfully.');
    }
}
