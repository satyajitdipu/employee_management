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

// Performance Review Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('performance-reviews', \App\Http\Controllers\PerformanceReviewController::class);
    Route::get('performance-reviews/my-reviews', [\App\Http\Controllers\PerformanceReviewController::class, 'myReviews']);
    Route::get('performance-reviews/statistics', [\App\Http\Controllers\PerformanceReviewController::class, 'statistics']);
    Route::get('performance-reviews/report', [\App\Http\Controllers\PerformanceReviewController::class, 'report']);
    Route::get('performance-reviews/history', [\App\Http\Controllers\PerformanceReviewController::class, 'history']);
    Route::post('performance-reviews/bulk', [\App\Http\Controllers\PerformanceReviewController::class, 'bulkCreate']);
    Route::get('performance-reviews/periods', [\App\Http\Controllers\PerformanceReviewController::class, 'periods']);
    Route::get('performance-reviews/categories', [\App\Http\Controllers\PerformanceReviewController::class, 'categories']);
    Route::post('performance-reviews/{performanceReview}/submit', [\App\Http\Controllers\PerformanceReviewController::class, 'submit']);
    Route::post('performance-reviews/{performanceReview}/complete', [\App\Http\Controllers\PerformanceReviewController::class, 'complete']);
});
