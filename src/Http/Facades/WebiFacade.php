<?php

namespace Webi\Http\Facades;

use Illuminate\Support\Facades\Facade;

class WebiFacade extends Facade {
	protected static function getFacadeAccessor() {
		return 'webi-facade';
	}
}