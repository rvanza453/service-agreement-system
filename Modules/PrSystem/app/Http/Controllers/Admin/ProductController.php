<?php

namespace Modules\PrSystem\Http\Controllers\Admin;

use Modules\PrSystem\Http\Controllers\Controller;
use Modules\PrSystem\Models\Product;
use Modules\PrSystem\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data Site untuk dropdown filter
        $sites = Site::orderBy('name')->get();

        // Mulai Query Product dengan Eager Loading 'sites'
        $query = Product::with('sites');

        if ($request->filled('site_id')) {
            $siteId = $request->site_id;
            
            if ($siteId === 'non_active') {
                // Cari produk yang BELUM punya relasi ke site manapun
                $query->doesntHave('sites');
            } else {
                // Cari produk yang memiliki relasi dengan site_id tertentu
                $query->whereHas('sites', function($q) use ($siteId) {
                    $q->where('sites.id', $siteId);
                });
            }
        }

        // 2. Logika Search (Opsional tapi sangat berguna)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Ambil data (gunakan pagination agar halaman tidak berat)
        $products = $query->orderBy('name')->paginate(10);

        // Append query string agar filter tidak hilang saat pindah halaman
        $products->appends($request->all());

        return view('prsystem::admin.products.index', compact('products', 'sites'));
    }

    public function create()
    {
        $sites = \Modules\PrSystem\Models\Site::all();
        $categories = config('options.product_categories', ['Sparepart', 'Consumable']);
        
        return view('prsystem::admin.products.create', compact('sites','categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:products,code|max:50',
            'sites' => 'required|array',
            'sites.*' => 'exists:sites,id',
            'unit' => 'required|string|max:50',
            'category' => ['required', Rule::in(config('options.product_categories'))],
            'min_stock' => 'integer|min:0',
        ]);

        $product = Product::create($request->except('sites'));

        $product->sites()->sync($request->sites);
    
        return redirect()->route('products.index')->with('success', 'Produk berhasil dibuat.');
    }

    public function edit(Product $product)
    {
        $sites = \Modules\PrSystem\Models\Site::all();
        $product->load('sites'); 
        
        $categories = config('options.product_categories', ['Sparepart', 'Consumable']);
        
        return view('prsystem::admin.products.edit', compact('product', 'sites','categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sites' => 'array',
            'code' => ['required', 'string', 'max:50', Rule::unique('products')->ignore($product->id)],
            'unit' => 'required|string|max:50',
            'category' => ['required', Rule::in(config('options.product_categories'))],
            'min_stock' => 'integer|min:0',
        ]);

        $product->update($request->except('sites'));
    
        if ($request->has('sites')) {
            $product->sites()->sync($request->sites);
        } else {
            $product->sites()->detach();
        }
    
        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product)
    {
        // Verify password
        $password = $request->input('admin_password');
        if ($password !== config('prsystem.app.admin_verification_password', config('app.admin_verification_password'))) {
            return back()->with('error', 'Password verifikasi salah!');
        }

        $name = $product->name;
        $product->delete();
        \Modules\PrSystem\Helpers\ActivityLogger::log('deleted', 'Deleted Product: ' . $name);
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function export()
    {
        $fileName = 'products_export_' . date('Y-m-d_H-i-s') . '.csv';
        $products = Product::orderBy('name')->get();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('ID', 'Code', 'Name', 'Unit', 'Category', 'Price Estimation', 'Min Stock', 'Created At');

        $callback = function() use($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($products as $product) {
                $row['ID']  = $product->id;
                $row['Code']    = $product->code;
                $row['Name']    = $product->name;
                $row['Unit']  = $product->unit;
                $row['Category']  = $product->category;
                $row['Price Estimation']  = $product->price_estimation;
                $row['Min Stock']  = $product->min_stock;
                $row['Created At']  = $product->created_at;

                fputcsv($file, array($row['ID'], $row['Code'], $row['Name'], $row['Unit'], $row['Category'], $row['Price Estimation'], $row['Min Stock'], $row['Created At']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
