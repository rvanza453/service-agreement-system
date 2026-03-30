<?php

namespace Modules\PrSystem\Http\Controllers\Admin;

use Modules\PrSystem\Http\Controllers\Controller;
use Modules\PrSystem\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::orderBy('name')->get();
        return view('prsystem::admin.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('prsystem::admin.vendors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:Pernah Transaksi,Belum Transaksi,Di Ajukan',
        ]);

        Vendor::create($request->all());

        return redirect()->route('vendors.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Vendor $vendor)
    {
        return view('prsystem::admin.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:Pernah Transaksi,Belum Transaksi,Di Ajukan',
        ]);

        $vendor->update($request->all());

        return redirect()->route('vendors.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('vendors.index')->with('success', 'Supplier berhasil dihapus.');
    }
}
