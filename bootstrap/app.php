<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust reverse proxy forwarded headers (X-Forwarded-Proto/Host/etc).
        // Needed so route()/url() generate https:// URLs when served through
        // a TLS-terminating tunnel (e.g. ngrok) instead of always falling back
        // to http:// — otherwise browsers block fetch()/form submissions from
        // the https page as mixed content. Direct http://localhost access is
        // unaffected since there's no forwarded header to trust in that case.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'lite_api.auth' => \App\Http\Middleware\EnsureLiteApiAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
