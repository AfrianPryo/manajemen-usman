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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role'            => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'unit.access'     => \App\Http\Middleware\EnsureUnitAccess::class,
            'password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'user.active'     => \App\Http\Middleware\EnsureUserIsActive::class,
            'single.session'  => \App\Http\Middleware\SingleActiveSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();