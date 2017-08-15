laravel-jwt-auth
======

**NOTE:** This package is no longer in active development. Feel free to fork and extend it as needed.

A simple Laravel interface for interacting with the JWT auth API.


# Installation
To install the package, simply add the following to your Laravel installation's `composer.json` file:

```json
"require": {
	"laravel/framework": "5.*",
	"blob/laravel-jwt-auth": "dev-master"
},
```

Run `composer update` to pull in the files.

Then, add the following **Service Provider** to your `providers` array in your `config/app.php` file:

```php
'providers' => [
	...
    JWTAuth\Providers\JWTAuthServiceProvider::class,
    JWTAuth\Providers\JWTEventServiceProvider::class,
];
```

Then, add the following **Facade** to your `aliases` array in your `config/app.php` file:
```php
'aliases' => [
    ...
    'JWTAuth' => JWTAuth\Facades\JWTAuth::class,
];
```

Then, add the following **Middleware** to your `routeMiddleware` array in your `app/Http/Kernel.php` file:
```php
protected $routeMiddleware = [
    ...
    'jwt.auth' => \JWTAuth\Http\Middleware\JWTAuth::class,
    'jwt.auth.acl' => \JWTAuth\Http\Middleware\JWTAuthAcl::class,
];
```

From the command-line run:
`php artisan vendor:publish --provider="JWTAuth\Providers\JWTAuthServiceProvider"`

# Configuration

Open `config/jwt.php` and configure the api endpoint and credentials:

```php
return [
    'username' => 'email',
    'secret' => 'secret_change_me',//32 length
    'token_header' => 'Authorization',
    //post, get, ...
    'token_name' => 'token',
    //ex: example.com
    'iss' => 'iss_change_me',
    //ex: my_app_name
    'aud' => 'aud_change_me',
    //token expiration
    'expiration' => 3600,//sec
    'store' => 'file',
    //count of attempt fails by credentials
    'attempts' => 5,
    //block user on *min, if count of attempts not remain
    'attempts_exp' => 60, //min
];
```

# Usage
Authenticate by credentials
```php
try
{
    $credentials = $request->only(['email', 'password']);
    
    if (!JWTAuth::attempt($credentials)) {
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
```

Authenticate by token
```php
try
{
    JWTAuth::validateToken(JWTAuth::getToken());
    
    if (!JWTAuth::attempt()) {
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
```

Get user will be return `\App\User` object after calling `attempt()` method.
```php
$user = JWTAuth::user();
```

Create and get new token. Where `$user` is instance of `\App\User`.
```php
$tokenObject = JWTAuth::createToken($user);
```

Get token object from `string`.
```php
$tokenObject = JWTAuth::parseToken($token);
```

Method `getToken()` will search token in headers or request data.

Get token as string
```php
$tokenString = (string)JWTAuth::getToken();
```

Get token as object (`\Lcobucci\JWT\Token`)
```php
$tokenObject = JWTAuth::getToken();
```

Mark token as unavailable. Where `$token` is instance of `\Lcobucci\JWT\Token`.
```php
JWTAuth::forgetToken($token);
```

Validate token. Where `$token` is instance of `\Lcobucci\JWT\Token`
```php
try
{
    JWTAuth::validateToken($token)
}
catch(
/**
 * @throws \JWTAuth\Exceptions\TokenExpiredException
 * @throws \JWTAuth\Exceptions\TokenInvalidException
 * @throws \JWTAuth\Exceptions\TokenUnavailableException
 */
)
{
}
```

Set `username` field name instead config default `email`
```php
JWTAuth::setUsername('login');
```

Get current `username` field name
```php
$username = JWTAuth::username();
```

Get user (`\App\User`) by credentials
```php
$user = JWTAuth::retrieveByCredentials($credentials);
```

Get user (`\App\User`) by token (`\Lcobucci\JWT\Token`)
```php
$user = JWTAuth::retrieveByJWT($token);
```

Login user. Where `$user` is instance of `\App\User` 
```php
login($user);
```