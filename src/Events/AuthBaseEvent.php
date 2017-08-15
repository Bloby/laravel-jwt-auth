<?php

namespace JWTAuth\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Request\Request;
use JWTAuth\Providers\Storage\CacheAdapter;

abstract class AuthBaseEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $clientID;
    public $cache;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        $this->clientID = sha1(Request::ip());
        $this->cache = new CacheAdapter(config('jwt.store'));
    }
}
