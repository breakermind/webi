<?php

use Illuminate\Support\Facades\Route;
use Webi\Http\Controllers\WebiController;

Route::prefix('web/api')->name('web.api.')->middleware(['web', 'webi-locale', 'webi-autologin'])->group(function() {

	// Public routes
	Route::post('/login', [WebiController::class, 'login'])->name('login');
	Route::post('/register', [WebiController::class, 'register'])->name('register');
	Route::post('/reset', [WebiController::class, 'reset'])->name('reset');
	Route::get('/activate/{id}/{code}', [WebiController::class, 'activate'])->name('activate');
	Route::get('/logged', [WebiController::class, 'logged'])->name('logged');
	Route::get('/csrf', [WebiController::class, 'csrf'])->name('csrf');
	Route::get('/locale/{locale}', [WebiController::class, 'locale'])->name('locale');

	// Only logged users
	Route::middleware(['auth', 'webi-role:admin|worker|user'])->group(function () {
		Route::get('/logout', [WebiController::class, 'logout'])->name('logout');
		Route::post('/change-password', [WebiController::class, 'change'])->name('change-password');
		Route::get('/test/user', [WebiController::class, 'test'])->name('test.user');
	});

	// Only logged admin
	Route::middleware(['auth', 'webi-role:admin'])->group(function () {
		Route::get('/test/admin', [WebiController::class, 'test'])->name('test.admin');
	});

});