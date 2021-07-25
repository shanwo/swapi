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
    public $redis;
    public $page = 1;
    public $size = 5;
    protected $timeOut = 6;
    protected $configModel;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->configModel = new ConfigModel();
    }

    public function configInfo($name)
    {
        $data = [];
        $config = $this->redis::HGET('config', $name);
        if ($config) {
            $data = json_decode($config, true);
        } else {
            $lists = $this->configModel->get()->toArray();
            foreach ($lists as $k => &$v) {
                $v['option_value'] = json_decode($v['option_value'], true);
                $this->redis::HSET('config', $v['option_name'], json_encode($v, JSON_UNESCAPED_UNICODE));
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

    public function hSetCache($key, $data)
    {
        foreach ($data as $k => $v) {
            $this->redis::HSET($key . ':' . $v['sort_id'], $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
        }
    }

    protected function setData($key, $data)
    {
//        if ($data) {
//            $this->redis::SET($key, $data);
//        } else {
            $this->redis::SETEX($key, $this->timeOut, $data);
//        }
    }
}
