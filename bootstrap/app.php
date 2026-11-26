<?php

declare(strict_types=1);

use App\Exceptions\ApiException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    // Listeners are listed explicitly in App\Providers\EventServiceProvider.
    ->withEvents(discover: false)
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            static fn(Request $request): bool => $request->is('api/*') || $request->expectsJson(),
        );

        // Expected business outcomes are answers to the caller, not application failures.
        $exceptions->dontReport(ApiException::class);

        $exceptions->render(
            static fn(ApiException $e) => response()->json(['message' => $e->getMessage()], $e->httpStatus()),
        );
    })->create();
