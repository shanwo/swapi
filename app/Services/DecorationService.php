<?php
/**
 * User: Powerless
 * Date: 2021/7/17
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\DecorationModel;
use App\Models\SortModel;

class DecorationService extends Service
{
    protected $sortModel;
    protected $decorationModel;

    public function __construct()
    {
        parent::__construct();
        $this->sortModel = new SortModel();
        $this->decorationModel = new DecorationModel();
    }

    public function lists($request, $sids = [])
    {
        $data = [];
        $sort = $request->input('sort_id', 0);
        if (!$sort) {
            foreach ($sids as $k => $v) {
                $tmp = $this->redis::HGETALL('decoration:' . $v);
                $data = array_merge($data, $tmp);
            }
        } else {
            $data = $this->redis::HGETALL('decoration:' . $sort);
        }
        if (!$data) {
            $this->setCache();
            $data = $this->redis::HGETALL('decoration:' . $sort);
        }
        foreach ($data as $k => &$v) {
            $v = json_decode($v, true);
            if ($sort && !in_array($v['sort_id'], $sids)) {
                unset($v);
            }
        }
        return array_values($data);
    }

    private function setCache()
    {
        $data = $this->decorationModel->select('id', 'sort_id', 'image')->get()->toArray();
        foreach ($data as $k => $v) {
            $this->redis::HSET('decoration:' . $v['sort_id'], $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
        }
    }
}
