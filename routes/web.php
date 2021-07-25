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

Route::prefix('public')->group(function () {
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
    Route::get('signIn', 'UserController@signIn');
    Route::get('info', 'UserController@info');
    Route::post('edit', 'UserController@edit');
});
//回调
Route::prefix('callback')->group(function () {
    //登录回调
    Route::get('douban', 'CallbackController@douban');
    Route::get('douyin', 'CallbackController@douyin');
    Route::get('facebook', 'CallbackController@facebook');
    Route::get('feishu', 'CallbackController@feishu');
    Route::get('github', 'CallbackController@github');
    Route::get('google', 'CallbackController@google');
    Route::get('linkedin', 'CallbackController@linkedin');
    Route::get('outlook', 'CallbackController@outlook');
    Route::get('qq', 'CallbackController@qq');
    Route::get('taobao', 'CallbackController@taobao');
    Route::get('wechat', 'CallbackController@wechat');
    Route::get('weibo', 'CallbackController@weibo');
    Route::get('wework', 'CallbackController@wework');
    //公众号
    Route::get('official', 'CallbackController@officialBind');
    Route::post('official', 'CallbackController@official');
    //支付
    Route::get('payment', 'CallbackController@payment');
});
//测试接口
Route::prefix('test')->group(function () {
    Route::get('initial', 'TestController@initial');
    Route::get('check', 'TestController@check');
    Route::get('info', 'TestController@info');
    Route::get('all', 'TestController@all');
    Route::get('image', 'TestController@image');
});

