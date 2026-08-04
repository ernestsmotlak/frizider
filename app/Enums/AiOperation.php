<?php

namespace App\Enums;

enum AiOperation: string
{
    case GenerateRecipeFromIngredients = 'generate_recipe_from_ingredients';
    case TurnVegetarian = 'turn_vegetarian';
    case TurnVegan = 'turn_vegan';

    /**
     * Credit cost of one successful operation. Placeholder values —
     * revisit once real token usage is known.
     */
    public function creditCost(): int
    {
        return match ($this) {
            self::GenerateRecipeFromIngredients => 1,
            self::TurnVegetarian, self::TurnVegan => 1,
        };
    }
}
