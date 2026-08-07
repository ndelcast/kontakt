<?php

use App\Exceptions\SymbioseReporter;
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
        $middleware->web(append: [
            // SetLocale était enregistré dans le middleware du panel Filament ;
            // sa suppression (#14) a orphelin ce middleware, et l'application
            // ignorait depuis la langue choisie par chaque utilisateur.
            // Placé avant HandleInertiaRequests pour que les props partagées
            // soient déjà traduites.
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->redirectGuestsTo('/admin/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Symbiose.IA — Exception Reporter
        // https://symbiose.ai
        $exceptions->reportable(function (\Throwable $e) {
            SymbioseReporter::report($e);
        });
    })->create();
