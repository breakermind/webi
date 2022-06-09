<?php

namespace Webi\Services;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Webi\Mail\PasswordMail;
use Webi\Mail\RegisterMail;
use Webi\Http\Traits\WebiAuthHelper;
use Webi\Http\Requests\WebiLoginRequest;
use Webi\Http\Requests\WebiActivateRequest;
use Webi\Http\Requests\WebiRegisterRequest;
use Webi\Http\Requests\WebiResetPasswordRequest;
use Webi\Http\Requests\WebiChangePasswordRequest;
use Webi\Events\WebiUserCreated;
use Webi\Events\WebiUserLogged;

class Webi
{
	use WebiAuthHelper;

	function __construct() {
		app()->setLocale(session('locale', config('app.locale')));
	}

	function csrf()
	{
		request()->session()->regenerateToken();

		session(['webi_cnt' => session('webi_cnt') + 1]);

		return response([
			'message' => trans('Csrf token created.'),
			'counter' => session('webi_cnt'),
			'locale' => app()->getLocale()
		]);
	}

	function locale($locale)
	{
		if(!empty($locale) && strlen($locale) == 2){
			session(['locale' => $locale]);
			app()->setLocale($locale);

			return response()->json([
				'message' => trans('Locale has been changed.'),
				'locale' => app()->getLocale(),
			], 200);
		} else {
			throw new Exception(trans("Locale has not been changed."), 422);
		}
	}

	function logged(Request $request)
	{
		$this->loginRememberToken($request);

		if(Auth::check()) {
			// Event
			WebiUserLogged::dispatch(Auth::user(), request()->ip());

			return response()->json([
				'message' => trans('Authenticated via remember me.'),
				'user' => Auth::user(),
				'locale' => app()->getLocale(),
			], 200);
		} else {
			return response()->json([
				'message' => trans('Not authenticated.'),
				'locale' => app()->getLocale(),
			], 422);
		}
	}

	function login(WebiLoginRequest $request)
	{
		$valid = $request->validated();
		$remember = !empty($valid['remember_me']) ? true : false;
		unset($valid['remember_me']);

		if (Auth::attempt($valid, $remember)) {
			try {
				$this->verifyEmail($request->user());

				if(!empty($remember)) {
					$this->setRemeberToken(Auth::user());
				}

				$request->session()->regenerate();

				// Event
				WebiUserLogged::dispatch(Auth::user(), request()->ip());

			} catch (Exception $e) {
				Log::error($e->getMessage());
				throw new Exception(trans("Confirm email address."), 422);
			}

			return response()->json([
				'message' => trans('Authenticated.'),
				'user' => Auth::user(),
				'current' => app()->getLocale(),
			], 200);
		} else {
			return response()->json([
				'message' => trans('Invalid credentials.'),
				'current' => app()->getLocale(),
				'session' => session('locale')
			], 422);
		}
	}

	function register(WebiRegisterRequest $request)
	{
		$user = null;
		$valid = $request->validated();

		try {
			$user = User::create([
				'name' => $this->cleanName($valid['name']),
				'email' => $valid['email'],
				'password' => Hash::make($valid['password'])
			]);
			$user = $this->createCode($user);
		} catch (Exception $e) {
			Log::error($e->getMessage());
			throw new Exception(trans("Can not create user."), 422);
		}

		try {
			Mail::to($user->email)->send(new RegisterMail($user));
		} catch (Exception $e) {
			$user->delete();
			Log::error($e->getMessage());
			throw new Exception(trans("Unable to send e-mail, please try again later."), 422);
		}

		// Event
		WebiUserCreated::dispatch($user, request()->ip());

		return response()->json(['message' => trans('Account has been created, please confirm your email address.'), 'created' => true], 201);
	}

	function activate(WebiActivateRequest $request)
	{
		$valid = $request->validated();

		try {
			$user = User::where('id', $valid['id'])->whereNotNull('code')->where('code', $valid['code'])->first();
			$this->activateEmail($user);
		} catch (Exception $e) {
			Log::error($e->getMessage());
			throw new Exception(trans("Invalid activation code."), 422);
		}

		return response()->json(['message' => trans('Email has been confirmed.')]);
	}

	function logout(Request $request)
	{
		try {
			if(Auth::check()) {
				Auth::user()->update(['remember_token' => null]);
			}
			Auth::logout();
			$request->session()->flush();
			$request->session()->invalidate();
			$request->session()->regenerateToken();
			session(['locale' => config('app.locale')]);
		} catch (Exception $e) {
			report($e);
			return response()->json(['message' => trans('Logged out error.')]);
		}
		return response()->json(['message' => trans('Logged out.')]);
	}

	function reset(WebiResetPasswordRequest $request)
	{
		$user = null;
		$valid = $request->validated();

		try {
			$user = User::where('email', $valid['email'])->first();
		} catch (Exception $e) {
			Log::error($e->getMessage());
			throw new Exception(trans("Database error."), 422);
		}

		$password = uniqid();
		$user = $this->updatePassword($user, $password);
		$user = $this->activateEmail($user);

		try {
			Mail::to($user)->send(new PasswordMail($user, $password));
		} catch (Exception $e) {
			Log::error($e->getMessage());
			throw new Exception(trans("Unable to send e-mail, please try again later.") . $e->getMessage());
		}

		return response()->json(['message' => trans('A new password has been sent to the e-mail address provided.')]);
	}

	function change(WebiChangePasswordRequest $request)
	{
		$valid = $request->validated();

		if (Hash::check($valid['password_current'], Auth::user()->password)) {
			try {
				User::where(['email' => $request->user()->email])
					->update(['password' => Hash::make($request->input('password'))]);
			} catch (Exception $e) {
				Log::error($e->getMessage());
				throw new Exception(trans("Database error."), 422);
			}
		} else {
			throw new Exception(trans("Invalid current password."), 422);
		}

		return response()->json(['message' => trans('A password has been updated.')]);
	}
}