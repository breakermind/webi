<?php

namespace Webi\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
 * Add in app/Http/Kernel.php
 *
 * protected $routeMiddleware = [
 * 		'webi-role' => \App\Http\Middleware\WebiAuthRole::class,
 * ]
 *
 * then
 * Route::middleware(['auth', 'webi-role:user|admin|worker']);
 */
class WebiAuthRoles
{
	public function handle($request, Closure $next, $role = '')
	{
		$roles = array_filter(explode('|',$role));

		if (! empty($roles)) {
			if (Auth::check()) {
				$user = Auth::user();
				if (! in_array($user->role, $roles)) {
					throw new Exception("Unauthorized Role", 401);
				}
			} else {
				throw new Exception("Unauthorized User", 401);
			}
		}

		return $next($request);
	}
}
