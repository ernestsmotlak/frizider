<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginateRecipes(Request $request)
    {
        $userRecipes = Recipe::select([
            'id',
            'name',
            'description',
            'image_url'
        ])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->simplePaginate(
                $request->integer('per_page', 10)
            );

        return response()->json(['data' => $userRecipes]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'servings' => 'nullable|integer|min:1',
            'prep_time' => 'nullable|integer|min:0',
            'cook_time' => 'nullable|integer|min:0',
            'image_url' => 'nullable|string|max:500'
        ]);

        $validated['user_id'] = auth()->id();

        $recipe = Recipe::create($validated);

        return response()->json([
            'message' => 'Recipe created.',
            'data' => $recipe->fresh()
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $recipe = Recipe::with('recipeIngredients')
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json(['data' => $recipe]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $recipeToUpdate = Recipe::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'servings' => 'nullable|integer|min:1',
            'prep_time' => 'nullable|integer|min:0',
            'cook_time' => 'nullable|integer|min:0',
            'image_url' => 'nullable|string|max:500',
        ]);

        $recipeToUpdate->update($validated);

        return response()->json([
            'message' => 'Recipe updated.',
            'data' => $recipeToUpdate->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $recipeToDestroy = Recipe::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $recipeToDestroy->delete();

        return response()->json(['message' => 'Recipe successfully deleted.']);
    }

    public function updateIngredients(Request $request, $recipeId)
    {
        $recipe = Recipe::findOrFail($recipeId);

        if ($recipe->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Not authorized.'
            ], 403);
        }

        $validated = $request->validate([
            'ingredients' => 'required|array',
            'ingredients.*.name' => 'required|string|max:255',
            'ingredients.*.quantity' => 'nullable|numeric',
            'ingredients.*.unit' => 'nullable|string|max:50',
            'ingredients.*.notes' => 'nullable|string|max:500',
            'ingredients.*.sort_order' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($recipe, $validated) {
            $recipe->recipeIngredients()->delete();

            foreach ($validated['ingredients'] as $index => $ingredientData) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'name' => $ingredientData['name'],
                    'quantity' => $ingredientData['quantity'] ?? null,
                    'unit' => $ingredientData['unit'] ?? null,
                    'notes' => $ingredientData['notes'] ?? null,
                    'sort_order' => $ingredientData['sort_order'] ?? $index,
                ]);
            }
        });

        return response()->json([
            'message' => 'Ingredients updated.',
            'data' => $recipe->fresh()->load('recipeIngredients')
        ]);
    }
}
