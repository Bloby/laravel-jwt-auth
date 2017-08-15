<?php

namespace JWTAuth\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use JWTAuth\Facades\JWTAuth as Auth;
use JWTAuth\Exceptions\AttemptException;
use JWTAuth\Exceptions\JWTException;
use JWTAuth\Exceptions\TokenInvalidException;
use JWTAuth\Exceptions\TokenUnavailableException;
use JWTAuth\Exceptions\TokenExpiredException;

class JWTAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try
        {
            Auth::validateToken(Auth::getToken());

            if (!Auth::attempt()) {
                return response()->json(['reason' => 'user_not_found', 'message' => 'User with provided credentials not found.'], 404);
            }
        }
        catch (AttemptException $e)
        {
            return response()->json(['reason' => 'attempt_locked', 'message' => $e->getMessage()], $e->getStatusCode());
        }
        catch (TokenUnavailableException $e)
        {
            return response()->json(['reason' => 'token_unavailable', 'message' => $e->getMessage()], $e->getStatusCode());
        }
        catch (TokenExpiredException $e)
        {
            return response()->json(['reason' => 'token_expired', 'message' => $e->getMessage()], $e->getStatusCode());
        }
        catch (TokenInvalidException $e)
        {
            return response()->json(['reason' => 'token_invalid', 'message' => $e->getMessage()], $e->getStatusCode());
        }
        catch (JWTException $e)
        {
            return response()->json(['reason' => 'token_not_provided', 'message' => $e->getMessage()], $e->getStatusCode());
        }

        return $next($request);
    }
}
