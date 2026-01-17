<?php

namespace App\Http\Controllers;

use App\Models\GroceryList;
use App\Models\GroceryListItem;
use Illuminate\Http\Request;

class GroceryListItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'grocery_list_id' => 'required|integer|exists:grocery_lists,id',
        ]);

        $groceryList = GroceryList::where('id', $validated['grocery_list_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $groceryListItems = $groceryList->groceryListItems()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $groceryListItems
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'grocery_list_id' => 'required|exists:grocery_lists,id',
            'pantry_item_id' => 'nullable|exists:pantry_items,id',
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_purchased' => 'nullable|boolean',
        ]);

        $groceryList = GroceryList::where('user_id', auth()->id())
            ->findOrFail($validated['grocery_list_id']);

        $item = $groceryList->groceryListItems()->create($validated);

        return response()->json([
            'message' => 'Grocery list item created.',
            'data' => $item,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $groceryListItem = GroceryListItem::whereHas('groceryList', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->findOrFail($id);

        return response()->json([
            'data' => $groceryListItem
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $groceryListItem = GroceryListItem::whereHas('groceryList', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->findOrFail($id);

        $validated = $request->validate([
            'pantry_item_id' => 'nullable|exists:pantry_items,id',
            'name' => 'sometimes|string|max:255',
            'quantity' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_purchased' => 'nullable|boolean',
        ]);

        $groceryListItem->update($validated);

        $groceryList = $groceryListItem->groceryList;

        return response()->json([
            'message' => 'Grocery list item updated.',
            'data' => $groceryList->fresh()->load('groceryListItems')
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $groceryListItem = GroceryListItem::whereHas('groceryList', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->findOrFail($id);

        $groceryListItem->delete();

        return response()->json([
            'message' => 'Grocery list item deleted.'
        ]);
    }
}
