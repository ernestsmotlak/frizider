<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nothing else ever ends a run whose worker never showed up. Overlapping runs
// would be harmless — the refund is idempotent — but there is no reason to
// have two sweeps competing for the same rows.
Schedule::command('ai:sweep-stalled')->everyFiveMinutes()->withoutOverlapping();

// Reviews get abandoned — someone opens the list, gets interrupted, never comes
// back. Hourly is often enough for a job whose threshold is a day.
Schedule::command('ai:sweep-scan-photos')->hourly()->withoutOverlapping();
