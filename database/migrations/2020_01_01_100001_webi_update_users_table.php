<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WebiUpdateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function (Blueprint $table) {
			if (!Schema::hasColumn('users', 'email_verified_at')) {
				$table->timestamp('email_verified_at')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'role')) {
				$table->enum('role', ['user','worker','admin'])->nullable()->default('user');
			}
			if (!Schema::hasColumn('users', 'code')) {
				$table->string('code', 128)->unique()->nullable(true);
			}
			if (!Schema::hasColumn('users', 'ip')) {
				$table->string('ip')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'remember_token')) {
				$table->string('remember_token')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'newsletter_on')) {
				$table->tinyInteger('newsletter_on')->nullable(true)->default(1);
			}
			if (!Schema::hasColumn('users', 'deleted_at')) {
				$table->softDeletes();
			}
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(['code', 'ip', 'role', 'remember_token', 'deleted_at']);
		});
	}
}
