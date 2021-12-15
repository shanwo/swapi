<?php
/**
 * User: Powerless
 * Date: 2021/7/18
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\TutorialsModel;

class TutorialsService extends Service
{
    protected $tutorialsModel;

    public function __construct()
    {
        parent::__construct();
        $this->tutorialsModel = new TutorialsModel();
    }

    public function lists($request, $sids = [])
    {
        $sort = $request->input('sort_id', Service::SORT_ALL);
        $data = $this->hGetCache('tutorials', $sort, $sids);
        if (!$data) {
            $where[] = ['is_delete', '=', Service::DELETE_NO];
            $data = $this->tutorialsModel->select('id', 'sort_id', 'name', 'image')->where($where)->get()->toArray();
            foreach ($data as $k => $v) {
                $this->redis::HSET('tutorials:' . $v['sort_id'], $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
            }
            $data = $this->hGetCache('tutorials', $sort, $sids);
        }
        foreach ($data as $k => &$v) {
            $v = json_decode($v, true);
        }
        return array_values($data);
    }

}
