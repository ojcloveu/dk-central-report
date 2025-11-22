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
    ->runInBackground();
