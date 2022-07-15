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
//微信支付安全医生验证
Route::get('verify_90bdbda5dbef9a37279156dd3ece6936.html', function () {
    return '<!DOCTYPE html><html><head><meta id="wxpaycode" content="2ec9fbfa0f5606e47e6095d122a50048"></head><body></body></html>';
});

Route::prefix('base')->group(function () {
//文件上传
    Route::post('upload', 'UploadController@file');
//地区信息
    Route::get('area', 'AreaController@lists');
//用户反馈
    Route::post('feedback', 'FeedbackController@add');
//浏览记录
    Route::get('browse', 'BrowseController@lists');
//装饰
    Route::get('decoration', 'DecorationController@lists');
//教程
    Route::get('tutorials', 'TutorialsController@lists');
//链接
    Route::get('link', 'LinkController@lists');
});
//模板
Route::prefix('template')->group(function () {
    Route::get('lists', 'TemplateController@lists');
    Route::get('info', 'TemplateController@info');
});
//关注
Route::prefix('attention')->group(function () {
    Route::get('lists', 'AttentionController@lists');
    Route::post('status', 'AttentionController@status');
});
//页面
Route::prefix('page')->group(function () {
    Route::get('lists', 'PageController@lists');
    Route::get('info', 'PageController@info');
    Route::post('add', 'PageController@add');
    Route::post('edit', 'PageController@edit');
    Route::post('status', 'PageController@status');
});
//首页
Route::prefix('home')->group(function () {
    Route::get('hot', 'HomeController@hot');
    Route::get('recent', 'HomeController@recent');
    Route::get('info', 'HomeController@info');
});
//用户
Route::prefix('user')->group(function () {
    Route::get('signIn', 'UserController@signIn');
    Route::get('signOut', 'UserController@signOut');
    Route::get('info', 'UserController@info');
    Route::post('edit', 'UserController@edit');
});
//支付
Route::prefix('pay')->group(function () {
    Route::get('lists', 'PayController@lists');
    Route::post('send', 'PayController@send');
    Route::post('result', 'PayController@result');
});
//回调
Route::prefix('callback')->group(function () {
    //登录
    Route::get('official', 'CallbackController@official');
    Route::get('wechat', 'CallbackController@wechat');
    //支付
    Route::post('payment', 'CallbackController@payment');
    Route::post('refund', 'CallbackController@refund');
});
//公众号
Route::prefix('official')->group(function () {
    Route::get('server', 'OfficialController@bind');
    Route::post('server', 'OfficialController@server');
});
//测试接口
Route::prefix('test')->group(function () {
    Route::get('initial', 'TestController@initial');
    Route::get('check', 'TestController@check');
    Route::get('info', 'TestController@info');
    Route::get('all', 'TestController@all');
    Route::get('image', 'TestController@image');
});

