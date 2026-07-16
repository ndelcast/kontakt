<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Sans cela, le middleware `guest` cherche une route nommée `dashboard`
        // ou `home` — la nôtre s'appelle `app.dashboard` — et retombe sur `/`.
        RedirectIfAuthenticated::redirectUsing(fn () => route('app.dashboard'));
    }
}
