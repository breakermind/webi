<?php

namespace Webi\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;

class WebiAutoLogin
{
	public function handle($request, Closure $next)
	{
		// If not logged
		if(!Auth::check()) {
			// Get cookie
			$sess = $request->cookie('_remeber_token');
			// Check
			if(!empty($sess)) {
				// Get user
				$user = User::where([
					'remember_token' => $sess
				])->whereNotNull('email_verified_at')->whereNull('deleted_at')->first();
				// If error ignore auth
				if(!empty($user) && !empty($user->id) && !empty($user->email)) {
					// Refresh, auth
					$request->session()->regenerate();
					Auth::login($user, true);
				}
			}
		}

		return $next($request);
	}
}
