<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\WechatService;
use Illuminate\Http\Request;

class UserController extends Controller
{
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
            $auth = $this->passportDecrypt($secret, 'wechat');
            $arr = json_decode($auth, true) ?? [];
            foreach ($arr as $k => $v) {
                if ($v == md5(dechex($k))) {
                    $url = $data->getTargetUrl();
                    parse_str(parse_url($url, PHP_URL_QUERY), $query);
                    $this->redis::SETEX('login:wechat:' . $query['state'], 60 * 30, $k);
                }
            }
        }
        return $data;
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:user,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = $this->request->input('id');
        $data = $this->userService->info($id);
        return $this->returnApi($data);
    }
}
