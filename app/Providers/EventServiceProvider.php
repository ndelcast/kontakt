<?php

namespace App\Providers;

use App\Listeners\ProcessPendingInvitation;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            ProcessPendingInvitation::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
