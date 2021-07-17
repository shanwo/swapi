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

    public function facebook()
    {

    }

    public function github()
    {

    }

    public function google()
    {

    }

    public function linkedin()
    {

    }

    public function weibo()
    {

    }

    public function qq()
    {

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
        $watermark = null;
        $state = $this->request->input('state', 0);
        if ($state) {
            $watermark['ip'] = $this->request->ip();
            $watermark['time'] = time();
            $watermark['uid'] = $this->service->redis::get('login:wechat:' . $state);
            $this->service->redis::del('login:wechat:' . $state);
        } else {
            $this->msg = '绑定成功';
        }
        $user = $this->driver($this->request, 'wechat')->stateless()->user();
        $data = $this->callbackService->login($watermark, $user);
        return $this->returnApi($data);
    }

    public function douban()
    {

    }

    public function wework()
    {

    }

    public function outlook()
    {

    }

    public function douyin()
    {

    }

    public function taobao()
    {

    }

    public function feishu()
    {

    }

    public function pay()
    {

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
}
