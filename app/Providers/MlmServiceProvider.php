<?php

namespace App\Providers;

use App\Services\MlmService;
use Illuminate\Support\ServiceProvider;

class MlmServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(MlmService::class, function () {
            return new MlmService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
