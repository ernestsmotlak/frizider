<?php

namespace App\Http\Controllers;

use App\Models\ShoppingItem;
use App\Models\ShoppingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShoppingItemController extends Controller
{
    public function update(Request $request, ShoppingItem $shoppingItem)
    {
        $session = ShoppingSession::where('user_id', auth()->id())->first();

        if (!$session || $shoppingItem->shopping_session_id !== $session->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'quantity' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'is_purchased' => 'nullable|boolean',
        ]);

        $shoppingItem->update($validated);

        return response()->json([
            'message' => 'Shopping item updated.',
            'data' => $shoppingItem->fresh()->load('groceryListItem.groceryList'),
        ]);
    }

    public function updateOrder(Request $request)
    {
        $session = ShoppingSession::where('user_id', auth()->id())->first();

        if (!$session) {
            return response()->json([
                'message' => 'No shopping session found.',
            ], 404);
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:shopping_items,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($session, $validated) {
            foreach ($validated['items'] as $item) {
                ShoppingItem::where('id', $item['id'])
                    ->where('shopping_session_id', $session->id)
                    ->update(['sort_order' => $item['sort_order']]);
            }
        });

        $updatedItems = ShoppingItem::where('shopping_session_id', $session->id)
            ->with('groceryListItem.groceryList')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'message' => 'Order updated.',
            'data' => $updatedItems,
        ]);
    }
}
