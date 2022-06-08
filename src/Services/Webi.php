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

	function csrf()
	{
		request()->session()->regenerateToken();

		session(['webi_cnt' => session('webi_cnt') + 1]);

		return response([
			'message' => 'Csrf token created.',
			'counter' => session('webi_cnt')
		]);
	}

	function logged(Request $request)
	{
		$this->loginRememberToken($request);

		if(Auth::user()) {
			return response()->json([
				'message' => 'Authenticated via remember me.',
				'user' => Auth::user(),
			], 200);
		} else {
			throw new Exception("Not authenticated.", 422);
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
				throw new Exception("Confirm email address.", 422);
			}
			return response()->json([
				'message' => 'Authenticated.',
				'user' => Auth::user(),
			], 200);
		} else {
			throw new Exception("Invalid credentials.", 422);
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
			throw new Exception("Can not create user.", 422);
		}

		try {
			Mail::to($user->email)->send(new RegisterMail($user));
		} catch (Exception $e) {
			$user->delete();
			Log::error($e->getMessage());
			throw new Exception("Unable to send e-mail, please try again later.", 422);
		}

		// Event
		WebiUserCreated::dispatch($user, request()->ip());

		return response()->json(['message' => 'Account has been created, please confirm your email address.', 'created' => true], 201);
	}

	function activate(WebiActivateRequest $request)
	{
		$valid = $request->validated();

		try {
			$user = User::where('id', $valid['id'])->whereNotNull('code')->where('code', $valid['code'])->first();
			$this->activateEmail($user);
		} catch (Exception $e) {
			Log::error($e->getMessage());
			throw new Exception("Invalid activation code.", 422);
		}

		return response()->json(['message' => 'Email has been confirmed.']);
	}

	function logout(Request $request)
	{
		try {
			Auth::logout();
			$r->session()->flush();
			$r->session()->invalidate();
			$r->session()->regenerateToken();
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
			throw new Exception("Database error.", 422);
		}

		$password = uniqid();
		$user = $this->updatePassword($user, $password);
		$user = $this->activateEmail($user);

		try {
			Mail::to($user)->send(new PasswordMail($user, $password));
		} catch (Exception $e) {
			Log::error($e->getMessage());
			throw new Exception("Unable to send e-mail, please try again later." . $e->getMessage());
		}

		return response()->json(['message' => 'A new password has been sent to the e-mail address provided.']);
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
				throw new Exception("Database error.", 422);
			}
		} else {
			throw new Exception("Invalid current password.", 422);
		}

		return response()->json(['message' => 'A password has been updated.']);
	}
}