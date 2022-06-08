<?php

namespace Webi\Listeners;

use Webi\Events\WebiUserLogged;
use Illuminate\Support\Facades\Log;

class UserLoggedNotification
{
    public function handle(WebiUserLogged $event)
    {
        Log::info("LOGGED##UID##".$event->user->id."##IP##".$event->ip_address."##".time()."##");
    }
}
