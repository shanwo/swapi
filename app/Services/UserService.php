<?php
/**
 * User: Powerless
 * Date: 21/06/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\AreaModel;
use App\Models\UserModel;

class UserService extends Service
{
    protected $areaModel;
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->areaModel = new AreaModel();
        $this->userModel = new UserModel();
    }

    public function info($id)
    {
        return $this->userModel->whereKey($id)->first();
    }

    public function edit($request, $id)
    {
        $aid = $request->input('area_id');
        $area = $this->areaModel->whereKey($aid)->first();
        $update = [
            'nickname'    => $request->input('nickname'),
            'sex'         => $request->input('nickname'),
            'avatar'      => $request->input('avatar'),
            'area_id'     => $aid,
            'merger_name' => $area['merger_name'],
            'contact'     => $request->input('nickname'),
        ];
        return $this->userModel->whereKey($id)->update($update);
    }
}
