<?php
/**
 * User: Powerless
 * Date: 21/07/15
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\OfficialServe;
use App\Services\OrderService;
use App\Services\UserService;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Overtrue\Socialite\AuthorizeFailedException;

class CallbackController extends Controller
{
    protected $uid = 0;
    protected $userService;
    protected $orderService;
    protected $officialServe;
    protected $payment;
    protected $factory;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userService = new UserService();
        $this->orderService = new OrderService();
        $this->officialServe = new OfficialServe();
        $this->factory = new Factory();
    }

    private function getUser($state, $name)
    {
        if ($state) {
            $this->uid = $this->service->redis::GET('signIn:' . $name . ':' . $state);
            $this->service->redis::DEL('signIn:' . $name . ':' . $state);
        }
    }

    /**
     * 第三方登录回调
     */
    public function official()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $state = $this->request->input('state', 0);
        $this->userService->configName = 'official';
        $this->getUser($state, $this->userService->configName);
        $data = [];
        try {
            $user = $this->driver($this->request, $this->userService->configName)->stateless()->user();
            $user['ip'] = $this->request->ip();
            $uid = $this->userService->wechat($this->uid, $user);
            $data = $this->userService->info($uid);
            if (empty($data['msg'])) {
                $this->service->redis::SETEX('user_tokens:' . $data['api_token'], $this->service->timeOut, $uid);
                $this->service->redis::SET('user_infos:' . $uid, json_encode($data, JSON_UNESCAPED_UNICODE));
//                $arr = [$data['id'] => md5(dechex($data['id']))];
//                $this->service->redis::setex('token:' . $data['api_token'], $this->service->timeOut, json_encode($arr));
//                $this->service->redis::setex('user:' . $data['id'], $this->service->timeOut, json_encode($data, JSON_UNESCAPED_UNICODE));
                $this->code = 200;
            } else {
                $this->msg = $data['msg'];
            }
        } catch (AuthorizeFailedException $e) {
            $this->msg = '(' . $e->body['errcode'] . '):' . $e->body['errmsg'];
        }
        return $this->returnApi($data);
    }

    public function wechat()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $state = $this->request->input('state', 0);
        $this->userService->configName = 'wechat';
        $this->getUser($state, $this->userService->configName);
        $data = [];
        try {
            $user = $this->driver($this->request, $this->userService->configName)->stateless()->user();
            $user['ip'] = $this->request->ip();
            $uid = $this->userService->wechat($this->uid, $user);
            $data = $this->userService->info($uid);
            if (empty($data['msg'])) {
                $this->service->redis::SETEX('user_tokens:' . $data['api_token'], $this->service->timeOut, $uid);
                $this->service->redis::SET('user_infos:' . $uid, json_encode($data, JSON_UNESCAPED_UNICODE));
//            $arr = [$data['id'] => md5(dechex($data['id']))];
//            $this->service->redis::setex('token:' . $data['api_token'], $this->service->timeOut, json_encode($arr));
//            $this->service->redis::setex('user:' . $data['id'], $this->service->timeOut, json_encode($data, JSON_UNESCAPED_UNICODE));
                $this->code = 200;
            } else {
                $this->msg = $data['msg'];
            }
        } catch (AuthorizeFailedException $e) {
            $this->msg = '(' . $e->body['errcode'] . '):' . $e->body['errmsg'];
        }
        return $this->returnApi($data);
    }

    public function officialBind()
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
        $config = $this->service->configInfo('official');
        return $this->factory::officialAccount($config)->server->serve();
    }

    public function officialServer()
    {
        $this->validate([
            'signature'     => 'required|alpha_num|between:32,64',
            'timestamp'     => 'required|integer|digits:10',
            'nonce'         => 'required|integer|digits_between:8,16',
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
        $config = $this->service->configInfo('official');
        $official = $this->factory::officialAccount($config);
        $official->server->push(function ($message) {
            $this->service->redis::SETEX('request_official_message:' . time(), 3600, json_encode($message));
            switch ($message['MsgType']) {
                case 'event':
                    return $this->officialServe->eventType($message);
                case 'text':
                    return $this->officialServe->textType($message);
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
        return $official->server->serve();
    }

    /**
     * 支付回调
     */
    public function payment()
    {
        $this->service->redis::SETEX('request_payment_log:' . time(), 3600, json_encode($this->request->getContent()));
        $config = $this->service->configInfo('payment');
        $config['cert_path'] = app_path('Exceptions' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'apiclient_cert.pem');
        $config['key_path'] = app_path('Exceptions' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'apiclient_key.pem');
        $this->payment = $this->factory::payment($config);
        $this->payment['request'] = $this->request;
        //支付结果通知
        $response = $this->payment->handlePaidNotify(function ($message, $fail) {
            $info = $this->service->redis::GET('pay_order:' . $message['out_trade_no']);
            $info = json_decode($info, true) ?? [];
            if (!$info) {
                $this->payment->refund->byTransactionId($message['transaction_id'], $message['out_trade_no'], $message['total_fee'], $message['cash_fee'], ['refund_desc' => '订单支付超时',]);
                return true;
            }
            $res = $this->orderService->info(['order_sn' => $message['out_trade_no']]);
            if ($res && $res['status'] == 2) {
                return true;
            }
            if ($message['return_code'] === 'SUCCESS') {
                if ($message['result_code'] === 'SUCCESS') {
                    $this->orderService->add($message, $info);
                }
            } else {
                return $fail('支付验证失败,请查看日志:' . time());
            }
            $this->service->redis::DEL('pay_order:' . $message['out_trade_no']);
            return true;
        });
        //退款结果通知
        $response = $this->payment->handleRefundedNotify(function ($message, $reqInfo, $fail) {
            // 其中 $message['req_info'] 获取到的是加密信息
            // $reqInfo 为 message['req_info'] 解密后的信息
            // 你的业务逻辑...
            return true; // 返回 true 告诉微信“我已处理完成”
            // 或返回错误原因 $fail('参数格式校验错误');
        });
        //扫码支付通知
        // 扫码支付通知接收第三个参数 `$alert`，如果触发该函数，会返回“业务错误”到微信服务器，触发 `$fail` 则返回“通信错误”
        $response = $this->payment->handleScannedNotify(function ($message, $fail, $alert) use ($payment) {
            // 如：$alert('商品已售空');
            // 如业务流程正常，则要调用“统一下单”接口，并返回 prepay_id 字符串，代码如下
            $result = $payment->order->unify([
                'trade_type' => 'NATIVE',
                'product_id' => $message['product_id'],
                // ...
            ]);
            return $result['prepay_id'];
        });
        return $response;
    }
}
