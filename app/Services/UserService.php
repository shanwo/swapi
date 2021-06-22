<?php
/**
 * User: Powerless
 * Date: 21/06/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\UserModel;

class UserService extends Service
{
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function info($id)
    {
        $data = $this->userModel->whereKey($id)->first();
//        $data['email'] = $data['user_email'];
//        $data['avatar'] = $this->cmfGetImageUrl($data['avatar']);
//        $data['birthday'] = date('Y-m-d', $data['birthday']);
//        $data['last_login_time'] = date('Y-m-d H:i:s', $data['last_login_time']);
//        $data['user_nickname'] = htmlspecialchars_decode($data['user_nickname']);
//        $data['signature'] = htmlspecialchars_decode($data['signature']);
//        $data['more'] = json_decode($data['more']);
//        $third = $this->lists($data['id']);
//        $data['third'] = array_column($third, 'id', 'third_party');
        return $data;
    }
}
