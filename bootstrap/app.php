<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register your middleware aliases here
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'suspended' => \App\Http\Middleware\CheckSuspended::class,
            'track.activity' => \App\Http\Middleware\TrackUserActivity::class,
        ]);

        // Add activity tracking to web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\TrackUserActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
