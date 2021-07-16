<?php
/**
 * User: Powerless
 * Date: 21/06/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\UserModel;
use App\Models\UserThirdModel;

class UserService extends Service
{
    protected $userModel;
    protected $userThirdModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->userThirdModel = new UserThirdModel();
    }

    public function info($id)
    {
        return $this->userModel->whereKey($id)->first();
    }

    public function add()
    {

    }

    public function edit($request)
    {
        return $request;
    }
}
