<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Load CT routes only when the module is enabled
            if (config('ct.enabled', true)) {
                \Illuminate\Support\Facades\Route::middleware('web')
                    ->group(base_path('routes/ct.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'                => \App\Http\Middleware\CheckRole::class,
            'verify.exam.session' => \App\Http\Middleware\VerifyExamSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->booted(function () {
        // CT rate limiter: lenient (shared school IP), primary protection is per-team cooldown
        RateLimiter::for('ct', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    })->create();
