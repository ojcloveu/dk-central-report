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

// Route::get('/test-dk-api', function () {
//     $mlmService = new \App\Services\MlmService();

//     $data = $mlmService->get('/api/report/summary', [
//         'username' => 'DKANAABA001',
//     ]);

//     dd($data);
// });
