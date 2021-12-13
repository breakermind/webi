<?php

namespace Webi\Http\Traits;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

trait WebiAuthHelper
{
	function verifyEmail(?User $user)
	{
		$this->checkUser($user);

		if(empty($user->email_verified_at)) {
			throw new Exception("Account not activated.", 422);
		}

		return $user;
	}

	function activateEmail(?User $user)
	{
		$this->checkUser($user);

		$user->email_verified_at = now();
		$user->save();

		return $user;
	}

	function createCode(?User $user)
	{
		$this->checkUser($user);

		$user->code = uniqid();
		$user->ip = request()->ip();
		$user->save();

		return $user;
	}

	function updatePassword(?User $user, $password)
	{
		$this->checkUser($user);

		$user->password = Hash::make($password);
		$user->ip = request()->ip();
		$user->save();

		return $user;
	}

	function checkUser(?User $user)
	{
		if(empty($user) || empty($user->id) || empty($user->email)) {
			throw new Exception("User not found.", 422);
		}

		return $user;
	}

	function cleanName($name)
	{
		return htmlentities(strip_tags($name), ENT_QUOTES, 'utf-8');
	}

	function jsonPretty($user)
	{
		return $user->toJson(JSON_PRETTY_PRINT);
	}

	function setRemeberToken(User $user)
	{
		// $name, $val, $minutes, $path, $domain, $secure, $httpOnly
		Cookie::queue(
			'_remember_token',
			$user->remember_token,
			env('APP_REMEBER_ME_MINUTES', 3592000),
			'/',
			'.'.request()->getHost(),
			request()->secure(),
			true
		);
	}

	function loginRememberToken(Request $request)
	{
		$sess = $request->cookie('_remeber_token');
		if(!empty($sess)) {
			$user = User::where([
				'remember_token' => $sess
			])->whereNotNull('email_verified_at')->first();

			if ($user) {
				$request->session()->regenerate();
				Auth::login($user, true);
				if(Auth::check()) {
					return Auth::user();
				}
			}
		}
		return null;
	}
}