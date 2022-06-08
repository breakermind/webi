<?php

namespace Webi\Http\Middleware;

use Closure;
use Exception;

class WebiLocales
{
	public function handle($request, Closure $next)
	{
		$lang = $request->input('lang') ?? '';

		if(!empty($lang)) {
			app()->setLocale($lang);
			$request->session()->put('locale', $lang);
		} else {
			if(!empty(session('locale'))) {
				app()->setLocale(session('locale'));
			}
		}

		return $next($request);
	}
}