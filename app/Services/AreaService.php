<?php
/**
 * User: Powerless
 * Date: 21/06/23
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\AreaModel;

class AreaService extends Service
{
    protected $areaModel;

    public function __construct()
    {
        parent::__construct();
        $this->areaModel = new AreaModel();
    }

    public function lists($request)
    {
        $pid = $request->input('pid', 0);
        $lists = $this->redis::GET('cn_area:' . $pid);
        if (empty($lists)) {
            $where[] = ['pid', '=', $pid];
            $lists = $this->areaModel->where($where)->get()->toJson();
            ini_set('max_execution_time', '0');
            if ($lists) {
                $this->redis::SET('cn_area:' . $pid, $lists);
            } else {
                $this->redis::SETEX('cn_area:' . $pid, 1, $lists);
            }
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
}
