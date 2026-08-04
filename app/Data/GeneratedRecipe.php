<?php

namespace App\Data;

final readonly class GeneratedRecipe
{
    /**
     * @param array<int, array{name: string, quantity: float|null, unit: string|null, notes: string|null}> $ingredients
     * @param array<int, string> $instructions
     */

    public function __construct(
        public string  $name,
        public ?string $description,
        public ?int    $servings,
        public ?int    $prepTime,
        public ?int    $cookTime,
        public array   $ingredients,
        public array   $instructions,
        public ?int    $tokensUsed = null,
    )
    {
    }
}
