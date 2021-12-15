<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\ConfigModel;
use Illuminate\Support\Facades\Redis;

class Service
{
    const ORDER_CREATE = 10;
    const ORDER_CANCEL = 20;
    const ORDER_SUCCESS = 30;
    const ORDER_FAIL = 40;
    const ORDER_REFUND = 50;
    const USER_ENABLE  = 1;
    const USER_VIP = 2;
    const USER_DISABLE = 3;
    const DELETE_YES = -1;
    const DELETE_NO = 1;
    const SORT_ALL = 0;
    const SORT_DECORATION = 1;
    const SORT_TUTORIALS = 2;
    const SORT_TEMPLATE = 3;
    const SORT_lINT = 4;
    public $redis;
    public $page = 1;
    public $size = 5;
    public $oneDay = 86400;
    protected $configModel;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->configModel = new ConfigModel();
    }

    public function configInfo($name)
    {
        $data = [];
        $config = $this->redis::GET('config:' . $name);
        if ($config) {
            $data = json_decode($config, true);
        } else {
            $lists = $this->configModel->where(['autoload' => 1, 'is_delete' => Service::DELETE_NO])->get()->toArray();
            foreach ($lists as $k => $v) {
                $v['option_value'] = json_decode($v['option_value'], true);
                $this->redis::SET('config:' . $v['option_name'], json_encode($v, JSON_UNESCAPED_UNICODE));
                if ($v['option_name'] == $name) {
                    $data = $v;
                }
            }
        }
        return $data['option_value'];
    }

    public function hGetCache($key, $sort, $sids)
    {
        $data = [];
        if ($sort) {
            $data = $this->redis::HGETALL($key . ':' . $sort);
        } else {
            foreach ($sids as $k => $v) {
                $tmp = $this->redis::HGETALL($key . ':' . $v);
                if ($tmp) $data = array_merge_recursive($data, $tmp);
            }
        }
        return $data;
    }

    public function endTime($type)
    {
        switch ($type) {
            case 1:
                $time = strtotime('+1 year');
                break;
            case 2:
                $time = strtotime('+3 month');
                break;
            case 3:
                $time = strtotime('+1 month');
                break;
            case 4:
                $time = strtotime('+1 week');
                break;
            case 5:
                $time = strtotime('+1 day');
                break;
            default:
                $time = time();
        }
        return $time - time();
    }
}
