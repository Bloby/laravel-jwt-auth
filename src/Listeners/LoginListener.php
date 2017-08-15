<?php

namespace JWTAuth\Listeners;

use JWTAuth\Events\Login;
use JWTAuth\Exceptions\AttemptException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class LoginListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     *
     * @throws AttemptException
     */
    public function handle(Login $event)
    {
        $attemptKey = sprintf('attempt.%s', $event->clientID);
        if ($event->cache->has($attemptKey) && (int)$event->cache->get($attemptKey) >= (int)config('jwt.attempts')) {
            throw new AttemptException(sprintf('You are blocked on %s min! You made many attempts at authentication.', config('jwt.attempts')));
        }
        
        $event->cache->forget($attemptKey);
    }
}
