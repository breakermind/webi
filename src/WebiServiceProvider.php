<?php

namespace Webi;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Webi\Http\Middleware\WebiAuthRoles;
use Webi\Http\Middleware\WebiLocales;
use Webi\Http\Middleware\WebiJsonResponse;
use Webi\Http\Middleware\WebiVerifyCsrfToken;
use Webi\Http\Facades\WebiFacade;
use Webi\Providers\WebiEventServiceProvider;
use Webi\Services\Webi;

class WebiServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->mergeConfigFrom(__DIR__.'/../config/config.php', 'webi');

		$this->app->bind(Webi::class, function($app) {
			return new Webi();
		});

		$this->app->bind('webi-facade', function($app) {
			return new WebiFacade();
		});

		$this->app->register(WebiEventServiceProvider::class);
	}

	public function boot(Kernel $kernel)
	{
		// Global
		// $kernel->pushMiddleware(GlobalMiddleware::class);

		// Route
		$this->app['router']->aliasMiddleware('webi-role', WebiAuthRoles::class);
		$this->app['router']->aliasMiddleware('webi-json', WebiJsonResponse::class);
		$this->app['router']->aliasMiddleware('webi-nocsrf', WebiVerifyCsrfToken::class);
		$this->app['router']->aliasMiddleware('webi-locale', WebiLocales::class);

		// Create routes
		if(config('webi.settings.routes') == true) {
			$this->loadRoutesFrom(__DIR__.'/../routes/web.php');
		}

		$this->loadViewsFrom(__DIR__.'/../resources/views', 'webi');
		$this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'webi');
		$this->loadMigrationsFrom(__DIR__.'/../database/migrations');

		if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__.'/../config/config.php' => config_path('webi.php'),
			], 'webi-config');

			$this->publishes([
				__DIR__.'/../resources/views' => resource_path('views/vendor/webi')
			], 'webi-email');

			$this->publishes([
				__DIR__.'/../lang' => base_path('lang/vendor/webi')
			], 'webi-lang');

			$this->publishes([
				__DIR__.'/../tests/Webi' => base_path('tests/Webi')
			], 'webi-tests');
		}
	}
}