<?php

namespace App\Http\Controllers;

use App\Models\CookingSession;
use App\Models\Recipe;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validated['user_id'] !== auth()->id()) {
            return response()->json([
                'message' => 'You do not have permission to access this cooking session.',
            ], 403);
        }

        $cookingSession = CookingSession::where('user_id', $validated['user_id'])->firstOrFail();

        $recipe = Recipe::with(['recipeIngredients', 'recipeInstructions'])
            ->findOrFail($cookingSession->recipe_id);

        return response()->json([
            'data' => $recipe,
        ]);
    }
}
