<?php

namespace Webi\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class WebiUserLogged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $ip_address;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $ip_address)
    {
        $this->user = $user;
        $this->ip_address = $ip_address;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
