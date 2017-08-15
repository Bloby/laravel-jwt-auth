<?php

namespace JWTAuth\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

use JWTAuth\Facades\JWTAuth as Auth;

class JWTAuthAcl
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
        $action = $request->route()->getAction();
        $roles = isset($action['roles']) ?
            $action['roles']
            : (isset($action['role']) ?
                $action['role']
                : []);

        if (!empty($roles) && (Auth::user() === null || !Auth::user()->hasRole($roles))) {
            return response()->json(['reason' => 'access_denied', 'message' => 'Not enough access rights.'], 403);
        }

        return $next($request);
    }
}
