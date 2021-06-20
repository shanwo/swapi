<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers\Callback;

use App\Services\UserService;
use Illuminate\Http\Request;

class WechatController extends Callback
{
    protected $wechatService;
    protected $userService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->wechatService = new WechatService();
        $this->userService = new UserService();
    }

    public function login()
    {
        $this->validate([
            'secret' => 'filled|string|between:64,256',
        ], [
            'secret' => '密钥',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->wechatService->driver($this->request)->redirect();
        $secret = $this->request->input('secret', '');
        if ($secret) {
            $auth = $this->passportDecrypt($secret, 'weibo');
            $arr = json_decode($auth, true) ?? [];
            foreach ($arr as $k => $v) {
                if ($v == md5(dechex($k))) {
                    $url = $data->getTargetUrl();
                    parse_str(parse_url($url, PHP_URL_QUERY), $query);
                    $this->redis::setex('login:wechat:' . $query['state'], 60 * 30, $k);
                }
            }
        }
        return $data;
    }

    public function binding()
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
}
