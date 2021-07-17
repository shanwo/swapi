<?php
/**
 * User: Powerless
 * Date: 2021/7/17
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\SortModel;

class SortService extends Service
{
    private $sortModel;

    public function __construct()
    {
        parent::__construct();
        $this->sortModel = new SortModel();
    }

    public function lists($type)
    {
        $sort = $this->redis::HGETALL('sort:' . $type);
        if(!$sort){
            $this->setCache();
            $sort = $this->redis::HGETALL('sort:' . $type);
        }
        foreach ($sort as $k => &$v){
            $v = json_decode($v,true);
        }
        return array_values($sort);
    }

    private function setCache()
    {
        $data = $this->sortModel->select('id', 'name','type')->get()->toArray();
        foreach ($data as $k => $v) {
            $type = $v['type'];
            unset($v['type']);
            $this->redis::HSET('sort:' . $type, $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
        }
    }
}
