<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'payments/mpesa/callback',
            'payments/tigo/callback',
            'payments/airtel/callback',
        ]);

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'admin.permission' => \App\Http\Middleware\EnsureAdminPermission::class,
            'social.signup.complete' => \App\Http\Middleware\EnsureSocialSignupComplete::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\EnsureAccountUsable::class,
        ]);
        $trusted = env('TRUSTED_PROXIES', '*');
        $middleware->trustProxies(
            at: $trusted === '*' ? '*' : array_filter(array_map('trim', explode(',', $trusted)))
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                ], 419);
            }

            return response()->view('errors.419', [], 419);
        });
    })->create();
