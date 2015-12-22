<?php namespace Creativeorange\Gravatar;

use Illuminate\Support\ServiceProvider;

class GravatarServiceProvider extends ServiceProvider {
//
//	/**
//	 * Indicates if loading of the provider is deferred.
//	 *
//	 * @var bool
//	 */
//	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$configPath = __DIR__ . '/../config/gravatar.php';
		$this->mergeConfigFrom($configPath, 'gravatar');

		$this->app->singleton('gravatar', function () {
			return new Gravatar;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}


	public function boot ()
	{
		$this->publishes([
			__DIR__.'/../config/gravatar.php' => config_path('gravatar.php'),
		]);
	}

}
