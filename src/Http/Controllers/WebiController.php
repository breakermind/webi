<?php

namespace Webi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Webi\Http\Requests\LoginRequest;
use Webi\Http\Requests\ActivateRequest;
use Webi\Http\Requests\RegisterRequest;
use Webi\Http\Requests\ResetPasswordRequest;
use Webi\Http\Requests\ChangePasswordRequest;
use Webi\Services\Webi;

class WebiController extends Controller
{
	function csrf()
	{
		return (new Webi())->csrf();
	}

	function logged(Request $r)
	{
		return (new Webi())->logged($r);
	}

	function login(LoginRequest $r)
	{
		return (new Webi())->login($r);
	}

	function activate(ActivateRequest $request)
	{
		return (new Webi())->activate($request);
	}

	function register(RegisterRequest $r)
	{
		return (new Webi())->register($r);
	}

	function reset(ResetPasswordRequest $r)
	{
		return (new Webi())->reset($r);
	}

	function change(ChangePasswordRequest $r)
	{
		return (new Webi())->change($r);
	}

	function logout(Request $r)
	{
		return (new Webi())->logout($r);
	}

	function test(Request $r)
	{
		if (Auth::check()) {
			return ['user' => [
				'id' => $r->user()->id,
				'name' => $r->user()->name,
				'email' => $r->user()->email,
			]];
		}

		return ['message' => 'User not authenticated.'];
	}
}