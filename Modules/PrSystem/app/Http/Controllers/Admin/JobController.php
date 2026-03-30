<?php

namespace Modules\PrSystem\Http\Controllers\Admin;

use Modules\PrSystem\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PrSystem\Models\Site;
use Modules\PrSystem\Models\Job;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Ambil nilai per_page dari input, default ke 10 jika tidak ada
        $perPage = $request->input('per_page', 10);
        
        $sites = Site::all();
        $query = Job::with('site','department');
    
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }
    
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }
    
        // 2. Gunakan variabel $perPage di dalam paginate
        $jobs = $query->orderBy('site_id')->orderBy('department_id')->orderBy('name')->paginate($perPage);
    
        // 3. Tambahkan append agar parameter pencarian & per_page tidak hilang saat pindah halaman
        $jobs->appends($request->all());
    
        return view('prsystem::admin.jobs.index', compact('jobs', 'sites'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sites = \Modules\PrSystem\Models\Site::orderBy('name')->get();
        return view('prsystem::admin.jobs.create', compact('sites'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'department_id' => 'nullable|exists:departments,id',
            'code' => 'required|string',
            'name' => 'required|string',
        ]);

        Job::create($validated);

        return redirect()->route('jobs.index')->with('success', 'Job created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Job $job)
    {
        $sites = \Modules\PrSystem\Models\Site::orderBy('name')->get();
        return view('prsystem::admin.jobs.edit', compact('job', 'sites'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Job $job)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'department_id' => 'nullable', // Allow whatever comes in for now
        ]);
        if (empty($validated['department_id'])) {
            $validated['department_id'] = null;
        }
        $job->update($validated);
    
        return redirect()->route('jobs.index', ['site_id' => $job->site_id])
            ->with('success', 'Job updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job $job)
    {
        $job->delete();
        return redirect()->back()->with('success', 'Job deleted successfully.');
    }
}
