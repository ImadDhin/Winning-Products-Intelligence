<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('winning:run-connectors')->everyFiveMinutes();
Schedule::command('winning:rebuild-leaderboards')->everyFiveMinutes();
Schedule::job(new \App\Domain\Alert\Jobs\EvaluateWatchlistAlertsJob())->everyFiveMinutes();
