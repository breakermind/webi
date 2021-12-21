<?php

namespace Webi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Database\Factories\WebiUserFactory;
use Webi\Http\Traits\WebiHasRoles;

class WebiUser extends Authenticatable
{
	use HasFactory, Notifiable, WebiHasRoles;

	protected $fillable = [
		'name',
		'email',
		'password',
		'email_verified_at',
		'remember_token',
		'role',
		'code',
		'ip',
	];

	protected $hidden = [
		'code',
		'password',
		'remember_token',
	];

	protected static function newFactory()
	{
		return WebiUserFactory::new();
	}

	protected function serializeDate(\DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}
}
