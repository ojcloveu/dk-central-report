<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceHttps(app()->isProduction());

        LogViewer::auth(function ($request) {
            return true; // TODO: Implement proper authentication
        });

        // Passport settings
        Passport::tokensExpireIn(now()->addYears(10));
        Passport::personalAccessTokensExpireIn(CarbonInterval::months(6));
    }
}
