<?php

namespace Modules\ServiceAgreementSystem\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\ServiceAgreementSystem\Models\UspkApprovalSchema;
use Modules\ServiceAgreementSystem\Models\Department;
use App\Models\User;

class UspkApprovalSchemaController extends Controller
{
    public function index()
    {
        $schemas = UspkApprovalSchema::with(['departments', 'steps.user'])->get();
        return view('serviceagreementsystem::approval-schema.index', compact('schemas'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        // Hanya ambil user dengan role approver/yang relevan, atau semua user aktif
        $users = User::orderBy('name')->get(); 
        
        return view('serviceagreementsystem::approval-schema.create', compact('departments', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'departments' => 'required|array',
            'departments.*' => 'exists:departments,id',
            'steps' => 'required|array|min:1',
            'steps.*.level' => 'required|integer|min:1',
            'steps.*.user_id' => 'required|exists:users,id',
        ]);

        DB::transaction(function () use ($validated) {
            $schema = UspkApprovalSchema::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $schema->departments()->sync($validated['departments']);

            foreach ($validated['steps'] as $step) {
                $schema->steps()->create([
                    'level' => $step['level'],
                    'user_id' => $step['user_id'],
                ]);
            }
        });

        return redirect()->route('sas.approval-schemas.index')->with('success', 'Skema Approval berhasil dibuat.');
    }

    public function edit(UspkApprovalSchema $approval_schema)
    {
        $approval_schema->load(['departments', 'steps']);
        $departments = Department::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('serviceagreementsystem::approval-schema.edit', compact('approval_schema', 'departments', 'users'));
    }

    public function update(Request $request, UspkApprovalSchema $approval_schema)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'departments' => 'required|array',
            'departments.*' => 'exists:departments,id',
            'steps' => 'required|array|min:1',
            'steps.*.level' => 'required|integer|min:1',
            'steps.*.user_id' => 'required|exists:users,id',
        ]);

        DB::transaction(function () use ($approval_schema, $validated) {
            $approval_schema->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'is_active' => $validated['is_active'] ?? false,
            ]);

            $approval_schema->departments()->sync($validated['departments']);

            $approval_schema->steps()->delete();
            foreach ($validated['steps'] as $step) {
                $approval_schema->steps()->create([
                    'level' => $step['level'],
                    'user_id' => $step['user_id'],
                ]);
            }
        });

        return redirect()->route('sas.approval-schemas.index')->with('success', 'Skema Approval berhasil diperbarui.');
    }

    public function destroy(UspkApprovalSchema $approval_schema)
    {
        $approval_schema->delete();
        return redirect()->route('sas.approval-schemas.index')->with('success', 'Skema Approval berhasil dihapus.');
    }
}
