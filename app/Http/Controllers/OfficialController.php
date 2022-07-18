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
        $user['original'] = $this->official->user->get($this->request->input('openid', ''));
        $user['ip'] = $this->request->ip();
        $this->service->redis::SETEX('request_official:xml:' . time(), $this->service->oneDay / 2, file_get_contents("php://input"));
        $this->service->redis::SETEX('request_official:param:' . time(), $this->service->oneDay / 2, json_encode($this->request->all()));
        $this->official->server->push(function ($message) use ($user) {
//            $this->service->redis::SETEX('request_official:message:' . time(), $this->service->oneDay / 2, json_encode($message));
            $config = $this->official->getConfig();
            $config['name'] = 'official';
            switch ($message['MsgType']) {
                case 'event':
                    return $this->officialServe->eventType($message, $user, $config);
                case 'text':
                    return $this->officialServe->textType($message,$config);
                case 'image':
                    return $this->officialServe->imageType($message);
                case 'voice':
                    return $this->officialServe->voiceType($message);
                case 'video':
                    return $this->officialServe->videoType($message);
                case 'location':
                    return $this->officialServe->locationType($message);
                case 'link':
                    return $this->officialServe->linkType($message);
                case 'file':
                    return $this->officialServe->fileType($message);
                default:
                    return '未识别的消息类型';
            }
        });
        return $this->official->server->serve();
    }
}