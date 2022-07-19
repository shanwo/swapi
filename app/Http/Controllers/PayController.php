<?php
/**
 * User: Powerless
 * Date: 21/08/09
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\AdService;
use App\Services\PayService;
use EasyWeChat\Pay\Application;
use Illuminate\Http\Request;

class PayController extends Controller
{
    protected $payService;
    protected $adService;
    protected $payment;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->adService = new AdService();
        $this->payService = new PayService();
        $config = $this->service->configInfo('payment');
        $config['private_key']= env('PRIVATE_KEY');
        $config['certificate']= env('CERTIFICATE');
        $this->payment = new Application($config);
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
            'channel' => 'filled|integer|gt:0',
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
        $info = json_decode($pay, true);
        $type = $this->request->input('type');
        $user = $this->request->user();
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
        if ($type === 'JSAPI') {
            $url = 'v3/pay/transactions/jsapi';
            $third = array_column($user->third, 'open_id', 'source');
            $body['payer'] = [
                'openid' => $third['official'] ?? ''
            ];
        }
        $data = $this->payment->getClient()->postJson($url, $body)->toArray();
        $data['order_sn'] = $order_sn;
        $this->service->redis::SETEX('pay_order:' . $order_sn, $this->service->oneDay / 24, json_encode(['pay' => $info, 'user' => $user], JSON_UNESCAPED_UNICODE));
        $this->code = 200;
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
        $order_sn = $this->request->input('order_sn');
        $data = $this->payment->getClient()->withMchId()->get('v3/pay/transactions/out-trade-no/' . $order_sn)->toArray();
        $data['ad'] = $this->adService->info($this->request, 10);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
