<?php

namespace App\Http\Controllers;

use App\Services\OfficialServe;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;

class OfficialController extends Controller
{
    protected $officialServe;
    protected $official;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->officialServe = new OfficialServe();
        $config = $this->service->configInfo('official');
        $this->official = new Application($config);
    }

    public function bind()
    {
        $this->validate([
            'echostr'   => 'required|alpha_num|between:16,32',
            'signature' => 'required|alpha_num|between:32,64',
            'timestamp' => 'required|integer|digits:10',
            'nonce'     => 'required|integer|digits_between:8,16',
        ], [
            'echostr'   => '随机字符串',
            'nonce'     => '随机数',
            'signature' => '加密签名',
            'timestamp' => '时间戳',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        return $this->official->getServer()->serve();
    }

    public function server()
    {
        $this->validate([
            'nonce'         => 'required|integer|digits_between:8,16',
            'signature'     => 'required|alpha_num|between:32,64',
            'timestamp'     => 'required|integer|digits:10',
            'openid'        => 'required|alpha_dash|between:16,32',
            'encrypt_type'  => 'nullable|string|between:2,4',
            'msg_signature' => 'nullable|string|between:32,64',
        ], [
            'nonce'         => '随机数',
            'signature'     => '加密签名',
            'timestamp'     => '时间戳',
            'openid'        => 'openid',
            'encrypt_type'  => '加密类型',
            'msg_signature' => '消息体签名',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->service->redis::SETEX('request_official:' . LARAVEL_START . ':xml', $this->service->oneDay / 2, file_get_contents("php://input"));
        $this->service->redis::SETEX('request_official:' . LARAVEL_START . ':param', $this->service->oneDay / 2, json_encode($this->request->all()));
        return $this->official->getServer()->with(function ($message) {
            $config = $this->official->getConfig();
            $config['name'] = 'official';
            switch ($message->MsgType) {
                case 'event':
                    $user['raw'] = $this->official->getClient()->get('cgi-bin/user/info', [
                        'query' => ['openid' => $this->request->input('openid')]
                    ])->toArray();
                    $user['ip'] = $this->request->ip();
                    $this->service->redis::SETEX('request_official:' . LARAVEL_START . ':user', $this->service->oneDay / 2, json_encode($user));
                    return $this->officialServe->eventType($message, $user, $config);
                case 'text':
                    return $this->officialServe->textType($message, $config);
                case 'voice':
                    return $this->officialServe->voiceType($message);
//                case 'location':
//                    return "此位置是：" . $message['Label'] . "\n经度:" . $message['Location_Y'] . "\n纬度:" . $message['Location_X'];
//                case 'image':
//                    return $message['PicUrl'];
//                case 'link':
//                    return $message['Title'];
                default:
                    return '';
            }
        })->serve();
    }
}