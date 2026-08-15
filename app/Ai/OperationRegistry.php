<?php

namespace App\Ai;

use App\Ai\Operations\PantryFromPhoto;
use App\Ai\Operations\RecipeFromIngredients;
use App\Contracts\AiOperationHandler;
use App\Enums\AiOperation;
use App\Models\UserAiRecipeLog;

/**
 * Action string to the class that runs it.
 *
 * The one place that knows which operations exist. The job resolves through
 * here rather than typehinting a handler, which is what lets a single job serve
 * every operation.
 */
class OperationRegistry
{
    public function forLog(UserAiRecipeLog $log): AiOperationHandler
    {
        return $this->for($log->action);
    }

    public function for(string $action): AiOperationHandler
    {
        $operation = AiOperation::tryFrom($action);

        $class = $operation === null ? null : match ($operation) {
            AiOperation::GenerateRecipeFromIngredients => RecipeFromIngredients::class,
            AiOperation::PantryFromPhoto => PantryFromPhoto::class,
            // Planned, not built. Reached only if a row is written with an
            // action nothing can run.
            default => null,
        };

        // Permanent by design: no amount of retrying will conjure a handler,
        // so this fails the run immediately and refunds instead of spinning
        // through the backoff.
        if ($class === null) {
            throw new PermanentAiException("No handler registered for AI operation '{$action}'.");
        }

        return app($class);
    }
}
