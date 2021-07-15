<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 * Tips: 用户模块
 */

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Overtrue\Socialite\SocialiteManager;

class UserController extends Controller
{
    protected $userService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userService = new UserService();
    }

    public function signIn()
    {
        $this->validate([
            'type' => 'required|alpha_num|in:wechat',
            'secret' => 'filled|string|between:64,256',
        ], [
            'type' => '登录类型',
            'secret' => '密钥',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $type = $this->request->input('type');
        $config = $this->service->configInfo($type);
        $socialite = new SocialiteManager([
            $type => [
                'client_id'     => $config['key'],
                'client_secret' => $config['secret'],
                'redirect'      => $config['callback'],
            ],
        ],$this->request);
        $data = $socialite->driver($type)->redirect();
        $secret = $this->request->input('secret', '');
        if ($secret) {
            $auth = $this->passportDecrypt($secret, $type);
            $arr = json_decode($auth, true) ?? [];
            foreach ($arr as $k => $v) {
                if ($v == md5(dechex($k))) {
                    $url = $data->getTargetUrl();
                    parse_str(parse_url($url, PHP_URL_QUERY), $query);
                    $this->service->redis::setex('login:'.$type.':' . $query['state'], 60 * 30, $k);
                }
            }
        }
        return $data;
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
