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

    public function wechat($uid, $data)
    {
        $configName = 'wechat';
        $config = $this->configInfo($configName);
        $where[] = ['source', '=', $configName];
        $where[] = ['openid', '=', $data['id']];
        $where[] = ['app_id', '=', $config['key']];
        $where[] = ['status', '=', 1];
        $userThird = $this->userThirdModel->where($where)->first(['id', 'user_id', 'logins_number']);
        if ($userThird) {
            if (!empty($uid) && $uid != $userThird['user_id']) {
                return ['msg' => '当前微信已绑定其他账号'];
            }
            $update = [
                'last_login_ip' => $data['ip'],
                'logins_number' => $userThird['logins_number'] + 1
            ];
            $tid = $userThird['id'];
            $this->userThirdModel->whereKey($tid)->update($update);
        } else {
            $original = $data['original'];
            if (!$uid) {
                $insertUser = [
                    'nickname'      => htmlspecialchars($original['nickname']),
                    'sex'           => $original['sex'],
                    'avatar'        => $original['avatar'] ?? 'https://cdn.shanwo.cc/img/avatar.jpg',
                    'last_login_ip' => $data['ip'],
                    'api_token'     => md5(uniqid()) . md5(uniqid())
                ];
                $uid = $this->userModel->insertGetId($insertUser);
            }
            $insertThird = [
                'user_id'       => $uid,
                'nickname'      => htmlspecialchars($original['nickname']),
                'union_id'      => $original['unionid'],
                'openid'        => $original['openid'],
                'source'        => $configName,
                'app_id'        => $config['key'],
                'last_login_ip' => $data['ip'],
                'original'      => json_encode($data['original'])
            ];
            $tid = $this->userThirdModel->insertGetId($insertThird);
        }
        $info = $this->userModel->whereKey($uid)->first();
        $info['contact'] = json_decode($info['contact'], true);
        $info['third'] = $this->userThirdModel->select('id', 'logins_number', 'source', 'last_login_ip', 'created_at')->where(['user_id' => $tid])->get()->toArray();
        return $info;
    }
}
