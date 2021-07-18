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
        $data = $this->redis::HGETALL('sort:' . $type);
        if (!$data) {
            $lists = $this->sortModel->select('id', 'name', 'type')->get()->toArray();
            foreach ($lists as $k => $v) {
                $tmp = $v['type'];
                unset($v['type']);
                $this->redis::HSET('sort:' . $tmp, $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
                if ($tmp == $type) {
                    $data[] = $v;
                }
            }
        } else {
            foreach ($data as $k => &$v) {
                $v = json_decode($v, true);
            }
        }
        return array_values($data);
    }
}
