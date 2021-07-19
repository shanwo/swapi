<?php
/**
 * User: Powerless
 * Date: 21/06/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\AreaModel;
use App\Models\UserModel;
use App\Models\UserThirdModel;

class UserService extends Service
{
    protected $areaModel;
    protected $userModel;
    protected $userThirdModel;

    public function __construct()
    {
        parent::__construct();
        $this->areaModel = new AreaModel();
        $this->userModel = new UserModel();
        $this->userThirdModel = new UserThirdModel();
    }

    public function info($id)
    {
        $info = $this->userModel->whereKey($id)->first();
        $info['contact'] = json_decode($info['contact'], true);
        $info['third'] = $this->userThirdModel->select('id', 'logins_number', 'source', 'last_login_ip', 'created_at')->where(['user_id' => $id])->get()->toArray();
        unset($info['api_token']);
        return $info;
    }

    public function edit($request, $id)
    {
        $aid = $request->input('area_id');
        $area = $this->areaModel->whereKey($aid)->first();
        $contact = $request->input('contact');
        $update = [
            'nickname'    => $request->input('nickname'),
            'avatar'      => $request->input('avatar'),
            'area_id'     => $aid,
            'merger_name' => $area['merger_name'],
            'contact'     => json_encode($contact, JSON_UNESCAPED_UNICODE),
        ];
        $sex = $request->input('sex', '');
        if (is_numeric($sex)) {
            $update['sex'] = $sex;
        }
        $birthday = $request->input('birthday', '');
        if ($birthday) {
            $update['birthday'] = $birthday;
        }
        return $this->userModel->whereKey($id)->update($update);
    }
}
