<?php

namespace Modules\PrSystem\Http\Controllers;

use Illuminate\Http\Request;

class CapexAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assets = \Modules\PrSystem\Models\CapexAsset::latest()->get();
        return view('prsystem::admin.capex.assets.index', compact('assets'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        \Modules\PrSystem\Models\CapexAsset::create($validated);

        return redirect()->route('admin.capex.assets.index')->with('success', 'Asset Created Successfully');
    }

    public function update(\Illuminate\Http\Request $request, \Modules\PrSystem\Models\CapexAsset $asset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $asset->update($validated);

        return redirect()->route('admin.capex.assets.index')->with('success', 'Asset Updated Successfully');
    }

    public function destroy(\Modules\PrSystem\Models\CapexAsset $asset)
    {
        $asset->delete();
        return redirect()->route('admin.capex.assets.index')->with('success', 'Asset Deleted Successfully');
    }
}
