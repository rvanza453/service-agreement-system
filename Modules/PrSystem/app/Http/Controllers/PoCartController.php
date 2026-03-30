<?php

namespace Modules\PrSystem\Http\Controllers;

use Modules\PrSystem\Models\PrItem;
use Illuminate\Http\Request;

class PoCartController extends Controller
{
    public function index()
    {
        $cart = session()->get('po_cart', []);
        
        if (empty($cart)) {
            $items = collect([]);
        } else {
            // Load items with PR details
            $items = PrItem::with(['purchaseRequest', 'product'])
                ->whereIn('id', $cart)
                ->whereHas('purchaseRequest', function($q) {
                    $q->where('status', 'Approved');
                })
                ->get();
        }

        return view('prsystem::po.cart', compact('items'));
    }



    public function getData()
    {
        $cart = session()->get('po_cart', []);
        
        if (empty($cart)) {
            return response()->json(['count' => 0, 'items' => []]);
        }

        $items = PrItem::with(['purchaseRequest', 'product'])
            ->whereIn('id', $cart)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'pr_number' => $item->purchaseRequest->pr_number,
                    'item_name' => $item->item_name,
                    'specification' => $item->specification,
                    'quantity' => $item->final_quantity,
                    'unit' => $item->unit,
                    'price' => $item->price_estimation, // estimation or unit_price?
                ];
            });

        return response()->json([
            'count' => count($cart),
            'items' => $items
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'exists:pr_items,id',
        ]);

        $currentCart = session()->get('po_cart', []);
        $newItems = $request->items;

        // Check for expired PRs (Max 14 days after approval)
        $expiredItems = PrItem::with('purchaseRequest')
            ->whereIn('id', $newItems)
            ->get()
            ->filter(function($item) {
                return $item->purchaseRequest->isExpired(); // Uses method added to Model
            });

        if ($expiredItems->isNotEmpty()) {
            $expiredPrs = $expiredItems->pluck('purchaseRequest.pr_number')->unique()->join(', ');
            $msg = 'Item dari PR berikut sudah kadaluarsa (lebih dari 14 hari sejak disetujui) dan tidak dapat dibuatkan PO: ' . $expiredPrs;
            
            if ($request->wantsJson()) {
                return response()->json(['error' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }
        
        // Merge and unique
        $updatedCart = array_unique(array_merge($currentCart, $newItems));
        
        session()->put('po_cart', $updatedCart);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => count($newItems) . ' item ditambahkan',
                'count' => count($updatedCart)
            ]);
        }

        return redirect()->back()->with('success', count($newItems) . ' item berhasil ditambahkan ke Keranjang PO.');
    }

    public function remove(Request $request)
    {
        $request->validate([
            'pr_item_id' => 'required',
        ]);

        $cart = session()->get('po_cart', []);
        
        // Remove item
        $cart = array_diff($cart, [$request->pr_item_id]);
        
        session()->put('po_cart', array_values($cart));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item dihapus',
                'count' => count($cart)
            ]);
        }

        return redirect()->route('po.cart')->with('success', 'Item dihapus dari keranjang.');
    }

    public function clear(Request $request)
    {
        session()->forget('po_cart');
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Keranjang dikosongkan',
                'count' => 0
            ]);
        }
        
        return redirect()->route('po.cart')->with('success', 'Keranjang dikosongkan.');
    }
}
