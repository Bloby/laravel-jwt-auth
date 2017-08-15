<?php

namespace JWTAuth\Listeners;

use JWTAuth\Events\Fail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class FailListener
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
     * @param  Fail  $event
     * @return bool
     */
    public function handle(Fail $event)
    {
        $attemptKey = sprintf('attempt.%s', $event->clientID);
        if ($event->cache->has($attemptKey) && $event->cache->get($attemptKey) <= config('jwt.attempts')) {
            $event->cache->increment($attemptKey);
            return true;
        }

        $event->cache->put($attemptKey, 1, config('jwt.attempts_exp'));

        return false;
    }
}
