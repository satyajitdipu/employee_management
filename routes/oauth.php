<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Auth\TokenController;


Route::post('/oauth/token', [OAuthController::class, 'generateToken'])->name('generateToken');
Route::get('/userinfo', [OAuthController::class, 'getUserInfo'])->name('getUserInfo');
Route::post('/validate-token', [TokenController::class, 'validateToken'])->name('validateToken');
