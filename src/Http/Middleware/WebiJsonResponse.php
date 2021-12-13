<?php

namespace Webi\Http\Middleware;

use Closure;

/**
 * Add to Kernel middleware
 * 'web' => [
 * 		\App\Http\Middleware\WebiJsonResponse::class,
 * 		...
 * ]
 *
 * Add to routes
 * Route::fallback(function (){ abort(404, 'API resource not found'); });
 */
class WebiJsonResponse
{
	public function handle($request, Closure $next, $role = '')
	{
		$request->headers->set('Accept', 'application/json');

		return $next($request);
	}
}
