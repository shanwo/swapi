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

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->adService = new AdService();
        $this->payService = new PayService();
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
        $data = $this->payService->send($info,$type,$user);
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
        $config = $this->service->configInfo('payment');
        $config['private_key']= env('PRIVATE_KEY');
        $config['certificate']= env('CERTIFICATE');
        $payment = new Application($config);
        $data = $payment->getClient()->get('v3/pay/transactions/out-trade-no/' . $order_sn, [
            'query' => ['mchid' => $payment->getConfig()->get('mch_id')]
        ])->toArray();
        $data['ad'] = $this->adService->info($this->request, 10);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
