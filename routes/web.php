<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BetController;

Route::get('/', fn () => redirect()->route('backpack.dashboard'));

Route::get('/health', function () {
    // Check connection to DK database
    DB::connection('dk')->getPdo();

    return response()->json(['status' => 'ok']);
});

Route::get('/sync-bets', [BetController::class, 'syncBets']);
