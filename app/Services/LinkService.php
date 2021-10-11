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
        $sort = $request->input('sort_id', 0);
        $data = $this->hGetCache('link', $sort, $sids);
        if (!$data) {
            $where[] = ['is_delete', '=', 0];
            $data = $this->linkModel->select('id', 'sort_id', 'name', 'image', 'svg', 'url')->where($where)->get()->toArray();
            foreach ($data as $k => $v) {
                $this->redis::HSET('link:' . $v['sort_id'], $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
            }
            $data = $this->hGetCache('link', $sort, $sids);
        }
        foreach ($data as $k => &$v) {
            $v = json_decode($v, true);
        }
        return array_values($data);
    }
}
