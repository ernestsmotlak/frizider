<?php

namespace App\Jobs;

use App\Contracts\RecipeGenerator;
use App\Data\GeneratedRecipe;
use App\Enums\AiGenerationStatus;
use App\Enums\Unit;
use App\Models\AiCreditTransaction;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeInstruction;
use App\Models\UserAiRecipeLog;
use App\Services\AiCreditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

class GenerateAiRecipe implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 30];

    public int $timeout = 120;

    public bool $failOnTimeout = true;

    public function __construct(
        public UserAiRecipeLog     $log,
        public AiCreditTransaction $charge,
    )
    {
    }

    /**
     * One attempt. Throws on failure — Laravel decides whether to retry.
     */
    public function handle(RecipeGenerator $generator): void
    {
        // A previous attempt may have committed just before the worker died.
        if ($this->log->status === AiGenerationStatus::Completed) {
            return;
        }

        $this->log->update(['status' => AiGenerationStatus::Processing]);

        $generated = $generator->generateFromIngredients(
            $this->log->request_meta['ingredients'] ?? []
        );

        DB::transaction(fn() => $this->persist($generated));
    }

    /**
     * Runs exactly once, after the final attempt has failed.
     * The only place credits are returned.
     */
    public function failed(?Throwable $error): void
    {
        app(AiCreditService::class)->refund($this->charge, ['reason' => 'generation_failed']);

        $this->log->update([
            'status' => AiGenerationStatus::Failed,
            'error_message' => $error?->getMessage(),
            'completed_at' => now(),
        ]);
    }

    private function persist(GeneratedRecipe $generated): void
    {
        $recipe = Recipe::create([
            'user_id' => $this->log->user_id,
            'name' => $generated->name,
            'description' => $generated->description,
            'servings' => $generated->servings,
            'prep_time' => $generated->prepTime,
            'cook_time' => $generated->cookTime,
            'is_ai_generated' => true,
        ]);

        foreach ($generated->ingredients as $index => $ingredient) {
            [$unit, $notes] = Unit::normalizeKeepingNotes(
                $ingredient['unit'] ?? null,
                $ingredient['notes'] ?? null,
            );

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'name' => $ingredient['name'],
                'quantity' => $ingredient['quantity'] ?? null,
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

        $this->log->update([
            'recipe_id' => $recipe->id,
            'status' => AiGenerationStatus::Completed,
            'tokens_used' => $generated->tokensUsed,
            'completed_at' => now(),
        ]);
    }
}
