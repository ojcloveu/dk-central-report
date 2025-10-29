<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('backpack.dashboard'));

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
