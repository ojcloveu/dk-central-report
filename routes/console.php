<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule bet sync command to run every 5 minutes without overlapping
Schedule::command('sync:bets --days-back=0')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/bet-sync-scheduler.log'))
    ->before(function () {
        \Log::info('Scheduled bet sync started', [
            'command' => 'sync:bets --days-back=0',
            'start_time' => now()->toDateTimeString(),
            'start_timestamp' => now()->timestamp
        ]);
    })
    ->onSuccess(function ($output) {
        \Log::info('Scheduled bet sync completed successfully', [
            'command' => 'sync:bets --days-back=0',
            'end_time' => now()->toDateTimeString(),
            'end_timestamp' => now()->timestamp,
            'output' => $output,
            'exit_code' => 0
        ]);
    })
    ->onFailure(function ($output) {
        \Log::error('Scheduled bet sync failed', [
            'command' => 'sync:bets --days-back=0',
            'end_time' => now()->toDateTimeString(),
            'end_timestamp' => now()->timestamp,
            'output' => $output,
            'exit_code' => 'non-zero'
        ]);
    });
