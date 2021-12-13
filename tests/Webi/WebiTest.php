<?php

namespace Tests\Webi;

use App\Models\User;
use Tests\Webi\DataCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Event;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;

/*
	php artisan test --testsuite=Webi --stop-on-failure
*/
class WebiTest extends DataCase
{
	// use RefreshDatabase;

	/* Test OK response */

	function test_register_user()
	{
		$this->deleteUser();

		Event::fake([MessageSent::class]);

		$response = $this->postJson('/web/api/register', $this->data);
		$response->assertStatus(201)->assertJson(['created' => true]);
		$this->assertDatabaseHas('users', ['email' => $this->data['email']]);

		Event::assertDispatched(MessageSent::class, function ($e) {
			$html = $e->message->getBody();
			$this->assertStringContainsString("/web/api/activate", $html);
			$this->assertMatchesRegularExpression('/web\/api\/activate\/[0-9]+\/[a-z0-9]+"/i', $html);
			return true;
		});
	}

	function test_activate_user()
	{
		$this->assertDatabaseHas('users', ['email' => $this->data['email']]);
		$user = User::where('email', $this->data['email'])->get()->first();
		$response = $this->get('/web/api/activate/'.$user->id.'/'.$user->code);
		$response->assertStatus(200)->assertJson(['message' => 'Email has been confirmed.']);
		$this->assertDatabaseHas('users', ['email' => $this->data['email']]);
		$user = User::where('email', $this->data['email'])->get()->first();
		$this->assertNotNull($user->email_verified_at);
	}

	function test_user_login_token()
	{
		$response = $this->postJson('/web/api/login', $this->data);
		$response->assertStatus(200);
		$this->assertNotNull($response['message']);

		// $this->setToken($response['token']);
	}

	function test_user_data()
	{
		$user = User::first();
		$response = $this->actingAs($user)->get('web/api/test');
		$response->assertStatus(200);
		$this->assertNotNull($response['user']['email']);
	}

	function test_user_change_password()
	{
		$data = [
			'password_current' => $this->data['password'],
			'password' => 'password1234',
			'password_confirmation' => 'password1234'
		];

		$user = User::first();
		$response = $this->actingAs($user)->postJson('/web/api/change-password', $data);
		$response->assertStatus(200)->assertJson(['message' => 'A password has been updated.']);
	}

	function test_user_login_after_pass_change()
	{
		$this->data['password'] = 'password1234';
		$this->data['password_confirmation'] = 'password1234';

		$response = $this->postJson('/web/api/login', $this->data);
		$response->assertStatus(200);
		$this->assertNotNull($response['message']);
	}

	function test_user_reset_password()
	{
		Event::fake([MessageSent::class]);

		$response = $this->postJson('/web/api/reset', ['email' => $this->data['email']]);
		$response->assertStatus(200)->assertJson(['message' => 'A new password has been sent to the e-mail address provided.']);

		Event::assertDispatched(MessageSent::class, function ($e) {
			$html = $e->message->getBody();
			$this->assertMatchesRegularExpression('/word>[a-zA-Z0-9]+<\/pass/', $html);

			// password
			$pass = $this->getPassword($html);
			$this->setPass($pass);

			return true;
		});
	}

	function test_user_login_after_pass_reset()
	{
		$this->data['password'] = $this->getPass();
		$response = $this->postJson('/web/api/login', $this->data);
		$response->assertStatus(200)->assertJson(['message' => 'Authenticated.']);
		// $this->assertNotNull($response['message']);
	}

	function test_user_logout()
	{
		$user = User::first();
		$response = $this->actingAs($user)->getJson('/web/api/logout');
		$response->assertStatus(200)->assertJson(['message' => 'Logged out.']);
	}

	function test_user_login_remember_me_error()
	{
		$response = $this->getJson('/web/api/logged');
		$response->assertStatus(422)->assertJson(['message' => 'Not authenticated.']);
	}

	function test_user_login_with_remember_me()
	{
		$this->data['remember_me'] = 1;
		$this->data['password'] = $this->getPass();
		$response = $this->postJson('/web/api/login', $this->data);
		$response->assertStatus(200)->assertJson(['message' => 'Authenticated.']);

		$this->disableCookieEncryption();
		$this->setToken([
			$response->headers->getCookies()[0]->getName() => $response->headers->getCookies()[0]->getValue(),
			$response->headers->getCookies()[1]->getName() => $response->headers->getCookies()[1]->getValue(),
			$response->headers->getCookies()[2]->getName() => $response->headers->getCookies()[2]->getValue(),
		]);
	}

	function test_user_login_remember_me()
	{
		// $c = $this->getToken();
		// $response = $this->withSession($c)->withCookies($c)->getJson('/web/api/logged');
		$token = User::first()->remember_token;
		$response = $this->withSession(['_remeber_token' => $token])->getJson('/web/api/logged');
		$response->assertStatus(200)->assertJson(['message' => 'Authenticated via remember me.']);
	}

	public function test_user_details_can_be_retrieved()
	{
		$user = User::first();
		$response = $this->actingAs($user)->get('/web/api/test');
		$response->assertOk();

		// $response = $this->actingAs($user)
		// ->withSession(['foo' => 'bar'])
		// ->withCookies(['color' => 'black'])
		// ->get('/web/api/test')
		// ->getJson('/web/api/webi-test', [])
		// ->see('Hello, '.$user->name);

		// $response = $this->get('/web/api/webi-test');
		// $response->assertOk();
	}

	/* Test ERROR response */

	function test_register_error_duplicate_email()
	{
		$response = $this->postJson('/web/api/register', $this->data);
		$response->assertStatus(422)->assertJsonMissing(['created'])->assertJson(['message' => 'The email has already been taken.']);
	}

	function test_register_error_name()
	{
		$this->deleteUser();

		unset($this->data['name']);
		$response = $this->postJson('/web/api/register', $this->data);
		$response->assertStatus(422)->assertJsonMissing(['created'])->assertJson(['message' => 'The name field is required.']);
	}

	function test_register_error_email()
	{
		unset($this->data['email']);
		$response = $this->postJson('/web/api/register', $this->data);
		$response->assertStatus(422)->assertJsonMissing(['created'])->assertJson(['message' => 'The email field is required.']);
	}

	function test_register_error_password()
	{
		unset($this->data['password']);
		$response = $this->postJson('/web/api/register', $this->data);
		$response->assertStatus(422)->assertJsonMissing(['created'])->assertJson(['message' => 'The password field is required.']);
	}

	function test_register_error_password_confirmation()
	{
		unset($this->data['password_confirmation']);
		$response = $this->postJson('/web/api/register', $this->data);
		$response->assertStatus(422)->assertJsonMissing(['created'])->assertJson(['message' => 'The password confirmation does not match.']);
	}
}