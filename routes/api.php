<?php

use App\Http\Controllers\AdressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
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

Route::controller(AuthController::class)->group(function () {
    Route::post('/user/login', 'login');
    Route::post('/user/logout', 'logout');
    Route::post('user/check/token', 'checkToken');
});

Route::controller(UserController::class)->group(function () {
    Route::post('/user/new', 'store');
    Route::get('/users', 'index');
    Route::get('/user/{user_id}', 'show')->where('id', '[0-9]+');
    Route::put('/user/{user_id}', 'update')->where('id', '[0-9]+');
    Route::delete('/user/delete/{user_id}', 'destroy')->where('id', '[0-9]+');;
});

Route::controller(AdressController::class)->group(function () {
    Route::post('/address/new', 'store');
    Route::get('/address', 'index');
    Route::get('/address/{address_id}', 'show')->where('id', '[0-9]+');
    Route::put('/address/{address_id}', 'update')->where('id', '[0-9]+');
});

// Route::middleware(['jwt', 'auth:user'])->group(function () {
//     Route::controller(userController::class)->group(function () {
//         Route::put('/user/{user_id}', 'update')->where('id', '[0-9]+');
//Route::get('/users', 'index');

//     });

//     Route::get('/core/protected', function () {
//         echo 'core protected';
//     });
// });
