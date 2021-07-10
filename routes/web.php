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

//测试接口
Route::prefix('test')->group(function () {
    Route::get('check', 'TestController@check');
    Route::get('info', 'TestController@info');
    Route::get('all', 'TestController@all');
    Route::get('image', 'TestController@image');
});
//文件上传
Route::prefix('upload')->group(function () {
    Route::post('file', 'UploadController@file');
});
//地区信息
Route::prefix('area')->group(function () {
    Route::get('lists', 'AreaController@lists');
});
//模板
Route::prefix('template')->group(function () {
    Route::get('lists', 'TemplateController@lists');
    Route::get('info', 'TemplateController@info');
});

//链接
Route::prefix('link')->group(function () {
    Route::get('lists', 'LinkController@lists');
    Route::get('info', 'LinkController@info');
});

//页面
Route::prefix('page')->group(function () {
    Route::get('lists', 'PageController@lists');
    Route::get('info', 'PageController@info');
    Route::post('add', 'PageController@add');
    Route::post('edit', 'PageController@edit');
    Route::post('status', 'PageController@status');
});

//关注
Route::prefix('attention')->group(function () {
    Route::get('lists', 'AttentionController@lists');
    Route::post('add', 'AttentionController@add');
    Route::post('status', 'AttentionController@status');
});

//首页
Route::prefix('home')->group(function () {
    Route::get('hot', 'HomeController@hot');
    Route::get('recent', 'HomeController@recent');
    Route::get('info', 'HomeController@info');
});

//用户
Route::prefix('user')->group(function () {
    Route::get('info', 'UserController@info');
    Route::get('signIn', 'UserController@signIn');
    Route::post('edit', 'UserController@edit');
});
