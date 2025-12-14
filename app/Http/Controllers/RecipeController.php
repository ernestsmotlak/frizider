<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginateRecipes(Request $request)
    {
        $userRecipes = Recipe::where('user_id', auth()->id())
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
        $recipe = Recipe::where('user_id', auth()->id())
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
}
