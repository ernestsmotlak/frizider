<?php

namespace App\Enums;

enum AiOperation: string
{
    case GenerateRecipeFromIngredients = 'generate_recipe_from_ingredients';
    case TurnVegetarian = 'turn_vegetarian';
    case TurnVegan = 'turn_vegan';
    case PantryFromPhoto = 'pantry_from_photo';

    /**
     * Credit cost of one successful operation. Placeholder values —
     * revisit once real token usage is known.
     */
    public function creditCost(): int
    {
        return match ($this) {
            self::GenerateRecipeFromIngredients => 1,
            self::TurnVegetarian, self::TurnVegan => 1,
            self::PantryFromPhoto => 1,
        };
    }

    /** What this was, in a sentence a user would recognise. */
    public function label(): string
    {
        return match ($this) {
            self::GenerateRecipeFromIngredients => 'Recipe from ingredients',
            self::TurnVegetarian => 'Recipe turned vegetarian',
            self::TurnVegan => 'Recipe turned vegan',
            self::PantryFromPhoto => 'Shelf scan',
        };
    }

    /**
     * Whether the result is a list the user reviews before it becomes real,
     * rather than a row written on their behalf. Review results are the only
     * ones that outlive the job holding something that needs cleaning up.
     */
    public function needsReview(): bool
    {
        return $this === self::PantryFromPhoto;
    }
}
