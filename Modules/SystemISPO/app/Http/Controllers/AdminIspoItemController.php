<?php

namespace Modules\SystemISPO\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\SystemISPO\App\Models\IspoItem;
use Illuminate\Http\Request;

class AdminIspoItemController extends Controller
{
    public function index()
    {
        // Fetch principles (roots) with their full hierarchy
        $principles = IspoItem::where('type', 'principle')
            ->orderBy('order_index')
            ->with(['children' => function($q) {
                $q->orderBy('order_index')->with(['children' => function($q) {
                    $q->orderBy('order_index')->with(['children' => function($q) {
                        $q->orderBy('order_index')->with(['children' => function($q) {
                            $q->orderBy('order_index');
                        }]);
                    }]);
                }]);
            }])
            ->get();

        return view('systemispo::ispo.admin.items.index', compact('principles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:ispo_items,id',
            'type' => 'required|in:principle,criteria,indicator,parameter,verifier',
            'code' => 'nullable|string|max:50',
            'name' => 'required|string',
            'order_index' => 'nullable|integer|min:0',
        ]);

        // Auto-calculate order_index if not provided
        if (!isset($validated['order_index'])) {
            $maxIndex = IspoItem::where('parent_id', $validated['parent_id'])
                                ->where('type', $validated['type'])
                                ->max('order_index');
            $validated['order_index'] = $maxIndex !== null ? $maxIndex + 1 : 1;
        }

        IspoItem::create($validated);

        return redirect()->route('ispo.admin.items.index')->with('success', 'Item created successfully.');
    }

    public function update(Request $request, IspoItem $item)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'order_index' => 'nullable|integer|min:0',
        ]);

        $item->update($validated);

        return redirect()->route('ispo.admin.items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(IspoItem $item)
    {
        // Optional: Check if it has children or entries before deleting
        // For now, strict delete
        $item->delete();
        return redirect()->route('ispo.admin.items.index')->with('success', 'Item deleted successfully.');
    }
}
