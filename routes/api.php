<?php

use App\Http\Controllers\authController;
use App\Http\Controllers\userController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::get('ana-sayfa', function (Request $request) {
    echo 'Hello Enrich MainPage';
});

Route::get('hata', function (Request $request) {
    echo 'Sistemde bir hata oluştu.';
});

Route::controller(authController::class)->group(function () {
    Route::post('/user/login', 'login');
    Route::post('/user/logout', 'logout');
    Route::post('user/check/token', 'checkToken');
});

Route::controller(userController::class)->group(function () {
    Route::post('/user/new', 'store');
    Route::get('/users', 'index');
});

Route::middleware(['jwt', 'auth:user'])->group(function () {
    Route::get('/core/protected', function () {
        echo 'core protected';
    });
});
