<?php

namespace Webi\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Webi\Listeners\UserCreatedNotification;
use Webi\Listeners\UserLoggedNotification;
use Webi\Events\WebiUserCreated;
use Webi\Events\WebiUserLogged;

class WebiEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        WebiUserLogged::class => [
            UserLoggedNotification::class,
        ],
        WebiUserCreated::class => [
            UserCreatedNotification::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}