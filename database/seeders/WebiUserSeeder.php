<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\User;

class WebiUserSeeder extends Seeder
{
	public function run()
	{
		User::factory()->create([
			'email' => 'user@app.xx',
			'newsletter_on' => 1,
			'role' => 'user',
		]);

		User::factory()->create([
			'email' => 'worker@app.xx',
			'newsletter_on' => 0,
			'role' => 'worker',
		]);

		User::factory()->create([
			'email' => 'admin@app.xx',
			'newsletter_on' => 0,
			'role' => 'admin',
		]);
	}
}