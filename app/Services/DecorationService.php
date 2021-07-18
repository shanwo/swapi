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
        $sort = $request->input('sort_id', 0);
        $data = $this->hGetCache('template', $sort, $sids);
        if (!$data) {
            $where[] = ['is_delete', '=', 0];
            $data = $this->decorationModel->select('id', 'sort_id', 'image')->where($where)->get()->toArray();
            $this->hSetCache('template', $data);
            $data = $this->hGetCache('template', $sort, $sids);
        }
        foreach ($data as $k => &$v) {
            $v = json_decode($v, true);
        }
        return array_values($data);
    }
}
