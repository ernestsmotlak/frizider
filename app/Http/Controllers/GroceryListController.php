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
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'searchTerm' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:completed,unfinished',
        ]);

        $perPage = (int)($validated['per_page'] ?? 10);
        $searchTerm = trim((string)($validated['searchTerm'] ?? ''));
        $status = $validated['status'] ?? null;

        $query = GroceryList::query()
            ->where('user_id', auth()->id())
            ->when($searchTerm !== '', function ($q) use ($searchTerm) {
                $escaped = addcslashes($searchTerm, "\\%_");
                $like = "%{$escaped}%";

                $q->where(function ($inner) use ($like) {
                    $inner
                        ->where('name', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhereHas('groceryListItems', function ($itemsQuery) use ($like) {
                            $itemsQuery
                                ->where('name', 'like', $like)
                                ->orWhere('notes', 'like', $like);
                        });
                });
            })
            ->when($status === 'completed', function ($q) {
                $q->whereNotNull('completed_at');
            })
            ->when($status === 'unfinished', function ($q) {
                $q->whereNull('completed_at');
            })
            ->orderByDesc('created_at');

        $countQuery = clone $query;
        $userLists = $query->simplePaginate($perPage);
        $total = $countQuery->count();

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

    public function updateItems(Request $request, GroceryList $groceryList)
    {
        if ($groceryList->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Forbidden.'
            ], 403);
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:grocery_list_items,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($groceryList, $validated) {
            foreach ($validated['items'] as $item) {
                GroceryListItem::where('id', $item['id'])
                    ->where('grocery_list_id', $groceryList->id)
                    ->update(['sort_order' => $item['sort_order']]);
            }
        });

        return response()->json([
            'message' => 'Items order updated.',
            'data' => $groceryList->fresh()->load('groceryListItems'),
        ]);
    }

    public function saveShoppingSession(Request $request)
    {
        $validated = $request->validate([
            'grocery_list_ids' => 'required|array',
            'grocery_list_ids.*' => 'required|integer|exists:grocery_lists,id',
        ]);

        $listIds = $validated['grocery_list_ids'];

        // Verify all lists belong to the authenticated user
        $userLists = GroceryList::where('user_id', auth()->id())
            ->whereIn('id', $listIds)
            ->pluck('id')
            ->toArray();

        if (count($userLists) !== count($listIds)) {
            return response()->json([
                'message' => 'Some lists do not belong to you or do not exist.',
            ], 403);
        }

        // Save to session
        session(['shopping_selected_lists' => $userLists]);

        return response()->json([
            'message' => 'Shopping session saved.',
            'data' => ['grocery_list_ids' => $userLists],
        ]);
    }

    public function getShoppingSession()
    {
        $listIds = session('shopping_selected_lists', []);

        if (empty($listIds)) {
            return response()->json([
                'data' => [
                    'grocery_list_ids' => [],
                    'grocery_lists' => [],
                ],
            ]);
        }

        // Validate that all list IDs belong to the authenticated user
        $userLists = GroceryList::where('user_id', auth()->id())
            ->whereIn('id', $listIds)
            ->pluck('id')
            ->toArray();

        // Only return lists that the user actually owns
        $validListIds = array_values($userLists);

        // Fetch full lists with items
        $groceryLists = GroceryList::where('user_id', auth()->id())
            ->whereIn('id', $validListIds)
            ->with('groceryListItems')
            ->get();

        return response()->json([
            'data' => [
                'grocery_list_ids' => $validListIds,
                'grocery_lists' => $groceryLists,
            ],
        ]);
    }
}
