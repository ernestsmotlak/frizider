<?php

namespace App\Contracts;

use App\Ai\AiRequest;
use App\Ai\AiResponse;
use App\Models\UserAiRecipeLog;

/**
 * One AI operation: what goes into the request, and what happens with the
 * answer.
 *
 * The job knows none of this. It charges, retries, refunds and sweeps the same
 * way whatever is running — so a new operation is a new class implementing this
 * interface plus a line in OperationRegistry, never an edit to the job.
 */
interface AiOperationHandler
{
    public function buildRequest(UserAiRecipeLog $log): AiRequest;

    /**
     * Write the answer, and say what the log should record about it.
     *
     * Returning columns rather than writing them keeps the whole terminal
     * update — result, status, tokens, timestamp — inside the job's single
     * transaction, so a run cannot be half-committed.
     *
     * @return array<string, mixed> columns to merge into the log row
     */
    public function persist(AiResponse $response, UserAiRecipeLog $log): array;

    /**
     * Prompt fingerprint, written into request_meta so a generation can be
     * traced back to the exact prompt text that produced it.
     */
    public function promptVersion(): string;

    /**
     * Let go of whatever the request was holding — an uploaded file, a temp
     * directory — because nothing is going to use it now.
     *
     * Called when a run ends with no usable result, and again when the user
     * settles a result that had one. Must be safe to call twice, and must not
     * throw: it runs inside failure paths whose real job is the refund.
     */
    public function releaseInputs(UserAiRecipeLog $log): void;
}
