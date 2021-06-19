<?php
/**
 * User: Powerless
 * Date: 21/06/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers\Api;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    protected $userService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userService = new UserService();
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:user,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = $this->request->input('id');
        $data = $this->userService->info($id);
        unset($data['user_pass']);
        return $this->returnApi($data);
    }
}
