<?php

namespace Webi\Listeners;

use Webi\Events\WebiUserCreated;
use Illuminate\Support\Facades\Log;

class UserCreatedNotification
{
    public function handle(WebiUserCreated $event)
    {
        Log::info("CREATED##UID##".$event->user->id."##IP##".$event->ip_address."##".time()."##");
    }
}
