<?php

namespace JWTAuth\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use JWTAuth\Events\Attempt;
use JWTAuth\Exceptions\AttemptException;

class AttemptListener
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
     * @param  Attempt  $event
     * @return void
     *
     * @throws \JWTAuth\Exceptions\AttemptException
     */
    public function handle(Attempt $event)
    {
        $attemptKey = sprintf('attempt.%s', $event->clientID);
        if ($event->cache->has($attemptKey) && (int)$event->cache->get($attemptKey) >= (int)config('jwt.attempts')) {
            throw new AttemptException(sprintf('You are blocked on %s min! You made many attempts at authentication.', config('jwt.attempts_exp')));
        }
    }
}
