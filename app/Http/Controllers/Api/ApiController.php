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

    /**
     * 功能：对字符串进行加密处理
     * @param string $str
     * 需要加密的内容.
     * @param string $key
     * 加密字符串
     * @return string
     * 加密后的内容
     */
    public function passportEncrypt($str, $key)
    {
        srand((double)microtime() * 1000000);
        $encrypt_key = md5(rand(0, 32000));
        $ctr = 0;
        $tmp = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $encrypt_key[$ctr] . ($str[$i] ^ $encrypt_key[$ctr++]);
        }
        return base64_encode($this->passportKey($tmp, $key));
    }

    /**
     * 功能：对字符串进行解密处理
     * @param string $str
     * 需要解密的密文.
     * @param string $key
     * 加密字符串
     * @return string
     * 解出的内容
     */
    public function passportDecrypt($str, $key)
    {
        $str = $this->passportKey(base64_decode($str), $key);
        $tmp = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $md5 = $str[$i];
            $tmp .= $str[++$i] ^ $md5;
        }
        return $tmp;
    }

    /**
     *辅助函数
     */
    private function passportKey($str, $encrypt_key)
    {
        $encrypt_key = md5($encrypt_key);
        $ctr = 0;
        $tmp = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $str[$i] ^ $encrypt_key[$ctr++];
        }
        return $tmp;
    }
}
