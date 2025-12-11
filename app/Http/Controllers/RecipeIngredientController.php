<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use Illuminate\Http\Request;

class RecipeIngredientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $recipeId = $request->query('recipe_id');

        if (!$recipeId) {
            return response()->json([
                'message' => 'Recipe id is required.'
            ], 422);
        }

        $recipe = Recipe::findOrFail($recipeId);

        if ($recipe->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'You do not have permission to view these ingredients.',
            ], 403);
        }

        $ingredients = RecipeIngredient::where('recipe_id', $recipeId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $ingredients
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
        ]);

        $recipe = Recipe::findOrFail($validated['recipe_id']);

        if ($recipe->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'You do not have permission to add ingredients to this recipe.',
            ], 403);
        }

        $ingredient = RecipeIngredient::create($validated);

        return response()->json([
            'message' => 'Ingredient successfully created.',
            'data' => $ingredient->fresh()
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ingredient = RecipeIngredient::findOrFail($id);

        if ($ingredient->recipe->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Forbidden.'
            ], 403);
        }

        return response()->json([
            'data' => $ingredient
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ingredientToUpdate = RecipeIngredient::findOrFail($id);

        if ($ingredientToUpdate->recipe->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Forbidden.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'quantity' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
        ]);

        $ingredientToUpdate->update($validated);

        return response()->json([
            'message' => 'Ingredient updated.',
            'data' => $ingredientToUpdate->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ingredientToDelete = RecipeIngredient::findOrFail($id);

        if ($ingredientToDelete->recipe->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Forbidden.'
            ], 403);
        }

        $ingredientToDelete->delete();

        return response()->json([
            'message' => 'Ingredient deleted successfully.'
        ]);
    }
}
