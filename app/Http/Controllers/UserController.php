<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 * Tips: 用户模块
 */

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\WechatService;
use Illuminate\Http\Request;
use Overtrue\Socialite\SocialiteManager;

class UserController extends Controller
{
    protected $pluginName = 'wechat';
    protected $userService;
    protected $wechatService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userService = new UserService();
        $this->wechatService = new WechatService();
    }

    public function signIn()
    {
        $this->validate([
            'type' => 'required|alpha_num|between:2,8',
        ], [
            'type' => '登录类型',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $type = $this->request->input('type');
        $config = $this->wechatService->configInfo($type);
        $socialite = new SocialiteManager([
            $type => [
                'client_id'     => $config['key'],
                'client_secret' => $config['secret'],
                'redirect'      => $config['callback'],
            ],
        ]);
        return $socialite->driver($type)->redirect();
    }

    public function login()
    {
        $this->validate([
            'code'  => 'filled|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $watermark = null;
        $state = $this->request->input('state', 0);
        if ($state) {
            $watermark['ip'] = $this->request->ip();
            $watermark['time'] = time();
            $watermark['uid'] = $this->redis::get('login:wechat:' . $state);
            $this->redis::del('login:wechat:' . $state);
        } else {
            $this->msg = '绑定成功';
        }
        $user = $this->wechatService->driver($this->request)->stateless()->user();
        $data = $this->wechatService->binding($watermark, $user);
        if (!empty($data['uid'])) {
            $token = $this->userService->token($data['uid'], 'web');
            return redirect('https://wzxaini9.cn/user/login/thirdPartyLogin?token=' . $token, 301);
        }
        return $data;
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_user,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = $this->request->input('id');
        $data = $this->userService->info($id);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
