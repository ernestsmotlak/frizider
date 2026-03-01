<?php

namespace App\Http\Controllers;

use App\Models\CookingSession;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CookingSessionController extends Controller
{
    public function createCookingSession(Request $request)
    {
        $validated = $request->validate([
            'recipe_id' => 'required|integer|exists:recipes,id',
        ]);

        $recipe = Recipe::where('id', $validated['recipe_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $cookingSession = CookingSession::updateOrCreate(
            ['user_id' => auth()->id()],
            ['recipe_id' => $validated['recipe_id']],
        );

        return response()->json([
            'message' => 'Cooking session successfully created.',
        ], 201);
    }

    public function getCookingSession(Request $request)
    {
        $cookingSession = CookingSession::with([
            'recipe.recipeIngredients',
            'recipe.recipeInstructions',
            'cookingSessionTimers',
        ])
            ->where('user_id', auth()->id())->firstOrFail();

        return response()->json([
            'data' => $cookingSession,
        ]);
    }

    public function resetRecipeData(Request $request)
    {
        $validated = $request->validate([
            'recipe_id' => 'required|integer|exists:recipes,id',
        ]);

        $recipe = Recipe::findOrFail($validated['recipe_id']);

        if ($recipe->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        DB::transaction(function () use ($validated) {
            Recipe::findOrFail($validated['recipe_id'])
                ->recipeInstructions()
                ->update(['completed' => false]);

            CookingSession::updateOrCreate(
                ['user_id' => auth()->id()],
                ['recipe_id' => $validated['recipe_id']],
            );
        });

        $cookingSession = CookingSession::with([
            'recipe.recipeIngredients',
            'recipe.recipeInstructions',
            'cookingSessionTimers',
        ])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'data' => $cookingSession,
        ]);
    }

    public function updateTimerFabPosition(Request $request)
    {
        $validated = $request->validate([
            'location.timer_fab_x_percent' => 'required|numeric|min:0|max:100',
            'location.timer_fab_y_percent' => 'required|numeric|min:0|max:100',
        ]);

        $cookingSession = CookingSession::where('user_id', auth()->id())->firstOrFail();

        $cookingSession->update([
            'timer_fab_x_percent' => (float) $validated['location']['timer_fab_x_percent'],
            'timer_fab_y_percent' => (float) $validated['location']['timer_fab_y_percent'],
        ]);

        return response()->json([
            'data' => $cookingSession->fresh(),
        ]);
    }
}
