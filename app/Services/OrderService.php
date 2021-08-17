<?php
/**
 * User: Powerless
 * Date: 21/08/17
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\OrderModel;

class OrderService extends Service
{
    protected $orderModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new OrderModel();
    }

    public function info($request)
    {

    }

    public function add($request)
    {
        $insert = [
            'user_id'       => $user['id'],
            'channel'   => $tid,
            'order_sn'    => $template['background'],
            'pay_type'   => $template['head_schema'],
            'original_price'     => '',
            'pay_price' => $template['center_schema'],
            'status'   => 1,
            'snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
        ];
        return $this->orderModel->insertGetId($insert);
    }
}
