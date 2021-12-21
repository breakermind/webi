<?php

namespace Tests\Webi;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\WebiSeeder;
use App\Models\User;

abstract class AuthenticatedTestCase extends TestCase
{
	use RefreshDatabase; // Refresh db before each test

	protected $user; // Logged user obj

	protected $authWithRole = 'user'; // Logged user role

	protected $seed = true; // Run seeder before each test.

	protected $seeder = WebiSeeder::class; // Choose seeder class

	protected function setUp(): void
	{
		parent::setUp(); // Run parent setUp

		$this->user = User::factory()->role($this->authWithRole)->create(); // Create user in db

		$this->actingAs($this->user); // Login user
	}

	function seedWebi()
	{
		$this->seed(WebiSeeder::class); // Run db seeder
	}

	function getPassword($html)
	{
		preg_match('/word>[a-zA-Z0-9]+<\/pass/', $html, $matches, PREG_OFFSET_CAPTURE);
		return str_replace(['word>', '</pass'], '', end($matches)[0]);
	}
}