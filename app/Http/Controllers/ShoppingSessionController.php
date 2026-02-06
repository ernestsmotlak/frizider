<?php

namespace App\Http\Controllers;

use App\Models\GroceryList;
use Illuminate\Http\Request;

class ShoppingSessionController extends Controller
{
    //
    public function createShoppingSession(Request $request)
    {
        $validated = $request->validate([
            'grocery_list_id' => 'required|exists:grocery_lists,id',
        ]);

        $groceryList = GroceryList::find($validated['grocery_list_id']);

        if (!$groceryList) {
            return response()->json([
                'message' => 'Grocery list not found',
            ]);
        }


    }
}
