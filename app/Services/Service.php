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
    protected $timeOut = 3600;
    protected $configModel;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->configModel = new ConfigModel();
    }

    public function configInfo($name)
    {
        $config = $this->redis::GET('config:' . $name);
        if (!$config) {
            $data = $this->configModel->where(['option_name' => $name])->first();
            $data['option_value'] = json_decode($data['option_value'], true);
            $this->setData('config:' . $name, json_encode($data,JSON_UNESCAPED_UNICODE));
        } else {
            $data = json_decode($config, true);
        }
        return $data['option_value'];
    }

    protected function setData($key, $data)
    {
        if ($data) {
            $this->redis::SET($key, $data);
        } else {
            $this->redis::SETEX($key, $this->timeOut, $data);
        }
    }
}
