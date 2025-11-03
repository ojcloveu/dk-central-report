<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('bet', 'BetCrudController');
    Route::crud('channel', 'ChannelCrudController');

    // Initial route
    Route::get('report', 'BetReportController@index')->name('page.report.index');
    // API route
    Route::get('api/bets', 'BetReportController@betReports')->name('api.bets.index');
    Route::get('api/bet-period', 'BetReportController@betReportPeriod')->name('api.bets.period');
});
