<?php

use Illuminate\Support\Facades\Route;

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

Route::redirect('/', 'https://shanwo.cc');

//微信公众号验证
Route::get('MP_verify_wOQevRdcPyMeqMJL.txt', function () {
    return 'wOQevRdcPyMeqMJL';
});

Route::prefix('test')->group(function () {
    Route::get('check', 'TestController@check');
    Route::get('info', 'TestController@info');
    Route::get('all', 'TestController@all');
    Route::get('image', 'TestController@image');
});
Route::namespace('Api')->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('info', 'UserController@info');
    });
});
