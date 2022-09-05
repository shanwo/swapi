<?php
/**
 * User: Powerless
 * Date: 21/08/09
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\PayModel;

class PayService extends Service
{
    protected $payment;
    protected $payModel;

    public function __construct()
    {
        parent::__construct();
        $this->payModel = new PayModel();
        $this->payment = $this->configInfo('payment');
    }

    public function lists($request)
    {
        $channel = $request->input('channel', 0);
        $data = $this->redis::HGETALL('pay_channel:' . $channel);
        if (!$data) {
            $where[] = ['channel', '=', $channel];
            $where[] = ['is_delete', '=', Service::DELETE_NO];
            $data = $this->payModel->select('id', 'name', 'channel', 'original_price', 'activity_price', 'description', 'type')->where($where)->get()->toArray();
            foreach ($data as $k => $v) {
                $v['original_price'] = $v['original_price'] / 100;
                $v['activity_price'] = $v['activity_price'] / 100;
                $this->redis::HSET('pay_channel:' . $channel, $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
            }
        } else {
            foreach ($data as $k => &$v) {
                $v = json_decode($v, true);
                $v['original_price'] = $v['original_price'] / 100;
                $v['activity_price'] = $v['activity_price'] / 100;
            }
        }
        return $data;
    }

    public function send($info,$user,$type='NATIVE')
    {
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
        $this->redis::SETEX('pay_order:' . $order_sn, $this->oneDay / 24, json_encode(['pay' => $info, 'user' => $user], JSON_UNESCAPED_UNICODE));
        return $data;
    }
}
