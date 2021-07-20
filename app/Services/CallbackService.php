<?php
/**
 * User: Powerless
 * Date: 21/07/16
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\UserModel;
use App\Models\UserThirdModel;
use App\Models\UserTokenModel;

class CallbackService extends Service
{
    protected $userModel;
    protected $userThirdModel;
    protected $userTokenModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->userThirdModel = new UserThirdModel();
        $this->userTokenModel = new UserTokenModel();
    }


    public function login($auth, $user)
    {
        $thirdParty = $this->userThirdModel->where(['openid' => $user['id'], 'app_id' => $this->config['appid'], 'third_party' => $this->pluginName])->first(['id', 'user_id']);
        if ($thirdParty) {
            if ($auth['uid'] && $auth['uid'] != $thirdParty['user_id']) {
                return view('home.thirdParty', ['title' => '当前GitHub账号已绑定其他账号']);
            }
            $auth['uid'] = $thirdParty['user_id'];
            $auth['tid'] = $thirdParty['id'];
            $this->editUser($user, $auth);
        } else {
            $auth['tid'] = $this->addThirdParty($auth, $user);
            if ($auth['uid']) {
                return view('home.thirdParty', ['title' => '绑定成功']);
            } else {
                $auth['uid'] = $this->addUser($auth, $user);
            }
        }
        return $auth;
    }

    public function token($uid,$type)
    {
        $where[] = ['user_id', '=', $uid];
        $where[] = ['device_type', '=', $type];
        $token = $this->userTokenModel->where($where)->first(['id', 'expire_time', 'token']);
        $time = time();
        $data['expire_time'] = $time + (60 * 60 * 24 * 180);
        $data['token'] = md5(uniqid()) . md5(uniqid());
        if ($token) {
            if ($token['expire_time'] > $time) {
                $data['token'] = $token['token'];
            }
            $this->userTokenModel->whereKey($token['id'])->update($data);
        } else {
            $data['user_id'] = $uid;
            $data['device_type'] = $type;
            $data['create_time'] = $time;
            $this->userTokenModel->insertGetId($data);
        }
        return $data['token'];
    }
}
