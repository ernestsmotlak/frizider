<?php

namespace App\Console\Commands;

use App\Enums\AiCreditTransactionType;
use App\Enums\AiGenerationStatus;
use App\Models\AiCreditTransaction;
use App\Models\UserAiRecipeLog;
use App\Services\AiCreditService;
use Illuminate\Console\Command;

/**
 * Ends generations nothing is going to finish.
 *
 * A job that merely fails ends itself: the worker exhausts its attempts and
 * GenerateAiRecipe::failed() refunds. The gap this closes is work that never
 * reaches that point — no worker running at all, a queue that was never
 * drained, a row whose job was lost. Left alone those sit at pending forever,
 * holding the user's credit and spinning the pill on every page load.
 *
 * The threshold has to clear the slowest legitimate run by a wide margin. One
 * attempt is capped at 60s and there are three of them plus 10s and 30s of
 * backoff, so about four minutes of genuine work; anything past fifteen has
 * no one left to finish it.
 */
class SweepStalledAiGenerations extends Command
{
    protected $signature = 'ai:sweep-stalled {--minutes=15 : How long a run may go unfinished before it is abandoned}';

    protected $description = 'Fail and refund AI generations that no worker is going to finish';

    public function handle(AiCreditService $credits): int
    {
        $minutes = max(1, (int)$this->option('minutes'));

        $stalled = UserAiRecipeLog::whereIn('status', [AiGenerationStatus::Pending, AiGenerationStatus::Processing])
            ->where('created_at', '<', now()->subMinutes($minutes))
            ->get();

        foreach ($stalled as $log) {
            // Refund first, exactly as failed() does — if the log update ever
            // throws, the money must already be right. Refunding a charge
            // twice is a no-op, so a second sweep cannot double-credit.
            $charge = AiCreditTransaction::where('reference_type', $log->getMorphClass())
                ->where('reference_id', $log->getKey())
                ->where('type', AiCreditTransactionType::Consumption)
                ->first();

            if ($charge !== null) {
                $credits->refund($charge, ['reason' => 'generation_stalled']);
            }

            $log->update([
                'status' => AiGenerationStatus::Failed,
                'error_message' => "Abandoned after {$minutes} minutes: no worker finished this run.",
                'completed_at' => now(),
            ]);
        }

        $this->info($stalled->isEmpty()
            ? 'No stalled generations.'
            : "Abandoned and refunded {$stalled->count()} generation(s).");

        return self::SUCCESS;
    }
}
