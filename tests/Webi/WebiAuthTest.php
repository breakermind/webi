<?php

namespace Tests\Webi;

use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\Webi\AuthenticatedTestCase;

/*
	php artisan vendor:publish --tag=webi-tests --force
	php artisan test --testsuite=Webi --stop-on-failure
*/
class WebiAuthTest extends AuthenticatedTestCase
{
	use RefreshDatabase;

	protected $authWithRole = 'admin'; // Logged user role

	/** @test */
	function logged_as_admin()
	{
		$this->assertSame($this->user->role, 'admin');
	}

	/** @test */
	function logged_user_data()
	{
		$res = $this->get('web/api/test/admin');

		$res->assertOk();

		$this->assertNotNull($res['user']['email']);
	}

	/** @test */
	function user_change_password()
	{
		$res = $this->postJson('/web/api/change-password', [
			'password_current' => 'password123',
			'password' => 'password1234',
			'password_confirmation' => 'password1234'
		]);

		$res->assertStatus(200)->assertJson([
			'message' => 'A password has been updated.'
		]);

		Auth::logout();

		$res = $this->postJson('/web/api/login', [
			'email' => $this->user->email,
			'password' => 'password1234'
		]);

		$res->assertStatus(200);

		$this->assertNotNull($res['message']);
	}

	/** @test */
	function user_logout()
	{
		$res = $this->getJson('/web/api/logout');

		$res->assertStatus(200)->assertJson([
			'message' => 'Logged out.'
		]);
	}
}