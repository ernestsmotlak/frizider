<?php

namespace App\Ai\Operations;

use App\Ai\AiPart;
use App\Ai\AiRequest;
use App\Ai\AiResponse;
use App\Ai\PromptRepository;
use App\Ai\Schemas\RecipeSchema;
use App\Data\GeneratedRecipe;
use App\Enums\Unit;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeInstruction;
use App\Models\UserAiRecipeLog;

/**
 * The recipe-from-ingredients operation: what goes into the AI request, and
 * what happens with the answer. The job stays generic — future operations
 * are new classes with this same shape, not edits to existing code.
 */
class RecipeFromIngredients
{
    public function __construct(protected PromptRepository $prompts)
    {
    }

    public function buildRequest(UserAiRecipeLog $log): AiRequest
    {
        $ingredients = $log->request_meta['ingredients'] ?? [];

        return new AiRequest(
            // The task description is appended after system.md, whose final
            // "Input handling" section then applies to everything in parts.
            systemInstruction: $this->prompts->get('system')
                ."\n\n".$this->prompts->get('recipe_from_ingredients'),
            parts: [AiPart::text(implode("\n", $ingredients))],
            schema: RecipeSchema::get(),
            model: config('services.ai.model'),
        );
    }

    /**
     * Write the answer through the recipe path. Mirrors
     * RecipeController::saveRecipeWithData(). Returns the new recipe id.
     */
    public function persist(AiResponse $response, UserAiRecipeLog $log): int
    {
        $generated = GeneratedRecipe::fromArray($response->data);

        $recipe = Recipe::create([
            'user_id' => $log->user_id,
            'name' => $generated->name,
            'description' => $generated->description,
            'servings' => $generated->servings,
            'prep_time' => $generated->prepTime,
            'cook_time' => $generated->cookTime,
            'is_ai_generated' => true,
        ]);

        foreach ($generated->ingredients as $index => $ingredient) {
            // The schema enum makes an invalid unit impossible and the prompt
            // guides the choice; this catches anything unexpected regardless.
            [$unit, $notes] = Unit::normalizeKeepingNotes(
                $ingredient['unit'],
                $ingredient['notes'],
            );

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'name' => $ingredient['name'],
                'quantity' => $ingredient['quantity'],
                'unit' => $unit,
                'notes' => $notes,
                'sort_order' => $index,
            ]);
        }

        foreach ($generated->instructions as $index => $instruction) {
            RecipeInstruction::create([
                'recipe_id' => $recipe->id,
                'instruction' => $instruction,
                'sort_order' => $index,
                'completed' => false,
            ]);
        }

        return $recipe->id;
    }

    /**
     * system hash + operation hash, so a change in either file is visible in
     * the logs.
     */
    public function promptVersion(): string
    {
        return $this->prompts->version('system')
            .'.'.$this->prompts->version('recipe_from_ingredients');
    }
}
