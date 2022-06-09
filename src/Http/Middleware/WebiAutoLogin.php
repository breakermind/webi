<?php

namespace Webi\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Models\User;

class WebiAutoLogin
{
	public function handle($request, Closure $next)
	{
		if(!Auth::check()) {
			$sess = Cookie::get('_remeber_token');
			if(!empty($sess)) {
				$user = User::where([
					'remember_token' => $sess
				])->whereNotNull('email_verified_at')
				  ->whereNull('deleted_at')
				  ->first();

				if ($user instanceof User) {
					$request->session()->regenerate();
					Auth::login($user, true);
				}
			}
		}

		return $next($request);
	}
}
