<?php
/**
 * User: Powerless
 * Date: 2021/6/3
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Controller extends BaseController
{
    use ValidatesRequests;

    protected $redis;
    protected $client;
    protected $request;
    protected $code = 100;
    protected $msg = null;
    protected $levels = [
        100 => 'debug',
        200 => 'info',
        300 => 'notice',
        400 => 'notice',
    ];

    public function __construct()
    {
        $this->redis = new Redis();
        $this->client = new Client();
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
            'data'    => $data,
            'code'    => $this->code,
            'msg'     => $this->msg,
        ];
        Log::log($this->levels[$this->code], 'Return Api', $result);
        return response()->json($result);
    }
}
