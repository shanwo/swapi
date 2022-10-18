<?php
/**
 * User: Powerless
 * Date: 21/08/09
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\PayService;
use App\Services\UserAttributeService;
use EasyWeChat\Pay\Application;
use Illuminate\Http\Request;

class PayController extends Controller
{
    protected $payment;
    protected $payService;
    protected $orderService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->payService = new PayService();
        $config = $this->configInfo('payment');
        $config['private_key'] = env('PRIVATE_KEY');
        $config['certificate'] = env('CERTIFICATE');
        $this->payment = new Application($config);
        $this->orderService = new OrderService();
    }

    public function lists()
    {
        $this->validate([
            'channel' => 'filled|integer|gt:0',
        ], [
            'channel' => '渠道',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data['lists'] = $this->payService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function send()
    {
        $this->validate([
            'id'      => 'required|integer|gt:0',
            'type'    => 'required|alpha_num|in:NATIVE,JSAPI',
            'channel' => 'filled|integer|gte:0',
        ], [
            'id'      => 'ID',
            'channel' => '渠道',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = $this->request->input('id');
        $channel = $this->request->input('channel', 0);
        $pay = $this->service->redis::HGET('pay_channel:' . $channel, $id);
        if (!$pay) {
            $this->msg = '支付渠道不存在';
            return $this->returnApi();
        }
        $user = $this->request->user();
        $info = json_decode($pay, true);
        $order_sn = dechex(time()) . rand(10, 99) . mb_substr(sprintf("%04d", $user->id), -4) . rand(10, 99);
        $total_fee = $info['original_price'];
        if ($info['activity_price']) $total_fee = $info['activity_price'];
        $url = 'v3/pay/transactions/native';
        $body = [
            'appid'        => $this->payment->getConfig()->get('app_id'),
            'mchid'        => $this->payment->getConfig()->get('mch_id'),
            'description'  => $info['name'] . ' - 会员购买',
            'out_trade_no' => $order_sn,
            'notify_url'   => $this->payment->getConfig()->get('notify_url'),
            'amount'       => [
                'total'    => $total_fee,
                'currency' => 'CNY'
            ],
        ];
        $type = $this->request->input('type');
        if ($type === 'JSAPI') {
            $url = 'v3/pay/transactions/jsapi';
            $third = array_column($user->third, 'open_id', 'source');
            $body['payer'] = [
                'openid' => $third['official'] ?? ''
            ];
        }
        try {
            $data = $this->payment->getClient()->postJson($url, $body)->toArray();
            $data['order_sn'] = $order_sn;
            $attribute = $this->service->redis::GET('user_attribute:' . $user->id);
            $attribute =  json_decode($attribute,true);
            $this->code = 200;
            $this->service->redis::SETEX('pay_order:' . $order_sn, $this->service->oneDay / 24, json_encode(['pay' => $info, 'user' => $attribute], JSON_UNESCAPED_UNICODE));
        } catch (\Exception $error) {
            $this->msg = $error->getMessage();
            return $this->returnApi();
        }
        return $this->returnApi($data);
    }

    public function result()
    {
        $this->validate([
            'order_sn' => 'required|alpha_num|size:16',
        ], [
            'order_sn' => '订单编号',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = [];
        try {
            $order_sn = $this->request->input('order_sn');
            $data = $this->payment->getClient()->get('v3/pay/transactions/out-trade-no/' . $order_sn, [
                'query' => ['mchid' => $this->payment->getConfig()->get('mch_id')]
            ])->toArray();
            $this->code = 200;
        } catch (\Exception $error) {
            $this->msg = $error->getMessage();
        }
        return $this->returnApi($data);
    }


    /**
     * 支付回调
     */
    public function payment()
    {
        $this->validate([
            'id'            => 'required|uuid|between:16,64',
            'create_time'   => 'required|date',
            'event_type'    => 'required|string|between:1,32',
            'resource_type' => 'required|alpha_dash|between:1,16',
            'summary'       => 'required|alpha_num|between:1,64',
            'resource'      => 'required|array',
        ], [
            'id'            => '通知ID',
            'create_time'   => '通知创建时间',
            'event_type'    => '通知类型',
            'resource_type' => '通知数据类型',
            'summary'       => '回调摘要',
            'resource'      => '通知数据',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->service->redis::SETEX('callback:payment:' . LARAVEL_START, $this->service->oneDay / 2, json_encode($this->request->getContent()));
        return $this->payment->getServer()->handlePaid(function ($message, $fail) {
            $data = $this->payment->getClient()->get('v3/pay/transactions/out-trade-no/' . $message['out_trade_no'], [
                'query' => ['mchid' => $this->payment->getConfig()->get('mch_id')]
            ])->toArray();
            $res = $this->orderService->info(['order_sn' => $message['out_trade_no']]);
            $this->service->redis::SETEX('callback:payment:' . LARAVEL_START . ':data', $this->service->oneDay / 2, json_encode($data));
            switch ($data['trade_state']) {
                case 'SUCCESS':     //支付成功
                    if ($res) break;
                    $info = $this->service->redis::GET('pay_order:' . $data['out_trade_no']);
                    $info = json_decode($info, true) ?? [];
                    if ($info) {
                        $this->orderService->add($data, $info);
                    } else {
                        $this->payment->getClient()->postJson('v3/refund/domestic/refunds', [
                            'transaction_id' => $data['transaction_id'],     //商户订单号
                            'out_refund_no'  => $data['out_trade_no'],       //商户退款单号
                            'reason'         => '订单支付超时',                 //退款原因
                            'notify_url'     => env('APP_URL') . '/pay/refund',
                            'amount'         => [
                                'refund'   => $data['amount']['payer_total'],//退款金额
                                'total'    => $data['amount']['total'],      //原订单金额
                                'currency' => 'CNY'
                            ]
                        ])->toArray();
                        $this->payment->getClient()->postJson('v3/pay/transactions/out-trade-no/' . $data['out_trade_no'] . '/close', [
                            ['mchid' => $this->payment->getConfig()->get('mch_id')]
                        ])->toArray();
                    }
                    break;
                case 'CLOSED':      //已关闭
                    if ($res && $res['status'] != OrderService::ORDER_SUCCESS) {
                        $this->orderService->status($res['id'], OrderService::ORDER_CANCEL);
                    }
                    break;
                case 'PAYERROR':    //支付失败（仅付款码支付会返回）
                    if ($res && $res['status'] != OrderService::ORDER_SUCCESS) {
                        $this->orderService->status($res['id'], OrderService::ORDER_FAIL);
                    }
                    break;
                case 'REFUND':      //转入退款
                    if ($res && $res['status'] != OrderService::ORDER_SUCCESS) {
                        $this->orderService->status($res['id'], OrderService::ORDER_REFUND);
                    }
                    break;
                case 'NOTPAY':      //未支付
                    break;
                case 'REVOKED':     //已撤销（仅付款码支付会返回）
                    break;
                case 'USERPAYING':  //用户支付中（仅付款码支付会返回）
                    break;
                default:
                    return $fail('支付验证失败,请查看日志:' . time());
            }
            $this->service->redis::DEL('pay_order:' . $message['out_trade_no']);
            return true;
        })->serve();
    }

    /**
     * 退款回调
     */
    public function refund()
    {
        $this->validate([
            'id'            => 'required|uuid|between:16,64',
            'create_time'   => 'required|date',
            'event_type'    => 'required|string|between:1,32',
            'resource_type' => 'required|alpha_dash|between:1,16',
            'summary'       => 'required|alpha_num|between:1,64',
            'resource'      => 'required|array',
        ], [
            'id'            => '通知ID',
            'create_time'   => '通知创建时间',
            'event_type'    => '通知类型',
            'resource_type' => '通知数据类型',
            'summary'       => '回调摘要',
            'resource'      => '通知数据',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->service->redis::SETEX('callback:refund:' . LARAVEL_START, $this->service->oneDay / 2, json_encode($this->request->getContent()));
        return $this->payment->getServer()->handleRefunded(function ($message, $reqInfo, $fail) {
            $res = $this->orderService->info(['order_sn' => $message['out_trade_no']]);
            $pay = $this->service->redis::HGET('pay_channel:' . $res['channel'], $res['pay_id']);
            $pay = json_decode($pay, true);
            $user = (new UserAttributeService())->info($res['user_id']);
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
