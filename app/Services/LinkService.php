<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\LinkModel;

class LinkService extends Service
{
    protected $linkModel;

    public function __construct()
    {
        parent::__construct();
        $this->linkModel = new LinkModel();
    }

    public function lists($request, $sids = [])
    {
        $data = [];
        $sort = $request->input('sort_id', 0);
        if (!$sort) {
            foreach ($sids as $k => $v) {
                $tmp = $this->redis::HGETALL('link:' . $v);
                $data = array_merge($data, $tmp);
            }
        } else {
            $data = $this->redis::HGETALL('link:' . $sort);
        }
        if (!$data) {
            $this->setCache();
            $data = $this->redis::HGETALL('link:' . $sort);
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
        $where[] = ['status', '=', 1];
        $data = $this->linkModel->select('id', 'sort_id', 'name', 'image', 'svg', 'url', 'created_at')->where($where)->get()->toArray();
        foreach ($data as $k => $v) {
            $this->redis::HSET('link:' . $v['sort_id'], $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
        }
    }

    public function info($id)
    {
        return $this->linkModel->whereKey($id)->first();
    }
}
