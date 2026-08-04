<?php

namespace App\Services;

use App\Contracts\RecipeGenerator;
use App\Data\GeneratedRecipe;
use RuntimeException;

class FakeRecipeGenerator implements RecipeGenerator
{
    public function generateFromIngredients(array $ingredients): GeneratedRecipe
    {
        sleep((int)config('services.ai.fake_delay', 3));

        // Manual failure trigger: include "fail" as an ingredient.
        if (in_array('fail', array_map('strtolower', $ingredients), true)) {
            throw new RuntimeException('Fake generator was told to fail.');
        }

        $rate = (float)config('services.ai.fake_failure_rate', 0.0);

        if ($rate > 0 && mt_rand() / mt_getrandmax() < $rate) {
            throw new RuntimeException('Fake generator failed randomly.');
        }

        return new GeneratedRecipe(
            name: 'Fake recipe with ' . ($ingredients[0] ?? 'nothing'),
            description: 'Placeholder recipe generated without calling any AI provider.',
            servings: 2,
            prepTime: 10,
            cookTime: 20,
            ingredients: [
                ['name' => $ingredients[0] ?? 'onion', 'quantity' => 200.0, 'unit' => 'g', 'notes' => null],
                ['name' => $ingredients[1] ?? 'olive oil', 'quantity' => 30.0, 'unit' => 'ml', 'notes' => null],
                ['name' => $ingredients[2] ?? 'egg', 'quantity' => 2.0, 'unit' => 'pcs', 'notes' => null],
            ],
            instructions: [
                'Chop everything roughly.',
                'Heat the pan over medium heat.',
                'Cook until it looks done.',
                'Season and serve.',
            ],
            tokensUsed: 1234,
        );
    }
}
