<?php

namespace App\Jobs;

use App\Ai\Operations\RecipeFromIngredients;
use App\Contracts\AiClient;
use App\Enums\AiGenerationStatus;
use App\Models\AiCreditTransaction;
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

    public int $timeout = 60;

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
    public function handle(RecipeFromIngredients $operation, AiClient $client): void
    {
        // A previous attempt may have committed just before the worker died.
        if ($this->log->status === AiGenerationStatus::Completed) {
            return;
        }

        $this->log->update(['status' => AiGenerationStatus::Processing]);

        $request = $operation->buildRequest($this->log);

        // Record what is about to run — merged, never replaced, so the
        // original input survives for retries.
        $this->log->update([
            'request_meta' => array_merge($this->log->request_meta ?? [], [
                'prompt_version' => $operation->promptVersion(),
                'model' => $request->model,
            ]),
        ]);

        $response = $client->send($request);

        DB::transaction(function () use ($operation, $response) {
            $recipeId = $operation->persist($response, $this->log);

            $this->log->update([
                'recipe_id' => $recipeId,
                'status' => AiGenerationStatus::Completed,
                'tokens_used' => $response->tokensUsed,
                'completed_at' => now(),
            ]);
        });
    }

    /**
     * Runs exactly once, after the final attempt has failed.
     * The only place credits are returned. Refund first — if the log update
     * ever throws, the money must already be right.
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
}
