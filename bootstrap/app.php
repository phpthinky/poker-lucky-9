<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
->withBroadcasting(
    channels: __DIR__.'/../routes/channels.php',
    // Use Sanctum so the Bearer token in Echo's auth header is accepted.
    attributes: ['middleware' => ['auth:sanctum']],
)
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // This app is token-based (no sessions/cookies).
        // Without this, any unauthenticated request to /api/* or /broadcasting/*
        // causes Laravel to redirect to Route::named('login') — which doesn't exist —
        // producing "Route [login] not defined." Return JSON 401 instead.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->is('broadcasting/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });
    })->create();
