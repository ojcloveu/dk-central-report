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
    ->onSuccess(function () {
        $logContent = '';
        $logFile = storage_path('logs/bet-sync-scheduler.log');
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $logContent = implode("\n", array_slice($lines, -20)); // Get last 20 lines
        }
        
        \Log::info('Scheduled bet sync completed successfully', [
            'command' => 'sync:bets --days-back=0',
            'end_time' => now()->toDateTimeString(),
            'end_timestamp' => now()->timestamp,
            'recent_output' => $logContent,
            'exit_code' => 0
        ]);
    })
    ->onFailure(function () {
        $logContent = '';
        $logFile = storage_path('logs/bet-sync-scheduler.log');
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $logContent = implode("\n", array_slice($lines, -20)); // Get last 20 lines
        }
        
        \Log::error('Scheduled bet sync failed', [
            'command' => 'sync:bets --days-back=0',
            'end_time' => now()->toDateTimeString(),
            'end_timestamp' => now()->timestamp,
            'recent_output' => $logContent,
            'exit_code' => 'non-zero'
        ]);
    });
