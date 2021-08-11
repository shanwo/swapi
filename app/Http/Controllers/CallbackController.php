<?php
/**
 * User: Powerless
 * Date: 21/07/15
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\UserService;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    protected $userService;
    protected $uid = 0;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userService = new UserService();

    }

    private function getUser($state, $name)
    {
        if ($state) {
            $this->uid = $this->service->redis::GET('signIn:' . $name . ':' . $state);
            $this->service->redis::DEL('signIn:' . $name . ':' . $state);
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

    public function official()
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
        $this->userService->configName = 'official';
        $this->getUser($state, $this->userService->configName);
        $user = $this->driver($this->request, $this->userService->configName)->stateless()->user();
        $user['ip'] = $this->request->ip();
        $data = $this->userService->wechat($this->uid, $user);
        if (empty($data['msg'])) {
            $this->service->redis::setex('token:' . $data['api_token'], 60 * 60 * 24 * 180, $data['id']);
//            $arr = [$data['id'] => md5(dechex($data['id']))];
//            $this->service->redis::setex('token:' . $data['api_token'], 60 * 60 * 24 * 180, json_encode($arr));
//            $this->service->redis::setex('user:' . $data['id'], 60 * 60 * 24 * 180, json_encode($data, JSON_UNESCAPED_UNICODE));
            $this->code = 200;
        } else {
            $this->msg = $data['msg'];
        }
        return $this->returnApi($data);
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
        $this->userService->configName = 'wechat';
        $this->getUser($state, $this->userService->configName);
        $user = $this->driver($this->request, $this->userService->configName)->stateless()->user();
        $user['ip'] = $this->request->ip();
        $data = $this->userService->wechat($this->uid, $user);
        if (empty($data['msg'])) {
            $this->service->redis::setex('token:' . $data['api_token'], 60 * 60 * 24 * 180, $data['id']);
//            $arr = [$data['id'] => md5(dechex($data['id']))];
//            $this->service->redis::setex('token:' . $data['api_token'], 60 * 60 * 24 * 180, json_encode($arr));
//            $this->service->redis::setex('user:' . $data['id'], 60 * 60 * 24 * 180, json_encode($data, JSON_UNESCAPED_UNICODE));
            $this->code = 200;
        } else {
            $this->msg = $data['msg'];
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

    public function officialBind()
    {
        $this->validate([
            'echostr'   => 'required|alpha_num|between:16,32',
            'nonce'     => 'required|alpha_num|between:8,16',
            'signature' => 'required|alpha_num|between:32,64',
            'timestamp' => 'required|alpha_num|between:8,16',
        ], [
            'echostr'   => '随机字符串',
            'nonce'     => '随机数',
            'signature' => '加密签名',
            'timestamp' => '时间戳',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $echoStr = $this->request->input('echostr');
        $signature = $this->request->input('signature');
        $timestamp = $this->request->input('timestamp');
        $nonce = $this->request->input('nonce');
        $tmpArr = ['rRmxsWtzpNeePu85mDNGtHODlkSt32lD', $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        # 打印返回结果
        if ($tmpStr == $signature) {
            return response($echoStr);
        } else {
            return response();
        }
    }

    public function officialServer()
    {
        $config = config('wechat.official_account.default');
        $app = Factory::officialAccount($config);
        $app->server->push(function ($message) {
            return "欢迎关注 闪我！";
        });
        $response = $app->server->serve();
        return $response->send();
    }

    /**
     * 支付回调
     */
    public function payment()
    {
        $this->validate([
        ], [
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通';
        return $this->returnApi();
    }
}
