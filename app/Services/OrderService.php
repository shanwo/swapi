<?php
/**
 * User: Powerless
 * Date: 21/08/17
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\OrderModel;
use App\Models\UserModel;

class OrderService extends Service
{
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
            'channel'        => $pay['channel'],
            'order_sn'       => $request['out_trade_no'],
            'third_sn'       => $request['transaction_id'],
            'pay_type'       => $request['trade_type'],
            'original_price' => $pay['original_price'],
            'activity_price' => $pay['activity_price'],
            'pay_price'      => $request['cash_fee'],
            'status'         => 2,
            'payated_at'     => date('Y-m-d H:i:s', strtotime($request['time_end'])),
            'snapshot'       => json_encode($request, JSON_UNESCAPED_UNICODE),
        ];
        $this->orderModel->insertGetId($insert);
        switch ($pay['type']) {
            case 1:
                $update['expired_at'] = date('Y-m-d H:i:s', strtotime('+1 year'));
                break;
            case 2:
                $update['expired_at'] = date('Y-m-d H:i:s', strtotime('+3 month'));
                break;
            case 3:
                $update['expired_at'] = date('Y-m-d H:i:s', strtotime('+1 month'));
                break;
            case 4:
                $update['expired_at'] = date('Y-m-d H:i:s', strtotime('+1 week'));
                break;
            case 5:
                $update['expired_at'] = date('Y-m-d H:i:s', strtotime('+1 day'));
                break;
            default:
                $update['expired_at'] = date('Y-m-d H:i:s');
        }
        $update['type'] = 2;
        $this->userModel->whereKey($user['id'])->update($update);
        return $this->redis::SET('user_infos:' . $user['id'], json_encode(array_merge($user,$update), JSON_UNESCAPED_UNICODE));
    }
}
