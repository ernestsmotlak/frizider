<?php

namespace App\Console\Commands;

use App\Enums\AiOperation;
use App\Models\UserAiRecipeLog;
use App\Services\ScanPhotoStorage;
use Illuminate\Console\Command;

/**
 * Deletes scan photos nobody came back for.
 *
 * Confirming or discarding a scan releases its photo immediately; failure
 * releases it too. The gap this closes is the ordinary one — someone opens the
 * review, gets interrupted, and never returns. Their photo has done its work
 * and there is no one left to look at it.
 *
 * Deliberately generous: the review is the only place the photo is shown, so
 * clearing it while a user might still be reading the list would leave them
 * checking a list against a thumbnail that has vanished.
 */
class SweepScanPhotos extends Command
{
    protected $signature = 'ai:sweep-scan-photos {--hours=24 : How long an unreviewed scan keeps its photo}';

    protected $description = 'Delete scan photos whose review was never settled';

    public function handle(ScanPhotoStorage $photos): int
    {
        $hours = max(1, (int)$this->option('hours'));

        $abandoned = UserAiRecipeLog::where('action', AiOperation::PantryFromPhoto->value)
            ->where('created_at', '<', now()->subHours($hours))
            ->whereNotNull('request_meta->photo_path')
            ->get();

        $deleted = 0;

        foreach ($abandoned as $log) {
            if (!$photos->exists($log)) {
                continue;
            }

            $photos->delete($log);
            $deleted++;
        }

        $this->info($deleted === 0
            ? 'No scan photos to clear.'
            : "Deleted {$deleted} abandoned scan photo(s).");

        return self::SUCCESS;
    }
}
