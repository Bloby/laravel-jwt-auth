<?php

namespace JWTAuth\Providers;

use Illuminate\Support\ServiceProvider;

class JWTAuthServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
        //
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $configPath = config_path('jwt.php');
        if (!\Illuminate\Support\Facades\File::exists($configPath)) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => $configPath
            ]);
        }

        $this->app->singleton('jwt.auth', function ($app) {
            $auth = new \JWTAuth\JWTAuth(
                $app['request'],
                new \JWTAuth\Providers\Storage\CacheAdapter(config('jwt.store'))
            );

            return $auth;
        });

		$this->app->booting(function() {
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('JWTAuth', 'JWTAuth\Facades\JWTAuth');
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['jwt.auth'];
	}

}
