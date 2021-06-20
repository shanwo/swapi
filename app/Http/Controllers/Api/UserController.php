<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers\Api;

use App\External\Weixin\WXBizDataCrypt;
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

    public function signIn()
    {
        $this->validate([
            'code'           => 'required|string|max:64',
            'encrypted_data' => 'required|string|max:1024',
            'iv'             => 'required|string|max:32',
            'raw_data'       => 'required|json',
            'signature'      => 'required|alpha_num|max:64',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $appId = $this->request->header('app-appid', $this->request->server->get('app-appid'));
        if (!array_key_exists($appId,$this->appids)) {
            $this->msg = 'APPID不存在';
            return $this->returnApi();
        }
        $jsCode = $this->request->input('code');
        $body = $this->getSession($appId, $this->appids[$appId], $jsCode);
        if (!$body) {
            $this->msg = '授权操作失败';
            return $this->returnApi();
        }
        $encrypted_data = $this->request->input('encrypted_data');
        $iv = $this->request->input('iv');
        $pc = new WXBizDataCrypt($appId, $body['session_key']);
        $res = $pc->decryptData($encrypted_data, $iv, $appData);
        if ($res) {
            $this->msg = '授权验证失败';
            return $this->returnApi();
        }
        $rawJson = $this->request->input('raw_data', '');
        if ($rawJson) {
            $rawArr = json_decode($rawJson);
            $appData = (object)array_merge((array)$appData, (array)$rawArr);
        }
        $appData->type = 'wx';
        $appData->ip = $this->request->ip();
        $appData = json_decode(json_encode($appData), true);
        $third = $this->userService->thirdInfo($appData);
        $uid = $this->request->input('uid', 0);
        if ($third) {
            if ($uid) {
                $this->msg = '当前小程序账号已与其他账号绑定';
                return $this->returnApi();
            }
            $this->userService->thirdEdit($appData, $third);
        } else {
            $third['user_id'] = $uid;
            if (!$uid) {
                $third['user_id'] = $this->userService->add($appData);
            }
            $third['id'] = $this->userService->thirdAdd($appData, $third['user_id']);
        }
        $data = $this->userService->info(['id' => $third['user_id']]);
        if ($data['user_nickname'] == '微信用户') {
            $this->userService->wxuser($appData, $third['user_id']);
            $data = $this->userService->info(['id' => $third['user_id']]);
        }
        $token = $this->userService->token($third['user_id']);
        $data['token'] = $token;
        $arr = [$third['user_id'] => md5(dechex($third['user_id']))];
        $this->redis::setex('user:' . $token, 60 * 60 * 24 * 180, json_encode($arr));
        $this->code = 200;
        unset($data['user_pass']);
        return $this->returnApi($data);
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
        return $this->returnApi($data);
    }
}
