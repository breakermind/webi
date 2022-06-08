<?php

namespace Webi;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Webi\Http\Middleware\WebiAuthRoles;
use Webi\Http\Middleware\WebiAutoLogin;
use Webi\Http\Middleware\WebiLocales;
use Webi\Http\Middleware\WebiJsonResponse;
use Webi\Http\Middleware\WebiVerifyCsrfToken;
use Webi\Http\Facades\WebiFacade;
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
	}

	public function boot(Kernel $kernel)
	{
		// Global
		// $kernel->pushMiddleware(GlobalMiddleware::class);

		// Route
		$this->app['router']->aliasMiddleware('webi-role', WebiAuthRoles::class);
		$this->app['router']->aliasMiddleware('webi-json', WebiJsonResponse::class);
		$this->app['router']->aliasMiddleware('webi-nocsrf', WebiVerifyCsrfToken::class);

		// Group
		if(config('webi.settings.locales') == true) {
			$this->app['router']->pushMiddlewareToGroup('web', WebiLocales::class);
		}
		if(config('webi.settings.autologin') == true) {
			$this->app['router']->pushMiddlewareToGroup('web', WebiAutoLogin::class);
		}

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
				__DIR__.'/../resources/views' => resource_path('views/vendor/webi')
			], 'webi-config');

			$this->publishes([
				__DIR__.'/../tests/Webi' => base_path('tests/Webi')
			], 'webi-tests');
		}
	}
}