<?php
/**
 * User: Powerless
 * Date: 21/08/09
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\PayService;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

class PayController extends Controller
{
    protected $payService;
    protected $payment;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->payService = new PayService();
        $config = config('wechat.payment.default');
        $this->payment = Factory::Payment($config);
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
        $data = $this->payService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function send()
    {
        $this->validate([
            'id'      => 'filled|integer|gt:0',
            'channel' => 'filled|integer|gt:0',
        ], [
            'id'      => 'ID',
            'channel' => '渠道',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $info = $this->payService->send($this->request);
        $order_sn = $info['channel'] . time();
        $total_fee = $info['original_price'];
        if ($info['activity_price']) $total_fee = $info['activity_price'];
        $body = '闪我科技-'.$info['name'].'会员购买';
        $data['native'] = $this->payment->order->unify([
            'trade_type'       => 'NATIVE',
            'body'             => $body,
            'out_trade_no'     => $order_sn,
            'total_fee'        => $total_fee,
            'spbill_create_ip' => $this->request->ip(), // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
        ]);
        $data['jsapi'] = $this->payment->order->unify([
            'trade_type'       => 'JSAPI',
            'body'             => $body,
            'out_trade_no'     => $order_sn,
            'total_fee'        => $total_fee,
            'spbill_create_ip' => $this->request->ip(), // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
        ]);
        $data['scheme'] = $this->payment->scheme($order_sn);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function result()
    {
        $this->validate([
            'order_sn' => 'filled|integer|gt:0',
        ], [
            'order_sn' => '渠道',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $order_sn = $this->request->input('order_sn');
        $data = $this->payment->order->queryByOutTradeNumber($order_sn);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
