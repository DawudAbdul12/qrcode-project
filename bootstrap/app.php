<?php

use App\Http\Middleware\EnsureCompanyUser;
use App\Http\Middleware\EnsurePasswordResetComplete;
use App\Http\Middleware\EnsurePlatformUser;
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
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/admin/dashboard');
        $middleware->alias([
            'company.user' => EnsureCompanyUser::class,
            'password.reset.complete' => EnsurePasswordResetComplete::class,
            'platform.user' => EnsurePlatformUser::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'ussd/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
