<?php

namespace App\Http\Controllers;

use App\Models\GroceryList;
use App\Models\GroceryListItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroceryListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groceryLists = GroceryList::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $groceryLists
        ]);
    }

    public function paginateGroceryLists(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $query = GroceryList::query()
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at');

        $userLists = $query->simplePaginate($perPage);
        $total = (clone $query)->count();

        return response()->json([
            'data' => $userLists,
            'allRecipes' => $total, // consider renaming to 'total'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'image_url' => 'nullable|string|max:500',
            'completed_at' => 'nullable|date',
        ]);

        $validated['user_id'] = auth()->id();

        $groceryList = GroceryList::create($validated);

        $groceryList->load('groceryListItems');

        return response()->json([
            'message' => 'Grocery list created.',
            'data' => $groceryList,
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(GroceryList $groceryList)
    {
        if ($groceryList->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Forbidden.'
            ], 403);
        }

        $groceryList->load('groceryListItems');

        return response()->json([
            'data' => $groceryList
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GroceryList $groceryList)
    {
        if ($groceryList->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Forbidden.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'notes' => 'nullable|string',
            'image_url' => 'nullable|string|max:500',
            'completed_at' => 'nullable|date',
        ]);

        $groceryList->update($validated);

        $groceryList->load('groceryListItems');

        return response()->json([
            'message' => 'Grocery list updated.',
            'data' => $groceryList
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GroceryList $groceryList)
    {
        if ($groceryList->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Forbidden.'
            ], 403);
        }

        $groceryList->delete();

        return response()->json([
            'message' => 'Grocery list deleted.'
        ]);
    }

    public function saveGroceryListWithData(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'image_url' => 'nullable|string|max:500',
            'items' => 'nullable|array',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.notes' => 'nullable|string|max:500',
            'items.*.sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['user_id'] = auth()->id();

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        DB::beginTransaction();
        try {
            $groceryList = GroceryList::create($validated);

            foreach ($items as $index => $item) {
                GroceryListItem::create([
                    'grocery_list_id' => $groceryList->id,
                    'name' => $item['name'],
                    'quantity' => $item['quantity'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'notes' => $item['notes'] ?? null,
                    'sort_order' => $item['sort_order'] ?? $index,
                    'is_purchased' => false,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Grocery list created.',
                'data' => $groceryList->fresh()->load('groceryListItems')
            ], 201);
        } catch (\Throwable $error) {
            DB::rollBack();
            report($error);
            return response()->json([
                'message' => 'Failed to create grocery list.',
                'error' => $error->getMessage()
            ], 500);
        }
    }
}
