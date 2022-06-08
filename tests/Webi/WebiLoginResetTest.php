<?php

namespace Tests\Webi;

use Tests\TestCase;
use Tests\Webi\AuthenticatedTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

/*
	php artisan vendor:publish --tag=webi-tests --force
	php artisan test --testsuite=Webi --stop-on-failure
*/
class WebiLoginResetTest extends TestCase
{
	use RefreshDatabase;

	function getPassword($html)
	{
		preg_match('/word>[a-zA-Z0-9]+<\/pass/', $html, $matches, PREG_OFFSET_CAPTURE);
		return str_replace(['word>', '</pass'], '', end($matches)[0]);
	}

	/** @test */
	function user_reset_password()
	{
		Event::fake([MessageSent::class]);

		$user = User::factory()->create();

		$res = $this->postJson('/web/api/reset', [
			'email' => $user->email
		]);

		$res->assertStatus(200)->assertJson([
			'message' => 'A new password has been sent to the e-mail address provided.'
		]);

		Event::assertDispatched(MessageSent::class, function ($e) use ($user) {
			// $html = $e->message->getBody();
			$html = $e->message->getHtmlBody();

			$this->assertMatchesRegularExpression('/word>[a-zA-Z0-9]+<\/pass/', $html);

			$pass = $this->getPassword($html);

			$res = $this->postJson('/web/api/login', [
				'email' => $user->email,
				'password' => $pass,
			]);

			$res->assertStatus(200)->assertJson([
				'message' => 'Authenticated.'
			]);

			return true;
		});
	}

	/** @test */
	function login_user()
	{
		Auth::logout();

		$user = User::factory()->create([
			'password' => Hash::make('hasło1233456')
		]);

		$this->assertDatabaseHas('users', [
			'name' => $user->name,
			'email' => $user->email,
		]);

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => 'hasło1233456'
		]);

		$res->assertStatus(200);

		$this->assertNotNull($res['message']);
	}

	/** @test */
	function login_remember_me()
	{
		$user = User::factory()->create();

		$res = $this->postJson('/web/api/login', [
			'email' => $user->email,
			'password' => 'password123',
			'remember_me' => 1,
		]);

		$res->assertStatus(200)->assertJson([
			'message' => 'Authenticated.'
		]);

		// Auth::logout();

		$token = User::where('email', $user->email)->first()->remember_token;

		$res = $this->withCookie('_remeber_token', $token)->get('/web/api/logged');

		$res->assertStatus(200)->assertJson([
			'message' => 'Authenticated via remember me.'
		]);
	}

	/** @test */
	function login_remember_me_error()
	{
		// Auth::logout();

		$res = $this->getJson('/web/api/logged');

		$res->assertStatus(422)->assertJson([
			'message' => 'Not authenticated.'
		]);
	}

	/** @test */
	function csrf_session_counter()
	{
		$res = $this->get('/web/api/csrf');

		$res->assertStatus(200)->assertJson([
			'message' => 'Csrf token created.',
			'counter' => 1,
		]);

		$token = [
			$res->headers->getCookies()[0]->getName() => $res->headers->getCookies()[0]->getValue(),
			$res->headers->getCookies()[1]->getName() => $res->headers->getCookies()[1]->getValue(),
			// $res->headers->getCookies()[2]->getName() => $res->headers->getCookies()[2]->getValue(),
		];

		$res = $this->withCookies($token)->get('/web/api/csrf');

		$res->assertStatus(200)->assertJson([
			'message' => 'Csrf token created.',
			'counter' => 2,
		]);

		// dd($res->headers->getCookies());
	}
}