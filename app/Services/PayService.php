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
    protected $pageModel;

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new PayModel();
        $this->payment = $this->configInfo('payment');
    }

    public function lists($request)
    {
        $channel = $request->input('channel', 0);
        $data = $this->redis::HGETALL('pay_channel:' . $channel);
        if (!$data) {
            $where[] = ['channel', '=', $channel];
            $where[] = ['is_delete', '=', Service::DELETE_NO];
            $data = $this->pageModel->select('id', 'name', 'channel', 'original_price', 'activity_price', 'description', 'type')->where($where)->get()->toArray();
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
}
