<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && in_array($request->user()->locale, ['en', 'fr', 'es'])) {
            App::setLocale($request->user()->locale);
        }

        return $next($request);
    }
}
