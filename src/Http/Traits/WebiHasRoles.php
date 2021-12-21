<?php

namespace Webi\Http\Traits;

trait WebiHasRoles
{
	protected $mainRole = 'user';

	protected $allowedRoles = [
		'worker',
		'admin',
		'user'
	];

	function setRoleAttribute($role)
	{
		if(in_array($role, $this->allowedRoles))  {
			$this->attributes['role'] = $role;
		} else {
			$this->attributes['role'] = $this->mainRole;
		}
	}

	function getRoleAttribute()
	{
		return $this->attributes['role'];
	}

	public function scopeHasRole($query, $role = 'user')
	{
		return $query->where('role', $role);
	}
}
