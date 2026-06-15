<?php

namespace App\Http\Controllers;

use App\Services\ItemConversionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemConversionController extends Controller
{
    public function __construct(protected ItemConversionService $conversionService)
    {
    }

    /**
     * Convert shopping items, grocery list items, or recipe ingredients into pantry items.
     */
    public function toPantryItem(Request $request)
    {
        $validated = $request->validate([
            'source_type' => ['required', Rule::in(['shopping_item', 'grocery_list_item', 'recipe_ingredient'])],
            'source_ids' => ['required', 'array', 'min:1'],
            'source_ids.*' => ['integer'],
            'space_id' => ['required', 'integer', Rule::exists('space_storages', 'id')->where('user_id', auth()->id())],
            'expiry_date' => ['nullable', 'date'],
        ]);

        $pantryItems = $this->conversionService->convertToPantryItem(
            $validated['source_type'],
            $validated['source_ids'],
            auth()->id(),
            $validated['space_id'],
            $validated['expiry_date'] ?? null,
        );

        return response()->json([
            'message' => 'Items converted to pantry items.',
            'data' => $pantryItems,
        ], 201);
    }

    /**
     * Convert pantry items or recipe ingredients into grocery list items.
     */
    public function toGroceryListItem(Request $request)
    {
        $validated = $request->validate([
            'source_type' => ['required', Rule::in(['pantry_item', 'recipe_ingredient'])],
            'source_ids' => ['required', 'array', 'min:1'],
            'source_ids.*' => ['integer'],
            'grocery_list_id' => ['required', 'integer', Rule::exists('grocery_lists', 'id')->where('user_id', auth()->id())],
        ]);

        $groceryListItems = $this->conversionService->convertToGroceryListItem(
            $validated['source_type'],
            $validated['source_ids'],
            auth()->id(),
            $validated['grocery_list_id'],
        );

        return response()->json([
            'message' => 'Items converted to grocery list items.',
            'data' => $groceryListItems,
        ], 201);
    }

    /**
     * Convert pantry items, shopping items, or grocery list items into recipe ingredients.
     */
    public function toRecipeIngredient(Request $request)
    {
        $validated = $request->validate([
            'source_type' => ['required', Rule::in(['pantry_item', 'shopping_item', 'grocery_list_item'])],
            'source_ids' => ['required', 'array', 'min:1'],
            'source_ids.*' => ['integer'],
            'recipe_id' => ['required', 'integer', Rule::exists('recipes', 'id')->where('user_id', auth()->id())],
        ]);

        $recipeIngredients = $this->conversionService->convertToRecipeIngredient(
            $validated['source_type'],
            $validated['source_ids'],
            auth()->id(),
            $validated['recipe_id'],
        );

        return response()->json([
            'message' => 'Items converted to recipe ingredients.',
            'data' => $recipeIngredients,
        ], 201);
    }
}
