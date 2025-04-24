<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\OAuthController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Server\AuthorizationServer;
use App\UserEntity;
use Illuminate\Http\Request;
use App\Http\Controllers\AttendanceController;


// ...

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/oauth/server', [OAuthController::class, 'handleAuthorizationOauth'])->name('handleAuthorizationOauth');
// Route::post('/token', [OAuthController::class, 'generateToken'])->name('generateToken');
// Route::post('/oauth/auth', [OAuthController::class, 'handleAuthorizationLogin'])->name('handleAuthorizationLogin');

Route::resource('attendances', AttendanceController::class);
