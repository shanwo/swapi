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
    protected $redis;
    protected $configModel;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->configModel = new ConfigModel();
    }

    public function configInfo($name)
    {
        $config = $this->redis::get('config:' . $name);
        if(!$config){
            $data = $this->configModel->where(['option_name'=>$name])->first();
            $data['option_value'] = json_decode($data['option_value'],true);
            $this->redis::setnx('config:' . $name, json_encode($data));
        }else{
            $data = json_decode($config,true);
        }
        return $data['option_value'];
    }
}
