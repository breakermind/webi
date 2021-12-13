<?php

namespace Webi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Webi\Http\Requests\WebiLoginRequest;
use Webi\Http\Requests\WebiActivateRequest;
use Webi\Http\Requests\WebiRegisterRequest;
use Webi\Http\Requests\WebiResetPasswordRequest;
use Webi\Http\Requests\WebiChangePasswordRequest;
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

	function login(WebiLoginRequest $r)
	{
		return (new Webi())->login($r);
	}

	function activate(WebiActivateRequest $request)
	{
		return (new Webi())->activate($request);
	}

	function register(WebiRegisterRequest $r)
	{
		return (new Webi())->register($r);
	}

	function reset(WebiResetPasswordRequest $r)
	{
		return (new Webi())->reset($r);
	}

	function change(WebiChangePasswordRequest $r)
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