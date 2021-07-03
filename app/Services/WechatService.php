<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\UserModel;

class WechatService extends Service
{
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }
}
