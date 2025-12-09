<?php

namespace App\Http\Controllers;

use App\Models\PantryItem;
use App\Models\SpaceStorage;
use Illuminate\Http\Request;

class PantryItemController extends Controller
{
    public function index()
    {
        $pantryItems = PantryItem::where('user_id', auth()->id())
            ->orderBy('expiry_date')
            ->get();

        return response()->json([
            'data' => $pantryItems
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'space_id' => 'nullable|exists:space_storages,id',
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'expiry_date' => 'nullable|date',
            'purchase_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['space_id'])) {
            $space = SpaceStorage::find($validated['space_id']);
            if ($space && $space->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'You do not have permission to use this storage space.',
                ], 403);
            }
        }

        $validated['user_id'] = auth()->id();

        $pantryItem = PantryItem::create($validated);

        return response()->json([
            'message' => 'Pantry item created.',
            'data' => $pantryItem->fresh(),
        ], 201);
    }

    public function show(string $id)
    {
        $pantryItem = PantryItem::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'data' => $pantryItem
        ]);
    }

    public function update(Request $request, string $id)
    {
        $pantryItem = PantryItem::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'space_id' => 'nullable|exists:space_storages,id',
            'name' => 'sometimes|string|max:255',
            'quantity' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'expiry_date' => 'nullable|date',
            'purchase_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['space_id'])) {
            $space = SpaceStorage::find($validated['space_id']);
            if ($space && $space->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'You do not have permission to use this storage space.',
                ], 403);
            }
        }

        $pantryItem->update($validated);

        return response()->json([
            'message' => 'Pantry item successfully updated.',
            'data' => $pantryItem->fresh()
        ]);
    }

    public function destroy(string $id)
    {
        $pantryItem = PantryItem::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $pantryItem->delete();

        return response()->json([
            'message' => 'Pantry item successfully deleted'
        ]);
    }
}
