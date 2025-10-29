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
