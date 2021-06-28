<?php
/**
 * User: Powerless
 * Date: 2021/6/3
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\Service;
use GuzzleHttp\Client;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class Controller extends BaseController
{
    use ValidatesRequests;

    protected $client;
    protected $request;
    protected $service;
    protected $code = 100;
    protected $msg = null;
    protected $levels = [
        100 => 'debug',
        200 => 'info',
        300 => 'notice',
        400 => 'notice',
    ];
    protected $uid = 1;
    protected $wxapi = "https://api.weixin.qq.com/";
    private $writeUrl = [
        'user/edit',
        'template/lists',
        'template/info',
        'page/lists',
        'page/info',
        'page/add',
    ];

    public function __construct($request)
    {
        $this->request = $request;
        $this->client = new Client();
        $this->service = new Service();
//        if (in_array($this->request->path(), $this->writeUrl)) {
//            $this->validateToken();
//        }
    }

    /**
     * 验证器
     * @param array $rules
     * @param array $customAttributes
     * @param array $messages
     */
    public function validate(array $rules, array $customAttributes = [], array $messages = [])
    {
        $validator = $this->getValidationFactory()->make($this->request->all(), $rules, $messages, $customAttributes);
        if ($validator->fails()) {
            $this->code = 400;
            $errors = $validator->errors()->all();
            $this->msg = current($errors);
        }
    }

    /**
     * 需要返回的数据
     * @param object $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function returnApi($data = [])
    {
        $result = [
            'request' => $this->request->all(),
            'data'    => (object)$data,
            'code'    => (int)$this->code,
            'msg'     => $this->msg,
            'uuid'    => Uuid::uuid6(),
        ];
        Log::log($this->levels[$this->code], 'Return Api', $result);
        \Illuminate\Support\Facades\DB::disconnect('mysql');
        unset($result['request']);
        return response()->json($result);
    }


    private function validateToken()
    {
        $this->code = 400;
        $token = $this->request->header();
        if ($token) {
            $this->getUserId($token);
        }
    }

    protected function getUserId($token)
    {
        $auth = $this->service->redis::GET('user:' . $token);
        if ($auth) {
            $arr = json_decode($auth, true) ?? [];
            foreach ($arr as $k => $v) {
                if ($v == md5(dechex($k))) {
                    $this->uid = $k;
                }
            }
        }
        if (!$this->uid) {
            $this->code = 300;
            $this->msg = '当前账户异常，请重新登录';
            return $this->returnApi();
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
