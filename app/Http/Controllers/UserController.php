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
            'type'   => 'required|alpha_num|in:official,wechat',
            'secret' => 'filled|string|between:64,256',
        ], [
            'type'   => '登录类型',
            'secret' => '密钥',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $type = $this->request->input('type');
        $data = $this->userService->driver($this->request, $type)->redirect();
        $url = $data->getTargetUrl();
        $secret = $this->request->input('secret', '');
        if ($secret) {
            $auth = $this->passportDecrypt($secret, $type);
            $arr = json_decode($auth, true) ?? [];
            foreach ($arr as $k => $v) {
                if ($v == md5(dechex($k))) {
                    parse_str(parse_url($url, PHP_URL_QUERY), $query);
                    $this->service->redis::setex('signIn:' . $type . ':' . $query['state'], $this->service->oneDay / (24 * 6), $k);
                }
            }
        }
        $this->code = 200;
        return $this->returnApi(['redirect_url' => $url]);
    }

    public function info()
    {
        $this->validate([
            'id' => 'nullable|integer|gt:0|exists:sw_user,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = $this->request->input('id', $this->request->user()->id ?? 0);
        $data = $this->userService->info($id);
        unset($data['api_token']);
//        $data['binding'] = $this->bindSecret($id);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function edit()
    {
        $this->validate([
            'area_id'        => 'required|integer|gt:0|exists:cn_area,id',
            'avatar'         => 'required|url|max:128',
            'nickname'       => 'required|string|between:2,32',
            'sex'            => 'nullable|integer|between:0,2',
            'birthday'       => 'nullable|date_format:Y-m-d|after_or_equal:1950-01-01|before_or_equal:' . date('Y-m-01', strtotime('-1 month')),
            'contact'        => 'nullable|array',
            'contact.wechat' => 'nullable|alpha_dash|between:6,20',
            'contact.phone'  => 'nullable|integer|between:13000000000,19999999999',
            'contact.qq'     => 'nullable|integer|between:10000,9999999999',
            'contact.weibo'  => 'nullable|string|between:4,32',
            'contact.email'  => 'nullable|email|between:8,32',
        ], [
            'area_id'        => '省市区',
            'avatar'         => '头像地址',
            'nickname'       => '昵称',
            'sex'            => '性别',
            'birthday'       => '生日',
            'contact'        => '联系方式',
            'contact.wechat' => '微信号',
            'contact.phone'  => '手机号',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->userService->edit($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    private function bindSecret($id)
    {
        $secret = json_encode([$id => md5(dechex($id))]);
        return [
            'douban'   => $this->passportEncrypt($secret, 'douban'),
            'douyin'   => $this->passportEncrypt($secret, 'douyin'),
            'facebook' => $this->passportEncrypt($secret, 'facebook'),
            'feishu'   => $this->passportEncrypt($secret, 'feishu'),
            'github'   => $this->passportEncrypt($secret, 'github'),
            'google'   => $this->passportEncrypt($secret, 'google'),
            'linkedin' => $this->passportEncrypt($secret, 'linkedin'),
            'outlook'  => $this->passportEncrypt($secret, 'outlook'),
            'qq'       => $this->passportEncrypt($secret, 'qq'),
            'taobao'   => $this->passportEncrypt($secret, 'taobao'),
            'wechat'   => $this->passportEncrypt($secret, 'wechat'),
            'weibo'    => $this->passportEncrypt($secret, 'weibo'),
            'wework'   => $this->passportEncrypt($secret, 'wework'),
        ];
    }
}
