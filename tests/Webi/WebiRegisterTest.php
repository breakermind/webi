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
class WebiRegisterTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function db_create_user()
	{
		$user = User::factory()->create();

		$this->assertNotNull($user);

		$this->assertDatabaseHas('users', [
			'name' => $user->name,
			'email' => $user->email,
		]);
	}

	/** @test */
	function http_create_user()
	{
		$pass = 'password123';

		$user = User::factory()->make();

		Event::fake([MessageSent::class]);

		$res = $this->postJson('/web/api/register', [
			'name' => $user->name,
			'email' => $user->email,
			'password' => $pass,
			'password_confirmation' => $pass,
		]);

		$res->assertStatus(201)->assertJson(['created' => true]);

		$this->assertDatabaseHas('users', [
			'name' => $user->name,
			'email' => $user->email,
		]);

		$db_user = User::where('email', $user->email)->first();

		$this->assertTrue(Hash::check($pass, $db_user->password));

		Event::assertDispatched(MessageSent::class, function ($e) {
			$html = $e->message->getBody();
			$this->assertStringContainsString("/web/api/activate", $html);
			$this->assertMatchesRegularExpression('/web\/api\/activate\/[0-9]+\/[a-z0-9]+"/i', $html);
			return true;
		});
	}

	function test_error_duplicate_email()
	{
		$user = User::factory()->create();

		$res = $this->postJson('/web/api/register', [
			'name' => $user->name,
			'email' => $user->email,
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$res->assertStatus(422)->assertJsonMissing(['created'])->assertJson([
			'message' => 'The email has already been taken.'
		]);
	}

	function test_error_name()
	{
		$user = User::factory()->make();

		$res = $this->postJson('/web/api/register', [
			'name' => '',
			'email' => $user->email,
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$res->assertStatus(422)->assertJsonMissing(['created'])->assertJson([
			'message' => 'The name field is required.'
		]);
	}

	function test_error_email()
	{
		$user = User::factory()->make();

		$res = $this->postJson('/web/api/register', [
			'name' => $user->name,
			'email' => '',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$res->assertStatus(422)->assertJsonMissing(['created'])->assertJson([
			'message' => 'The email field is required.'
		]);
	}

	function test_error_password()
	{
		$user = User::factory()->make();

		$res = $this->postJson('/web/api/register', [
			'name' => $user->name,
			'email' => $user->email,
			'password' => '',
			'password_confirmation' => 'password123',
		]);

		$res->assertStatus(422)->assertJsonMissing(['created'])->assertJson([
			'message' => 'The password field is required.'
		]);
	}

	function test_error_password_confirmation()
	{
		$user = User::factory()->make();

		$res = $this->postJson('/web/api/register', [
			'name' => $user->name,
			'email' => $user->email,
			'password' => 'password123',
			'password_confirmation' => '',
		]);

		$res->assertStatus(422)->assertJsonMissing(['created'])->assertJson([
			'message' => 'The password confirmation does not match.'
		]);
	}
}