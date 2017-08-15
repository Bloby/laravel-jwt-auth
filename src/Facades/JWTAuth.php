<?php 

namespace JWTAuth\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\User user()
 * @method static \Lcobucci\JWT\Token createToken(\App\User $user)
 * @method static \Lcobucci\JWT\Token parseToken(string $token)
 * @method static \Lcobucci\JWT\Token getToken()
 * @method static void validateToken(\Lcobucci\JWT\Token $token)
 * @method static bool attempt($credentials = [], $remember = false)
 * @method static $this forgetToken(\Lcobucci\JWT\Token $token)
 * @method static bool isUnavailableToken(\Lcobucci\JWT\Token $token)
 * @method static \Illuminate\Contracts\Auth\Authenticatable|null retrieveByCredentials(array $credentials)
 * @method static \Illuminate\Contracts\Auth\Authenticatable|null retrieveByJWT(\Lcobucci\JWT\Token $token)
 * @method static bool hasValidCredentials($user, $credentials)
 * @method static bool validateCredentials(\Illuminate\Contracts\Auth\Authenticatable $user, array $credentials)
 * @method static void login(\Illuminate\Contracts\Auth\Authenticatable $user, $remember = false)
 * @method static void fireAttemptEvent()
 * @method static void fireFailedEvent()
 * @method static void fireLoginEvent()
 * @method static mixed config($name)
 * @method static string username()
 * @method static $this setUsername($username)
 *
 * @see \JWTAuth\JWTAuth
 * @see \JWTAuth\Facades\JWTAuth
 */
class JWTAuth extends Facade {

	/**
	 * Get the registered name of the component
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'jwt.auth'; }
}