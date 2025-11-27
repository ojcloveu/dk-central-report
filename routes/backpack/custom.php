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
    Route::crud('masters', 'MasterCrudController');

    // Initial route
    Route::get('report', 'BetReportController@index')->name('page.report.index');
    
    // API routes
    Route::group(['prefix' => 'api'], function () {
        Route::get('bets', 'BetReportController@betReports')->name('api.bets.index');
        Route::get('bet-period', 'BetReportController@betReportPeriod')->name('api.bets.period');

        // DK API route
        Route::get('external-summary', 'BetReportController@getExternalSummary')->name('api.external.summary');
    });
});
