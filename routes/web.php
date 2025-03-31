<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\DynamicFormController;
use App\Http\Responses\Auth\CustomLoginResponse;
use App\Models\OAuthClient;
use App\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// routes/web.php



Route::get('/', function () {
    return redirect('dashboard');
});

Route::get('/oauth/authorize', function () {
    // Get the current request URL
    if (auth()->user()) {
        return app(CustomLoginResponse::class);
    }
    $routeUrl = request()->getRequestUri();
    $client_id = Str::getValueFromRequest($routeUrl, 'client_id');
    $url = config("app.url") . "dashboard/login?client_id=" . $client_id;
    return redirect($url);
});

Route::post('/oauth/allow', [OAuthController::class, 'allowAuthorization'])->name('allow-auth');
Route::get('/oauth/login', [OAuthController::class, 'handleAuthorizationLogin'])->name('handleAuthorizationLogin');
Route::post('/dynamic-form/store', [DynamicFormController::class, 'store']);
Route::get('/dynamic-form/retrieve', [DynamicFormController::class, 'retrieve']);
Route::post('/dynamic-form/update', [DynamicFormController::class, 'update']);
Route::post('/dynamic-form/delete', [DynamicFormController::class, 'delete']);
