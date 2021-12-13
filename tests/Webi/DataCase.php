<?php

namespace Tests\Webi;

use Tests\TestCase;
use App\Models\User;

class DataCase extends TestCase
{
	public $data = [];

	function __construct()
	{
		parent::__construct();

		$this->data = [
			'name' => 'Maiolka',
			'email' => 'test.user@woo.xx',
			'password' => 'password123',
			'password_confirmation' => 'password123'
		];

		// Set global variable accesible from all tests
		global $authToken;
	}

	function setToken($token)
	{
		global $authToken;
		$authToken = $token;
	}

	function getToken()
	{
		global $authToken;
		return $authToken;
	}

	function setPass($str)
	{
		global $authPass;
		$authPass = $str;
	}

	function getPass()
	{
		global $authPass;
		return $authPass;
	}

	function deleteUser()
	{
		$user = User::where('email', $this->data['email'])->first();
		if($user) {
			$user->delete();
		}
	}

	function getPassword($html)
	{
		preg_match('/word>[a-zA-Z0-9]+<\/pass/', $html, $matches, PREG_OFFSET_CAPTURE);
		return str_replace(['word>', '</pass'], '', end($matches)[0]);
	}

	function refreshDatabase()
	{
		$this->artisan('db:seed');
	}
}
