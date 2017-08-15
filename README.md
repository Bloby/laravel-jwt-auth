laravel-jwt-auth
======

**NOTE:** This package is no longer in active development. Feel free to fork and extend it as needed.

A simple Laravel interface for interacting with the morinc API.


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
    'token_name' => 'token',
    'iss' => 'iss_change_me',
    'aud' => 'aud_change_me',
    'expiration' => 3600,//sec
    'store' => 'file',
    'attempts' => 5,
    'attempts_exp' => 60, //min
];
```

# Usage
```php
$DIDs = MOR::getDIDs($client_id);
```
