<?php

namespace App\Http\Controllers;

use App\Enums\Unit;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeInstruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Enums\AiGenerationStatus;
use App\Enums\AiOperation;
use App\Jobs\GenerateAiRecipe;
use App\Models\AiUserData;
use App\Models\PantryItem;
use App\Models\UserAiRecipeLog;
use App\Services\AiCreditService;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginateRecipes(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'searchTerm' => 'nullable|string|max:100',
        ]);

        $perPage = (int)($validated['per_page'] ?? 10);
        $searchTerm = trim((string)($validated['searchTerm'] ?? ''));

        $query = Recipe::select([
            'id',
            'name',
            'description',
            'image_url',
        ])
            ->where('user_id', auth()->id())
            ->when($searchTerm !== '', function ($q) use ($searchTerm) {
                $escaped = addcslashes($searchTerm, '\\%_');
                $like = "%{$escaped}%";

                $q->where(function ($inner) use ($like) {
                    $inner
                        ->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhereHas('recipeIngredients', function ($ingredientsQuery) use ($like) {
                            $ingredientsQuery
                                ->where('name', 'like', $like)
                                ->orWhere('notes', 'like', $like);
                        })
                        ->orWhereHas('recipeInstructions', function ($instructionsQuery) use ($like) {
                            $instructionsQuery->where('instruction', 'like', $like);
                        });
                });
            })
            ->orderBy('created_at', 'desc');

        $countQuery = clone $query;
        $userRecipes = $query->simplePaginate($perPage);
        $allRecipes = $countQuery->count();

        return response()->json([
            'data' => $userRecipes,
            'allRecipes' => $allRecipes,
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
            'image_url' => 'nullable|string|max:500',
        ]);

        $validated['user_id'] = auth()->id();

        $recipe = Recipe::create($validated);

        return response()->json([
            'message' => 'Recipe created.',
            'data' => $recipe->fresh(),
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
            'data' => $recipeToUpdate->fresh(),
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
            'ingredients.*.unit' => ['nullable', Rule::enum(Unit::class)],
            'ingredients.*.notes' => ['nullable', 'string', 'max:500'],
            'ingredients.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'ingredients.*.completed' => ['nullable', 'boolean'],
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

                if (isset($ingredient['completed'])) {
                    $data['completed'] = $ingredient['completed'];
                }

                // update existing or create new
                $recipe->recipeIngredients()->updateOrCreate(
                    ['id' => $ingredient['id'] ?? null], // if null -> create
                    $data
                );
            }
        });

        return response()->json([
            'message' => 'Ingredients updated.',
            'data' => $recipe->fresh()->load(['recipeIngredients', 'recipeInstructions']),
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

        $updatedRecipe = Recipe::where('user_id', auth()->id())
            ->where('id', $recipe)
            ->with(['recipeInstructions', 'recipeIngredients'])
            ->firstOrFail();

        return response()->json([
            'message' => 'Instruction status updated.',
            'data' => $updatedRecipe,
        ]);
    }

    public function toggleIngredientCompleted(string $recipe, string $ingredient)
    {
        $recipeModel = Recipe::where('user_id', auth()->id())
            ->where('id', $recipe)
            ->firstOrFail();

        $ingredientModel = RecipeIngredient::where('recipe_id', $recipeModel->id)
            ->where('id', $ingredient)
            ->firstOrFail();

        $ingredientModel->completed = !$ingredientModel->completed;
        $ingredientModel->save();

        return response()->json([
            'message' => 'Ingredient status updated.',
            'data' => $recipeModel->fresh()->load(['recipeIngredients', 'recipeInstructions']),
        ]);
    }

    public function saveRecipeWithData(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'servings' => 'nullable|integer|min:1',
            'prep_time' => 'nullable|integer|min:0',
            'cook_time' => 'nullable|integer|min:0',
            'image_url' => 'nullable|string|max:500',
            'ingredients' => 'nullable|array',
            'ingredients.*.name' => 'required|string|max:255',
            'ingredients.*.quantity' => 'nullable|numeric|min:0',
            'ingredients.*.unit' => ['nullable', Rule::enum(Unit::class)],
            'ingredients.*.notes' => 'nullable|string|max:500',
            'ingredients.*.sort_order' => 'nullable|integer|min:0',
            'instructions' => 'nullable|array',
            'instructions.*.instruction' => 'required|string',
            'instructions.*.sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['user_id'] = auth()->id();

        $ingredients = $validated['ingredients'] ?? [];
        $instructions = $validated['instructions'] ?? [];
        unset($validated['ingredients'], $validated['instructions']);

        DB::beginTransaction();
        try {
            $recipe = Recipe::create($validated);

            foreach ($ingredients as $index => $ingredient) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'name' => $ingredient['name'],
                    'quantity' => $ingredient['quantity'] ?? null,
                    'unit' => $ingredient['unit'] ?? null,
                    'notes' => $ingredient['notes'] ?? null,
                    'sort_order' => $ingredient['sort_order'] ?? $index,
                ]);
            }

            foreach ($instructions as $index => $instruction) {
                RecipeInstruction::create([
                    'recipe_id' => $recipe->id,
                    'instruction' => $instruction['instruction'],
                    'sort_order' => $instruction['sort_order'] ?? $index,
                    'completed' => false,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Recipe created.',
                'data' => $recipe->fresh()->load(['recipeIngredients', 'recipeInstructions']),
            ], 201);
        } catch (\Throwable $error) {
            DB::rollBack();
            report($error);

            return response()->json([
                'message' => 'Failed to create recipe.',
                'error' => $error->getMessage(),
            ], 500);
        }
    }

    public function generateAiRecipeFromIngredients(Request $request, AiCreditService $credits)
    {
        $userId = $request->user()->id;
        $aiData = AiUserData::where('user_id', $userId)->first();

        abort_if($aiData === null || !$aiData->can_use_ai, 403, 'AI generation is not enabled for this account.');

        $validated = $request->validate([
            'ingredients' => 'required|array|min:1|max:30',
            'ingredients.*.id' => 'nullable|integer',
            'ingredients.*.name' => 'required|string|max:120',
            'ingredients.*.quantity' => 'nullable|numeric|min:0',
            'ingredients.*.unit' => ['nullable', Rule::enum(Unit::class)],
            'idempotency_key' => 'required|string|max:64',
        ]);

        // Every referenced pantry item must be the user's own.
        $pantryItemIds = array_values(array_unique(array_filter(array_column($validated['ingredients'], 'id'))));

        if ($pantryItemIds !== []) {
            $owned = PantryItem::whereIn('id', $pantryItemIds)->where('user_id', $userId)->count();

            if ($owned !== count($pantryItemIds)) {
                return response()->json([
                    'message' => 'You do not have permission to use one or more of these pantry items.',
                ], 403);
            }
        }

        // Same key already submitted — hand back the run that is already going.
        $existing = UserAiRecipeLog::where('user_id', $userId)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();

        if ($existing !== null) {
            return response()->json([
                'generation_id' => $existing->id,
                'status' => $existing->status,
            ], 202);
        }

        $operation = AiOperation::GenerateRecipeFromIngredients;

        $log = DB::transaction(function () use ($credits, $userId, $operation, $validated) {
            $log = UserAiRecipeLog::create([
                'user_id' => $userId,
                'action' => $operation->value,
                'status' => AiGenerationStatus::Pending,
                'request_meta' => ['ingredients' => $validated['ingredients']],
                'idempotency_key' => $validated['idempotency_key'],
            ]);

            $charge = $credits->spend(
                $userId,
                $operation->creditCost(),
                $log,
                $validated['idempotency_key'],
                ['operation' => $operation->value],
            );

            GenerateAiRecipe::dispatch($log, $charge)->afterCommit();

            return $log;
        });

        return response()->json([
            'generation_id' => $log->id,
            'status' => AiGenerationStatus::Pending,
            'credits_remaining' => $credits->balance($userId),
        ], 202);
    }

    /**
     * Everything still running, plus recently finished runs so a refreshed
     * client can announce results it missed. The client remembers what it
     * already showed.
     */
    public function activeGenerations(Request $request)
    {
        $logs = UserAiRecipeLog::where('user_id', $request->user()->id)
            ->where(function ($query) {
                $query->whereIn('status', [AiGenerationStatus::Pending, AiGenerationStatus::Processing])
                    ->orWhere('completed_at', '>=', now()->subMinutes(15));
            })
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return response()->json([
            'generations' => $logs->map(fn(UserAiRecipeLog $log) => [
                'generation_id' => $log->id,
                'status' => $log->status,
                'recipe_id' => $log->recipe_id,
                'error' => $this->clientError($log),
                'created_at' => $log->created_at,
            ]),
        ]);
    }

    public function generationStatus(Request $request, UserAiRecipeLog $log)
    {
        abort_if($log->user_id !== $request->user()->id, 404);

        return response()->json([
            'generation_id' => $log->id,
            'status' => $log->status,
            'recipe_id' => $log->recipe_id,
            'error' => $this->clientError($log),
            'recipe' => $log->status === AiGenerationStatus::Completed
                ? $log->recipe?->load(['recipeIngredients', 'recipeInstructions'])
                : null,
        ]);
    }

    /**
     * Never the stored error_message — that is the provider's raw response,
     * written for us and not for the user.
     */
    private function clientError(UserAiRecipeLog $log): ?string
    {
        return $log->status === AiGenerationStatus::Failed
            ? UserAiRecipeLog::CLIENT_FAILURE_MESSAGE
            : null;
    }

    // public function turnRecipeVegetarian(Request $request)
    // {

    // }

    // public function turnRecipeVegan(Request $request)
    // {

    // }
}
