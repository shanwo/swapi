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
    public $size = 10;
    protected $timeOut = 3600;
    protected $configModel;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->configModel = new ConfigModel();
    }

    //使用hash结构
    public function configInfo($name)
    {
        $config = $this->redis::GET('config:' . $name);
        if (!$config) {
            $data = $this->configModel->where(['option_name' => $name])->first();
            $data['option_value'] = json_decode($data['option_value'], true);
            $this->setData('config:' . $name, json_encode($data, JSON_UNESCAPED_UNICODE));
        } else {
            $data = json_decode($config, true);
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
                if ($tmp) $data = array_merge($data, $tmp);
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
}
