<?php

use Illuminate\Support\Facades\Route;
use Webi\Http\Controllers\WebiController;

Route::prefix('web/api')->name('web.api.')->middleware(['web'])->group(function() {

	// Public routes
	Route::get('/csrf', [WebiController::class, 'csrf'])->name('csrf');
	Route::post('/login', [WebiController::class, 'login'])->name('login');
	Route::post('/register', [WebiController::class, 'register'])->name('register');
	Route::post('/reset', [WebiController::class, 'reset'])->name('reset');
	Route::get('/activate/{id}/{code}', [WebiController::class, 'activate'])->name('activate');
	Route::get('/logged', [WebiController::class, 'logged'])->name('logged');

	// Only logged users
	Route::middleware(['auth', 'webi-role:admin|worker|user'])->group(function () {
		Route::get('/logout', [WebiController::class, 'logout'])->name('logout');
		Route::post('/change-password', [WebiController::class, 'change'])->name('change-password');
		Route::get('/test', [WebiController::class, 'test'])->name('test');
	});

	// Only logged admin
	Route::middleware(['auth', 'webi-role:admin'])->group(function () {
		//
	});

});