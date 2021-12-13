<?php

namespace Webi\Services;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Webi\Mail\PasswordMail;
use Webi\Mail\RegisterMail;
use Webi\Http\Traits\WebiAuthHelper;
use Webi\Http\Requests\LoginRequest;
use Webi\Http\Requests\ActivateRequest;
use Webi\Http\Requests\RegisterRequest;
use Webi\Http\Requests\ResetPasswordRequest;
use Webi\Http\Requests\ChangePasswordRequest;

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
			return response()->json(['message' => 'Authenticated via remember me.'], 200);
		} else {
			throw new Exception("Not authenticated.", 422);
		}
	}

	function login(LoginRequest $request)
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
			} catch (Exception $e) {
				Log::error($e->getMessage());
				throw new Exception("Confirm email address.", 422);
			}
			return response()->json(['message' => 'Authenticated.'], 200);
		} else {
			throw new Exception("Invalid credentials.", 422);
		}
	}

	function register(RegisterRequest $request)
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

		return response()->json(['message' => 'Account has been created, please confirm your email address.', 'created' => true], 201);
	}

	function activate(ActivateRequest $request)
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
		Auth::logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();
		return response()->json(['message' => 'Logged out.']);
	}

	function reset(ResetPasswordRequest $request)
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

	function change(ChangePasswordRequest $request)
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