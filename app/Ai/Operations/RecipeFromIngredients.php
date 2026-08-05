<?php

namespace App\Ai\Operations;

use App\Ai\AiPart;
use App\Ai\AiRequest;
use App\Ai\AiResponse;
use App\Ai\PromptRepository;
use App\Ai\Schemas\RecipeSchema;
use App\Data\GeneratedRecipe;
use App\Enums\Unit;
use App\Models\PantryItem;
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
        [$names, $lines] = $this->submittedIngredients($log);

        return new AiRequest(
            // The task description is appended after system.md, whose final
            // "Input handling" section then applies to everything in parts.
            systemInstruction: $this->prompts->get('system')
                ."\n\n".$this->prompts->get('recipe_from_ingredients'),
            parts: [AiPart::text(implode("\n", $lines))],
            // The submitted names become the schema enum — the model cannot
            // return an ingredient the user does not have, staples aside.
            schema: RecipeSchema::get($names),
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
        $pantryIds = $this->pantryIdsByName($log);

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
                'pantry_item_id' => $pantryIds[mb_strtolower($ingredient['name'])] ?? null,
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

    /**
     * The submitted ingredients as [names for the schema enum, data lines
     * for the model]. Amounts ride along as data — a ceiling the prompt
     * tells the model to respect, not a schema constraint.
     *
     * @return array{0: string[], 1: string[]}
     */
    private function submittedIngredients(UserAiRecipeLog $log): array
    {
        $names = [];
        $lines = [];

        foreach ($log->request_meta['ingredients'] ?? [] as $ingredient) {
            if (is_string($ingredient)) {
                $ingredient = ['name' => $ingredient];
            }

            $name = trim((string)($ingredient['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $names[] = $name;

            $quantity = $ingredient['quantity'] ?? null;
            $amount = trim((is_numeric($quantity) ? (float)$quantity : '').' '.($ingredient['unit'] ?? ''));

            $lines[] = $amount === '' ? $name : "{$name}: {$amount}";
        }

        return [$names, $lines];
    }

    /**
     * lowercase name → pantry_item_id for the items that were submitted and
     * still exist. Matched against the submitted names, not the live pantry,
     * so a rename mid-generation cannot break the link.
     *
     * @return array<string, int>
     */
    private function pantryIdsByName(UserAiRecipeLog $log): array
    {
        $map = [];

        foreach ($log->request_meta['ingredients'] ?? [] as $ingredient) {
            if (!is_array($ingredient) || empty($ingredient['id']) || empty($ingredient['name'])) {
                continue;
            }

            $map[mb_strtolower(trim((string)$ingredient['name']))] ??= (int)$ingredient['id'];
        }

        if ($map === []) {
            return [];
        }

        // An item deleted since submission must not produce a dangling link.
        $existing = array_flip(PantryItem::whereIn('id', array_values($map))->pluck('id')->all());

        return array_filter($map, fn(int $id) => isset($existing[$id]));
    }
}
