<?php

namespace Modules\PrSystem\Http\Controllers;

use Illuminate\Http\Request;

class CapexConfigController extends Controller
{
    public function index()
    {
        $departments = \Modules\PrSystem\Models\Department::with(['site', 'capexConfigs.approver'])
                        ->orderBy('name')
                        ->get()
                        ->groupBy(function($dept) {
                            return $dept->site->name ?? 'No Site';
                        });
                        
        return view('prsystem::admin.capex.config.index', compact('departments'));
    }

    public function edit(\Modules\PrSystem\Models\Department $department)
    {
        // Ensure 5 columns exist for this department
        for ($i = 1; $i <= 5; $i++) {
            \Modules\PrSystem\Models\CapexColumnConfig::firstOrCreate(
                [
                    'department_id' => $department->id,
                    'column_index' => $i
                ],
                [
                    'label' => 'Step ' . $i,
                    'is_digital' => true
                ]
            );
        }
        
        $configs = \Modules\PrSystem\Models\CapexColumnConfig::where('department_id', $department->id)
                    ->where('column_index', '<=', 5)
                    ->orderBy('column_index')
                    ->get();

        $users = \Modules\PrSystem\Models\User::orderBy('name')->get();

        return view('prsystem::admin.capex.config.edit', compact('department', 'configs', 'users'));
    }

    public function update(Request $request, \Modules\PrSystem\Models\Department $department)
    {
        $validated = $request->validate([
            'configs' => 'array',
            'configs.*.id' => 'required|exists:capex_column_configs,id',
            'configs.*.label' => 'required|string|max:50',
            'configs.*.approver_user_id' => 'required|exists:users,id',
        ]);

        foreach ($validated['configs'] as $configData) {
            $config = \Modules\PrSystem\Models\CapexColumnConfig::find($configData['id']);
            if($config->department_id == $department->id) {
                 // Logic check: if digital, must have role or user?? 
                 // Frontend validation or simple check here.
                 // We just save as is.
                 $config->update([
                     'label' => $configData['label'],
                     'approver_role' => null,
                     'approver_user_id' => $configData['approver_user_id'],
                 ]);
            }
        }

        return redirect()->route('admin.capex.config.index')->with('success', 'Capex Configuration for ' . $department->name . ' updated successfully.');
    }
}
