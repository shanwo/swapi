<?php

namespace App\Services;

use App\Models\UserAttributeModel;

class UserAttributeService extends Service
{
    protected $userAttributeModel;

    public function __construct()
    {
        parent::__construct();
        $this->userAttributeModel = new UserAttributeModel();
    }
    public function info($id)
    {
        $info = $this->redis::GET('user_infos:' . $id);
        $info = json_decode($info, true) ?? [];
        if (!$info) {
            $info = $this->userAttributeModel->whereKey($id)->first();
            if ($info['type'] == Service::USER_DISABLE) return ['msg' => '当前账户已被禁用，不可登录'];
            $info['contact'] = json_decode($info['contact'], true);
            $this->redis::SET('user_infos:' . $id, json_encode($info, JSON_UNESCAPED_UNICODE));
        }
        return $info;
    }
}
