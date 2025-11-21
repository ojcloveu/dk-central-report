<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BetController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

// Bet sync endpoints
Route::post('/sync-bets', [BetController::class, 'syncBets']);
Route::post('/sync-bets/date-range', [BetController::class, 'syncBetsByDateRange']);
Route::post('/sync-bets/background', [BetController::class, 'startBackgroundSync']);
Route::get('/sync-bets/status/{jobId}', [BetController::class, 'getJobStatus'])->name('api.bets.job-status');
