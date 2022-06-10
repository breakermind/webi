<?php

namespace Tests\Webi;

use Tests\TestCase;
use Tests\Webi\AuthenticatedTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

/*
	php artisan vendor:publish --tag=webi-tests --force
	php artisan test --testsuite=Webi --stop-on-failure
*/
class WebiActivateTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function activate_user()
	{
		$user = User::factory()->create(['email_verified_at' => null]);

		$this->assertDatabaseHas('users', [
			'email' => $user->email,
			'code' => $user->code,
		]);

		$res = $this->get('/web/api/activate/'.$user->id.'/'.$user->code.'xxx');

		$res->assertStatus(200)->assertJson([
			'message' => 'Invalid activation code.'
		]);

		$res = $this->get('/web/api/activate/'.$user->id.'/'.$user->code);

		$res->assertStatus(200)->assertJson([
			'message' => 'Email has been confirmed.'
		]);

		$res = $this->get('/web/api/activate/'.$user->id.'/'.$user->code);

		$res->assertStatus(200)->assertJson([
			'message' => 'The email address has already been confirmed.'
		]);

		$db_user = User::where('email', $user->email)->first();

		$this->assertNotNull($db_user->email_verified_at);
	}
}