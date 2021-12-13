<?php
/**
 * User: Powerless
 * Date: 21/08/17
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\OrderModel;
use App\Models\UserModel;
use function Symfony\Component\Translation\t;

class OrderService extends Service
{
    const ORDER_CREATE = 10;
    const ORDER_CANCEL = 20;
    const ORDER_SUCCESS = 30;
    const ORDER_FAIL = 40;
    const ORDER_REFUND = 50;
    protected $userModel;
    protected $orderModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->orderModel = new OrderModel();
    }

    public function info($where)
    {
        return $this->orderModel->where($where)->first();
    }

    public function add($request, $info)
    {
        $user = $info['user'];
        $pay = $info['pay'];
        $insert = [
            'user_id'        => $user['id'],
            'pay_id'         => $pay['id'],
            'channel'        => $pay['channel'],
            'order_sn'       => $request['out_trade_no'],
            'third_sn'       => $request['transaction_id'],
            'pay_type'       => $request['trade_type'],
            'original_price' => $pay['original_price'],
            'activity_price' => $pay['activity_price'],
            'pay_price'      => $request['cash_fee'],
            'status'         => self::ORDER_SUCCESS,
            'payated_at'     => date('Y-m-d H:i:s', strtotime($request['time_end'])),
            'snapshot'       => json_encode($request, JSON_UNESCAPED_UNICODE),
        ];
        $this->orderModel->insertGetId($insert);
        $update['expired_at'] = date('Y-m-d H:i:s', $this->endTime($pay['type']));
        $update['type'] = 2;
        $this->userModel->whereKey($user['id'])->update($update);
        return $this->redis::SET('user_infos:' . $user['id'], json_encode(array_merge($user, $update), JSON_UNESCAPED_UNICODE));
    }

    public function edit($expire, $user)
    {
        $update['expired_at'] = date('Y-m-d H:i:s', $expire);
        if ($expire < time()) {
            $update['type'] = 1;
        }
        $this->userModel->whereKey($user['id'])->update($update);
        return $this->redis::SET('user_infos:' . $user['id'], json_encode(array_merge($user, $update), JSON_UNESCAPED_UNICODE));
    }

    public function status($id, $status)
    {
        $update['status'] = $status;
        $this->orderModel->whereKey($id)->update($update);
    }
}
