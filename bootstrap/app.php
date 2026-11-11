<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->redirectUsersTo(
            fn (\Illuminate\Http\Request $request): string => match ($request->user()->rol) {
                'admin' => route('admin.dashboard'),
                'empresa' => route('empresa.dashboard'),
                'estudiante' => route('estudiante.dashboard'),
                default => route('dashboard'),
            }
        );
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\PreventAuthenticatedPageCaching::class,
        ]);
        $middleware->alias([
            'rol' => \App\Http\Middleware\EnsureRol::class,
        ]);
    })
    
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
