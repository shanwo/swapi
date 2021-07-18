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
        $sort = $request->input('sort_id', 0);
        $data = $this->hGetCache('tutorials', $sort, $sids);
        if (!$data) {
            $where[] = ['is_delete', '=', 0];
            $data = $this->tutorialsModel->select('id', 'sort_id', 'name', 'image', 'created_at')->where($where)->get()->toArray();
            $this->hSetCache('tutorials', $data);
            $data = $this->hGetCache('tutorials', $sort, $sids);
        }
        foreach ($data as $k => &$v) {
            $v = json_decode($v, true);
        }
        return array_values($data);
    }

}
