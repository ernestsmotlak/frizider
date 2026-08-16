<?php

namespace App\Jobs;

use App\Ai\OperationRegistry;
use App\Ai\PermanentAiException;
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
    public function handle(OperationRegistry $registry, AiClient $client): void
    {
        // A previous attempt may have committed just before the worker died,
        // or the sweep may have given up on this one and refunded it. Either
        // way the answer is already settled.
        if ($this->log->status->isTerminal()) {
            return;
        }

        // Which operation this row is. The job does the charging, retrying and
        // refunding; the handler is the only part that differs per operation.
        $operation = $registry->forLog($this->log);

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

        try {
            $response = $client->send($request);
        } catch (PermanentAiException $error) {
            // Skip the remaining attempts and go straight to failed(), which
            // still refunds. Retrying a rejected request only makes the user
            // watch a spinner for the length of the backoff.
            $this->fail($error);

            // fail() quietly does nothing without a queue job instance. Left
            // alone that would return as if all was well, stranding the log on
            // Processing and keeping the credit — so let it throw instead.
            if ($this->job === null) {
                throw $error;
            }

            return;
        }

        DB::transaction(function () use ($operation, $response) {
            // The sweep may have declared this abandoned and refunded it while
            // the request was in flight. Honour that rather than racing it:
            // the credit is already back, so the recipe is not ours to keep.
            $settled = UserAiRecipeLog::whereKey($this->log->getKey())->lockForUpdate()->first();

            if ($settled === null || $settled->status->isTerminal()) {
                return;
            }

            // The handler says what the log should record — a recipe id, or a
            // list held for review. Merged inside the same transaction as the
            // status, so a run cannot be half-committed.
            $settled->update([
                ...$operation->persist($response, $this->log),
                'status' => AiGenerationStatus::Completed,
                'tokens_used' => $response->tokensUsed,
                'completed_at' => now(),
            ]);
        });

        // The answer is stored, and nothing reads the input again: the review
        // screen shows the list, never the photo it came from. Guarded and last
        // — a disk error must not fail a run that already succeeded, because
        // failing it would refund a credit that was genuinely spent.
        try {
            $operation->releaseInputs($this->log);
        } catch (Throwable) {
        }
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

        // Nothing will read the uploaded photo now. Last, and guarded: the
        // refund above is the part that must not be lost to a disk error, and
        // an unregistered action reaches this method too.
        try {
            app(OperationRegistry::class)->forLog($this->log)->releaseInputs($this->log);
        } catch (Throwable) {
        }
    }
}
