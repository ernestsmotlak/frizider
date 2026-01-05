<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeInstruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginateRecipes(Request $request)
    {

        $allRecipes = Recipe::where('user_id', auth()->id())->count();

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

        return response()->json([
            'data' => $userRecipes,
            'allRecipes' => $allRecipes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
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
        $recipe = Recipe::with(['recipeIngredients', 'recipeInstructions'])
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

    public function updateIngredients(Request $request, Recipe $recipe)
    {

        if ($recipe->user_id !== auth()->id()) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }

        $validated = $request->validate([
            'ingredients' => ['required', 'array'],
            'ingredients.*.id' => [
                'nullable',
                'integer',
                Rule::exists('recipe_ingredients', 'id')->where('recipe_id', $recipe->id),
            ],
            'ingredients.*.name' => ['required', 'string', 'max:255'],
            'ingredients.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:50'],
            'ingredients.*.notes' => ['nullable', 'string', 'max:500'],
            'ingredients.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($recipe, $validated) {
            foreach ($validated['ingredients'] as $index => $ingredient) {
                $data = [
                    'name' => $ingredient['name'],
                    'quantity' => $ingredient['quantity'] ?? null,
                    'unit' => $ingredient['unit'] ?? null,
                    'notes' => $ingredient['notes'] ?? null,
                    'sort_order' => $ingredient['sort_order'] ?? $index,
                ];

                // update existing or create new
                $recipe->recipeIngredients()->updateOrCreate(
                    ['id' => $ingredient['id'] ?? null], // if null -> create
                    $data
                );
            }
        });

        return response()->json([
            'message' => 'Ingredients updated.',
            'data' => $recipe->fresh()->load('recipeIngredients'),
        ]);
    }

    public function deleteIngredientFromRecipe(string $recipe, string $ingredient)
    {
        $recipeModel = Recipe::where('user_id', auth()->id())
            ->where('id', $recipe)
            ->firstOrFail();

        $ingredientModel = RecipeIngredient::where('recipe_id', $recipeModel->id)
            ->where('id', $ingredient)
            ->firstOrFail();

        $ingredientModel->delete();

        return response()->json([
            'message' => 'Ingredient deleted successfully.',
            'data' => $recipeModel->fresh()->load('recipeIngredients'),
        ]);
    }

    public function updateInstructions(Request $request, Recipe $recipe)
    {
        if ($recipe->user_id !== auth()->id()) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }

        $validated = $request->validate([
            'instructions' => ['required', 'array'],
            'instructions.*.id' => [
                'nullable',
                'integer',
                Rule::exists('recipe_instructions', 'id')->where('recipe_id', $recipe->id),
            ],
            'instructions.*.instruction' => ['required', 'string'],
            'instructions.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'instructions.*.completed' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($recipe, $validated) {
            foreach ($validated['instructions'] as $index => $instruction) {
                $data = [
                    'instruction' => $instruction['instruction'],
                    'sort_order' => $instruction['sort_order'] ?? $index,
                ];

                if (isset($instruction['completed'])) {
                    $data['completed'] = $instruction['completed'];
                }

                $recipe->recipeInstructions()->updateOrCreate(
                    ['id' => $instruction['id'] ?? null],
                    $data
                );
            }
        });

        return response()->json([
            'message' => 'Instructions updated.',
            'data' => $recipe->fresh()->load(['recipeIngredients', 'recipeInstructions']),
        ]);
    }

    public function deleteInstructionFromRecipe(string $recipe, string $instruction)
    {
        $recipeModel = Recipe::where('user_id', auth()->id())
            ->where('id', $recipe)
            ->firstOrFail();

        $instructionModel = RecipeInstruction::where('recipe_id', $recipeModel->id)
            ->where('id', $instruction)
            ->firstOrFail();

        $instructionModel->delete();

        return response()->json([
            'message' => 'Instruction deleted successfully.',
            'data' => $recipeModel->fresh()->load(['recipeIngredients', 'recipeInstructions']),
        ]);
    }

    public function toggleInstructionCompleted(string $recipe, string $instruction)
    {
        $recipeModel = Recipe::where('user_id', auth()->id())
            ->where('id', $recipe)
            ->firstOrFail();

        $instructionModel = RecipeInstruction::where('recipe_id', $recipeModel->id)
            ->where('id', $instruction)
            ->firstOrFail();

        $instructionModel->completed = !$instructionModel->completed;
        $instructionModel->save();


        return response()->json([
            'message' => 'Instruction status updated.',
            'data' => $instructionModel,
            'instructions' => $recipeModel->recipeInstructions()->orderBy('sort_order')->get(),
        ]);
    }
}
