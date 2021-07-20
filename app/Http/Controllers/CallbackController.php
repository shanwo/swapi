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

    private function driver($request, $name)
    {
        $config = $this->service->configInfo($name);
        $socialite = new SocialiteManager([
            $name => [
                'client_id'     => $config['key'],
                'client_secret' => $config['secret'],
                'redirect'      => $config['callback'],
            ],
        ], $request);
        return $socialite->driver($name);
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
        $this->code = 400;
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function douyin()
    {
        $this->code = 400;
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function facebook()
    {
        $this->code = 400;
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function feishu()
    {
        $this->code = 400;
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function github()
    {
        $this->code = 400;
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function google()
    {
        $this->code = 400;
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function linkedin()
    {
        $this->code = 400;
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function outlook()
    {
        $this->code = 400;
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function qq()
    {
        $this->code = 400;
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function taobao()
    {
        $this->code = 400;
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    /**
     * 微信登录
     {
        "data": {
            "id": "ontMB57lnxMnQG9e2TEnT4AssdiY",
            "name": "神经蛙",
            "nickname": "神经蛙",
            "avatar": "https://thirdwx.qlogo.cn/mmopen/vi_32/Q3auHgzwzM5QQAfBvhib5F4yiaNgeWz1PWOvOsNqkMA4ic2PsIV7k6UFrglT57rUcHm57ekI7XUT4C8XaezGaPefQ/132",
            "email": null,
            "original": {
                "openid": "ontMB57lnxMnQG9e2TEnT4AssdiY",
                "nickname": "神经蛙",
                "sex": 0,
                "language": "",
                "city": "",
                "province": "",
                "country": "",
                "headimgurl": "https://thirdwx.qlogo.cn/mmopen/vi_32/Q3auHgzwzM5QQAfBvhib5F4yiaNgeWz1PWOvOsNqkMA4ic2PsIV7k6UFrglT57rUcHm57ekI7XUT4C8XaezGaPefQ/132",
                "privilege": [],
                "unionid": "oR6W450ByXNPA5Tu96jlle6yEV3I"
            },
            "token": "47_gcQLnmoH9wwTBUDiIA0RwxxvJlTPBsf1pYI06ZgZ12RFuB9URWwS7e_1kHvtCiqDGIT-u88qd3XhIfl5ZK1j1DXLOS664xq65Pd6OtTynKw",
            "access_token": "47_gcQLnmoH9wwTBUDiIA0RwxxvJlTPBsf1pYI06ZgZ12RFuB9URWwS7e_1kHvtCiqDGIT-u88qd3XhIfl5ZK1j1DXLOS664xq65Pd6OtTynKw",
            "refresh_token": "47_KqXsRxX68KnPwQJTH6BryjiOiLQ5stilsLCXBe5PFGqfHdHCjHizbJQSTDNfkREF7UsAHqz6nCmYsZ9o9oKvjYNYsLDamNbyBO_s8k-8Fq0",
            "provider": "WeChat"
        },
        "code": 100,
        "msg": null,
        "uuid": "1ebe53e3-2d01-6c84-83b2-0242cb007103"
    }
     *
    //type:facebook,github,google,linkedin,weibo,qq,wechat,douban,wework,outlook,douyin,taobao,feishu
    //secret:用户ID转为16进制后进行MD5加密，使用用户ID为key，加密结果为value转为json后使用type进行字符串加密
     */
    public function wechat()
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
        $type = 'wechat';
        $state = $this->request->input('state', 0);
        $user = $this->driver($this->request, $type)->stateless()->user();
        $data = $this->callbackService->login($this->getUser($state,$type), $user);
        $data['token'] = $this->callbackService->token($data['uid'], $type);
        return $this->returnApi($data);
    }
    public function weibo()
    {
        $this->code = 400;
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }
    public function wework()
    {
        $this->code = 400;
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
