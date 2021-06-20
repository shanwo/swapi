<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    protected $user_id = 0;
    protected $appids = ['wx508dad4113cb2e4a'=>'35e78c598d44147fa46cc363c228da3e'];
    protected $wxapi = "https://api.weixin.qq.com/";

    public function __construct($request)
    {
        $this->request = $request;
        parent::__construct();
        $this->setToken();
    }

    private function setToken()
    {
        $this->code = 400;
//        $token = $this->request->header();
//        if ($token) {
//            $this->getUserId($token);
//        }
    }

    protected function getUserId($token)
    {
        $auth = $this->redis::get('user:' . $token);
        if (!$auth) {
            $type = $this->request->header('xcx-device-type', $this->request->server->get('xcx-device-type'));
            $where[] = ['device_type', '=', $type];
            $where[] = ['token', '=', $token];
            $userTokenModel = new UserTokenModel();
            $userToke = $userTokenModel->where($where)->first();
            if ($userToke) {
                $this->user_id = $userToke['user_id'];
                $arr = [$this->user_id => md5(dechex($this->user_id))];
                $this->redis::setex('user:' . $token, 60 * 60 * 24 * 180, json_encode($arr));
            }else{
                $this->code = 300;
                $this->msg = '当前账户异常，请重新登录';
                return $this->returnApi();
            }
        } else {
            $arr = json_decode($auth, true) ?? [];
            foreach ($arr as $k => $v) {
                if ($v == md5(dechex($k))) {
                    $this->user_id = $k;
                }
            }
        }
    }

    public function getSession($appId, $secret, $jsCode)
    {
        $array = [
            'appid'      => $appId,
            'secret'     => $secret,
            'js_code'    => $jsCode,
            'grant_type' => 'authorization_code'
        ];
        $param = http_build_query($array);
        $url = $this->wxapi . 'sns/jscode2session?' . $param;
        $form['timeout'] = 3;
        $body = null;
        $api = $this->client->get($url, $form);
        if ($api->getStatusCode() == 200) {
            $body = $api->getBody()->getContents();
            $body = json_decode($body, true);
        }
        return $body;
    }
}
