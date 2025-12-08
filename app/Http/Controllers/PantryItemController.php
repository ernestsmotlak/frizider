<?php

namespace App\Http\Controllers;

use App\Models\PantryItem;
use App\Models\SpaceStorage;
use Illuminate\Http\Request;

class PantryItemController extends Controller
{
    protected function user()
    {
        return auth('api')->user();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pantryItems = PantryItem::where('user_id', $this->user()->id)
            ->orderBy('expiry_date')
            ->get();

        return response()->json([
            'data' => $pantryItems
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
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
            if ($space && $space->user_id !== $this->user()->id) {
                return response()->json([
                    'message' => 'You do not have permission to use this storage space.',
                ], 403);
            }
        }

        $validated['user_id'] = $this->user()->id;

        $pantryItem = PantryItem::create($validated);

        return response()->json([
            'message' => 'Pantry item created.',
            'data' => $pantryItem->fresh(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PantryItem $pantryItem)
    {
        if ($pantryItem->user_id !== $this->user()->id) {
            return response()->json([
                'message' => 'You do not have permission to view this pantry item.'
            ], 403);
        }
        return response()->json([
            'data' => $pantryItem
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PantryItem $pantryItem)
    {
        if ($pantryItem->user_id !== $this->user()->id) {
            return response()->json([
                'message' => 'You do not have permission to update this pantry item.',
            ], 403);
        }

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
            if ($space && $space->user_id !== $this->user()->id) {
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PantryItem $pantryItem)
    {
        if ($pantryItem->user_id !== $this->user()->id) {
            return response()->json([
                'message' => 'You do not have permission to delete this pantry item.',
            ], 403);
        }

        $pantryItem->delete();

        return response()->json([
            'message' => 'Pantry item successfully deleted'
        ]);
    }
}
