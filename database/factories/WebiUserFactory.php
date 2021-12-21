<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class WebiUserFactory extends Factory
{
	protected $model = User::class;

	public function definition()
	{
		return [
			'name' => $this->faker->name(),
			'email' => uniqid().'@app.xx',
			'role' => 'user',
			'email_verified_at' => now(),
			'password' => Hash::make('password123'),
			'remember_token' => Str::random(50),
			'code' => Str::random(10),
			'ip' => '127.0.0.127',
		];
	}

	public function role($role = 'user')
	{
		return $this->state(function (array $attributes) use ($role) {
			return [
				'role' => $role,
			];
		});
	}
}
