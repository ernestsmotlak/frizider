<?php

namespace App\Http\Controllers;

use App\Models\GroceryListItem;
use Illuminate\Http\Request;

class GroceryListItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $groceryListId = GroceryListItem::query('grocery_list_id');

        if (!$groceryListId) {
            return response()->json([
                'message' => 'Grocery list id is required.'
            ], 422);
        }

        $groceryList = GroceryList::findOrFail('grocery_list_id', $groceryListId);

        if ($groceryList->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Forbidden.'
            ], 403);
        }

        $groceryListItems = $groceryList->groceries()->get();

        return response()->json([
            'data' => $groceryListItems
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
