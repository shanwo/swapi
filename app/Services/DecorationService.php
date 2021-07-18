<?php
/**
 * User: Powerless
 * Date: 2021/7/17
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\DecorationModel;

class DecorationService extends Service
{
    protected $decorationModel;

    public function __construct()
    {
        parent::__construct();
        $this->decorationModel = new DecorationModel();
    }

    public function lists($request, $sids = [])
    {
        $sort = $request->input('sort_id', 0);
        $data = $this->hGetCache('decoration', $sort, $sids);
        if (!$data) {
            $where[] = ['is_delete', '=', 0];
            $data = $this->decorationModel->select('id', 'sort_id', 'image')->where($where)->get()->toArray();
            $this->hSetCache('decoration', $data);
            $data = $this->hGetCache('decoration', $sort, $sids);
        }
        foreach ($data as $k => &$v) {
            $v = json_decode($v, true);
        }
        return array_values($data);
    }
}
