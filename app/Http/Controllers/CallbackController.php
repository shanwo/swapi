<?php
/**
 * User: Powerless
 * Date: 21/07/15
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\UserService;
use EasyWeChat\Pay\Application;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    protected $userService;
    protected $orderService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userService = new UserService();
        $this->orderService = new OrderService();
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
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function payment()
    {
        $this->service->redis::SETEX('callback:payment:' . time(), $this->service->oneDay / 2, json_encode($this->request->getContent()));
        $config = $this->service->configInfo('payment');
        $payment = (new Application($config));
        $payment->setRequest($this->request);
        return $payment->handlePaid(function ($message, $fail) use ($payment) {
            $info = $this->service->redis::GET('pay_order:' . $message['out_trade_no']);
            $info = json_decode($info, true) ?? [];
            if (!$info) {
                $payment->byTransactionId($message['transaction_id'], $message['out_trade_no'], $message['total_fee'], $message['cash_fee'], ['refund_desc' => '订单支付超时']);
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

    /**
     * 退款回调
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function refund()
    {
        $this->service->redis::SETEX('callback:refund:' . time(), $this->service->oneDay / 2, json_encode($this->request->getContent()));
        $config = $this->service->configInfo('payment');
        $config['cert_path'] = app_path('Exceptions' . DIRECTORY_SEPARATOR . 'certs' . DIRECTORY_SEPARATOR . 'apiclient_cert.pem');
        $config['key_path'] = app_path('Exceptions' . DIRECTORY_SEPARATOR . 'certs' . DIRECTORY_SEPARATOR . 'apiclient_key.pem');
        $payment = (new Application($config));
        $payment['request'] = $this->request;
        return $payment->handleRefunded(function ($message, $reqInfo, $fail) {
            $res = $this->orderService->info(['order_sn' => $message['out_trade_no']]);
            $pay = $this->service->redis::HGET('pay_channel:' . $res['channel'], $res['pay_id']);
            $pay = json_decode($pay, true);
            $user = $this->userService->info($res['user_id']);
            if ($user['type'] != OrderService::USER_VIP) {
                return $fail('非会员状态不可发起退款');
            }
            $expired = strtotime($user['expired_at']);
            $endTime = time() + $this->service->lengthTime($pay['type']) - 60;
            if ($expired < $endTime) {
                return $fail('会员到期时间小于购买时间，不可发起退款');
            }
            $expired = $expired - $this->service->lengthTime($pay['type']);
            $this->orderService->edit($expired, $user);
            $this->orderService->status($res['id'], OrderService::ORDER_REFUND);
            return true;
        });
    }
}