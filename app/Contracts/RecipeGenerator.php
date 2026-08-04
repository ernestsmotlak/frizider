<?php

namespace App\Contracts;

use App\Data\GeneratedRecipe;

interface RecipeGenerator
{
    /**
     * @param  array<int, string>  $ingredients
     *
     * @throws \RuntimeException when generation fails or returns an unusable shape
     */
    public function generateFromIngredients(array $ingredients): GeneratedRecipe;
}
