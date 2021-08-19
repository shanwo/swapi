<?php
/**
 * User: Powerless
 * Date: 21/07/15
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\UserService;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    protected $uid = 0;
    protected $userService;
    protected $orderService;
    protected $payment;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userService = new UserService();
        $this->orderService = new OrderService();

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
    public function douban()
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
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function douyin()
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
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function facebook()
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
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function feishu()
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
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function github()
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
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function google()
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
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function linkedin()
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
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

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
        return $this->returnApi($data);
    }

    public function outlook()
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
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function qq()
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
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function taobao()
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
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
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
        return $this->returnApi($data);
    }

    public function weibo()
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
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function wework()
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
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function officialBind()
    {
        $this->service->redis::SETEX('request_official_log:' . time(), 3600, json_encode($this->request->getContent()));
        $this->validate([
            'echostr'   => 'required|alpha_num|between:16,32',
            'nonce'     => 'required|alpha_num|between:8,16',
            'signature' => 'required|alpha_num|between:32,64',
            'timestamp' => 'required|alpha_num|between:8,16',
        ], [
            'echostr'   => '随机字符串',
            'nonce'     => '随机数',
            'signature' => '加密签名',
            'timestamp' => '时间戳',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $echoStr = $this->request->input('echostr');
        $signature = $this->request->input('signature');
        $timestamp = $this->request->input('timestamp');
        $nonce = $this->request->input('nonce');
        $tmpArr = ['rRmxsWtzpNeePu85mDNGtHODlkSt32lD', $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        # 打印返回结果
        if ($tmpStr == $signature) {
            return response($echoStr);
        } else {
            return response();
        }
    }

    public function officialServer()
    {
        $this->service->redis::SETEX('request_official_log:' . time(), 3600, json_encode($this->request->getContent()));
        $config = $this->service->configInfo('official');
        $app = Factory::officialAccount($config);
        $app->server->push(function ($message) {
            switch ($message['MsgType']) {
                case 'event':
                    return '收到事件消息';
                    break;
                case 'text':
                    return '收到文字消息';
                    break;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                case 'file':
                    return '收到文件消息';
                default:
                    return '收到其它消息';
                    break;
            }
        });
        $response = $app->server->serve();
        return $response;
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
        $this->payment = Factory::Payment($config);
        $response = $this->payment->handlePaidNotify(function ($message, $fail) {
            $info = $this->service->redis::GET('pay_order:' . $message['out_trade_no']);
            $info = json_decode($info, true) ?? [];
            if (!$info) {
                $this->payment->refund->byTransactionId($message['transaction_id'], $message['out_trade_no'], $message['total_fee'], $message['cash_fee'], ['refund_desc' => '订单支付超时',]);
                return true;
            }
            $res = $this->orderService->info(['order_sn' => $message['out_trade_no']]);
            if ($res && $res['status'] == 2) return true;
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
        return $response;
    }
}
