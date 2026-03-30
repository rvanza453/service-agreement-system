<?php

namespace Modules\PrSystem\Http\Controllers\Admin;

use Modules\PrSystem\Http\Controllers\Controller;
use Modules\PrSystem\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::withCount('departments')->orderBy('name')->get();
        return view('prsystem::admin.sites.index', compact('sites'));
    }

    public function create()
    {
        return view('prsystem::admin.sites.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255|unique:sites,name',
            'code'  => 'required|string|max:20|unique:sites,code',
        ]);

        Site::create($validated);

        return redirect()->route('sites.index')->with('success', 'Site berhasil ditambahkan.');
    }

    public function edit(Site $site)
    {
        return view('prsystem::admin.sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('sites', 'name')->ignore($site->id)],
            'code' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('sites', 'code')->ignore($site->id)],
        ]);

        $site->update($validated);

        return redirect()->route('sites.index')->with('success', 'Site berhasil diperbarui.');
    }

    public function destroy(Site $site)
    {
        $site->delete();
        return redirect()->route('sites.index')->with('success', 'Site berhasil dihapus.');
    }
}
