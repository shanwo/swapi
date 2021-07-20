<?php
/**
 * User: Powerless
 * Date: 21/07/15
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\CallbackService;
use Illuminate\Http\Request;
use Overtrue\Socialite\SocialiteManager;

class CallbackController extends Controller
{
    protected $callbackService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->callbackService = new CallbackService();

    }

    private function getUser($state, $name)
    {
        $info = [];
        if ($state) {
            $info['ip'] = $this->request->ip();
            $info['time'] = time();
            $info['uid'] = $this->service->redis::GET('login:' . $name . ':' . $state);
            $this->service->redis::DEL('login:' . $name . ':' . $state);
        }
        return $info;
    }

    /**
     * 第三方登录回调
     */
    public function douban()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function douyin()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function facebook()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function feishu()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function github()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function google()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function linkedin()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function outlook()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function qq()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function taobao()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function wechat()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $type = 'wechat';
        $state = $this->request->input('state', 0);
        $auth = $this->getUser($state,$type);
        $user = $this->driver($this->request, $type)->stateless()->user();
        $data = $this->callbackService->wechat($auth, $user);
        $this->code = 200;
        return $this->returnApi($data);
    }
    public function weibo()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function wework()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    /**
     * 支付回调
     */
    public function pay()
    {

    }
}
