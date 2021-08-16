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
        $config = $this->service->configInfo('payment');
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
        $info = $this->payService->send($this->request);
        $type = $this->request->input('type');
        $order_sn = $type . $info['channel'] . time();
        $total_fee = $info['original_price'];
        if ($info['activity_price']) $total_fee = $info['activity_price'];
        $data = $this->payment->order->unify([
            'trade_type'   => $type,
            'body'         => $info['name'] . ' - 会员购买',
            'out_trade_no' => $order_sn,
            'total_fee'    => $total_fee,
        ]);
        if (!strcmp($data['result_code'] ?? '', 'SUCCESS')) {
            $this->code = 200;
        } else {
            $this->msg = $data['err_code_des'] ?? '';
        }
        return $this->returnApi($data);
    }

    public function result()
    {
        $this->validate([
            'order_sn' => 'required|alpha_num|between:16,32',
        ], [
            'order_sn' => '订单编号',
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
