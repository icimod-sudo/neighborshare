<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

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
        // Debug route resolution
        Route::matched(function ($event) {
            Log::info('Route matched: ' . $event->route->getName());
            Log::info('Route action: ' . json_encode($event->route->getAction()));
        });
    }
}
