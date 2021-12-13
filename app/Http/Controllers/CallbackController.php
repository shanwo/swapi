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
    protected $userService;
    protected $orderService;
    protected $factory;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userService = new UserService();
        $this->orderService = new OrderService();
        $this->factory = new Factory();
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
        $data = $this->userService->signInResult($this->request, 'official');
        if (!empty($data['msg'])) {
            $this->msg = $data['msg'];
            return $this->returnApi();
        }
        $this->code = 200;
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
        $data = $this->userService->signInResult($this->request, 'wechat');
        if (!empty($data['msg'])) {
            $this->msg = $data['msg'];
            return $this->returnApi();
        }
        $this->code = 200;
        return $this->returnApi($data);
    }

    /**
     * 支付回调
     */
    public function payment()
    {
        $this->service->redis::SETEX('callback:payment:' . time(), $this->service->oneDay / 2, json_encode($this->request->getContent()));
        $config = $this->service->configInfo('payment');
        $payment = $this->factory::Payment($config);
        $payment['request'] = $this->request;
        //支付结果通知
        return $payment->handlePaidNotify(function ($message, $fail) use ($payment) {
            $info = $this->service->redis::GET('pay_order:' . $message['out_trade_no']);
            $info = json_decode($info, true) ?? [];
            if (!$info) {
                $payment->refund->byTransactionId($message['transaction_id'], $message['out_trade_no'], $message['total_fee'], $message['cash_fee'], ['refund_desc' => '订单支付超时']);
                return true;
            }
            $res = $this->orderService->info(['order_sn' => $message['out_trade_no']]);
            if ($res && $res['status'] == OrderService::ORDER_SUCCESS) {
                return true;
            }
            if ($message['return_code'] === 'SUCCESS') {
                if ($message['result_code'] === 'SUCCESS') {
                    $this->orderService->add($message, $info);
                } else {
                    $this->orderService->status($res['id'], OrderService::ORDER_FAIL);
                }
                $this->service->redis::DEL('pay_order:' . $message['out_trade_no']);
                return true;
            } else {
                return $fail('支付验证失败,请查看日志:' . time());
            }
        });
    }

    //退款回调
    public function refund()
    {
        $this->service->redis::SETEX('callback:refund:' . time(), $this->service->oneDay / 2, json_encode($this->request->getContent()));
        $config = $this->service->configInfo('payment');
        $config['cert_path'] = app_path('Exceptions' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'apiclient_cert.pem');
        $config['key_path'] = app_path('Exceptions' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'apiclient_key.pem');
        $payment = $this->factory::Payment($config);
        $payment['request'] = $this->request;
        //退款结果通知
        return $payment->handleRefundedNotify(function ($message, $reqInfo, $fail) {
            // 其中 $message['req_info'] 获取到的是加密信息
            // $reqInfo 为 message['req_info'] 解密后的信息
            // 你的业务逻辑...
            $this->orderService->status($res['id'] ?? 0, OrderService::ORDER_REFUND);
            return true; // 返回 true 告诉微信“我已处理完成”
            // 或返回错误原因 $fail('参数格式校验错误');
        });
    }
}
