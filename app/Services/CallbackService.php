<?php
/**
 * User: Powerless
 * Date: 21/07/16
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\UserModel;
use App\Models\UserThirdModel;

class CallbackService extends Service
{
    protected $userModel;
    protected $userThirdModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->userThirdModel = new UserThirdModel();
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
    public function wechat($auth, $user)
    {
        $config = $this->configInfo('wechat');
        $where[] = ['openid', '=', $user['id']];
        $where[] = ['app_id', '=', $config['key']];
        $where[] = ['third_party', '=', 'wechat'];
        $userThird = $this->userThirdModel->where($where)->first(['id', 'user_id']);
        if ($userThird) {//已注册用户登录。
//            if ($auth['uid'] && $auth['uid'] != $userThird['user_id']) {
//                return view('home.thirdParty', ['title' => '当前GitHub账号已绑定其他账号']);
//            }
//            $auth['uid'] = $userThird['user_id'];
//            $auth['tid'] = $userThird['id'];
//            $this->editUser($user, $auth);
        } else {//新增三方平台信息
            $third = [
                'user_id'       => $auth['uid'] ?? 0,
                'nickname'      => htmlspecialchars($user['nickname']),
                'union_id'      => $user['original']['unionid'],
                'openid'        => $user['original']['openid'],
                'source'        => 'wechat',
                'app_id'        => $config['key'],
                'last_login_ip' => $auth['ip'],//三方首次登录无数据、
                'more'          => json_encode($user['original'])
            ];
            $auth['tid'] = $this->userThirdModel->insertGetId($third);
            if (!$auth['uid']) {//新用户信息

                $user = [
                    'nickname'      => htmlspecialchars($user['nickname'] ?? ''),
                    'sex'           => $user['original']['sex'] ?? 0,
                    'birthday'      => $user['original']['birthday'] ?? '',
                    'avatar'        => $user['original']['avatar'] ?? 'https://cdn.shanwo.cc/img/avatar.jpg',
                    'last_login_ip' => $auth['ip'],
                    'api_token'     => md5(uniqid()) . md5(uniqid())
                ];
                $auth['uid'] = $this->userModel->insertGetId($user);
                $this->userThirdModel->whereKey($auth['tid'])->update(['user_id' => $auth['uid']]);
            }
        }
        $info = $this->userModel->whereKey($auth['uid'])->first();
        $info['contact'] = json_decode($info['contact'], true);
        $info['third'] = $this->userThirdModel->select('id', 'logins_number', 'source', 'last_login_ip', 'created_at')->where(['user_id' => $auth['tid']])->get()->toArray();
        return $info;
    }

    private function editUser($data, $auth)
    {
        $original = $data['original'];
        $info = $this->userModel->where(['user_login' => $original['login']])->first(['id', 'user_login']);
        if (empty($info) || ($info['id'] == $auth['uid'] && !$info['user_login'])) {
            $user['user_login'] = $original['login'];
        }
        $user['last_login_ip'] = $auth['ip'];
        $user['last_login_time'] = $auth['time'];
        $area['location'] = $original['location'];
        $user['more'] = json_encode($area, JSON_UNESCAPED_UNICODE);
        $this->userModel->whereKey($auth['uid'])->update($user);

        unset($data['original']);
        $user['more'] = json_encode($data);
        $this->thirdPartyUserModel->whereKey($auth['tid'])->update($user);
    }
}
