<?php
namespace Webi\Http\Middleware;

use Closure;
use Session;
use App;
use Config;

class WebiLocales {

	public function handle($request, Closure $next)
	{
		$lang = Session::get('locale', Config::get('app.locale'));
		App::setLocale($lang);

		return $next($request);
	}
}