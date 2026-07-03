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

        // AQUÍ REGISTRAMOS EL NOMBRE DEL MIDDLEWARE
        $middleware->alias([
            'checkpermiso' => \App\Http\Middleware\CheckPermiso::class,
        ]);
        $middleware->trustProxies(at: '*'); // Confía en los headers de Cloudflare

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
