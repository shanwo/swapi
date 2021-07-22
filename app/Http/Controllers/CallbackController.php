<?php
/**
 * User: Powerless
 * Date: 21/07/15
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\CallbackService;
use Illuminate\Http\Request;

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
        if ($state) {
            $this->uid = $this->service->redis::GET('login:' . $name . ':' . $state);
            $this->service->redis::DEL('login:' . $name . ':' . $state);
        }
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
        $state = $this->request->input('state', 0);
        $this->getUser($state, 'wechat');
        $user = $this->driver($this->request, 'wechat')->stateless()->user();
        $user['ip'] = $this->request->ip();
        $user['time'] = time();
        $data = $this->callbackService->wechat($this->uid, $user);
        if ($data['msg']) {
            $this->msg = $data['msg'];
        } else {
            $arr = [$data['id'] => md5(dechex($data['id']))];
            $this->service->redis::setex('token:' . $data['api_token'], 60 * 60 * 24 * 180, json_encode($arr));
            $this->service->redis::setex('user:' . $data['id'], 60 * 60 * 24 * 180, json_encode($data, JSON_UNESCAPED_UNICODE));
            $this->code = 200;
        }
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
