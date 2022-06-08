<?php

namespace Webi\Listeners;

use Webi\Events\WebiUserCreated;
use Illuminate\Support\Facades\Log;

class UserCreatedNotification
{
    public function handle(WebiUserCreated $event)
    {
        Log::build([
          'driver' => 'single',
          'path' => storage_path('logs/webi.log'),
        ])->info("CREATED##UID##".$event->user->id."##IP##".$event->ip_address."##".time()."##");
    }
}
