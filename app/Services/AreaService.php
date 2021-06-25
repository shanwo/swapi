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
        $lists = $this->getData('cn_area:' . $pid);
        if (empty($lists)) {
            $where[] = ['pid', '=', $pid];
            $lists = $this->areaModel->where($where)->get()->toJson();
            ini_set('max_execution_time', '0');
            $this->setData('cn_area:' . $pid, $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
}
