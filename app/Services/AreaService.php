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
        $level = $request->input('level', 0);
        switch ($level) {
            case 1:
                $list = $this->redis::get('cn_area:province:' . $pid);
                break;
            case 2:
                $list = $this->redis::get('cn_area:city:' . $pid);
                break;
            case 3:
                $list = $this->redis::get('cn_area:district:' . $pid);
                break;
            default:
                $list = $this->redis::get('cn_area:all');
        }
        if (!$list) {
            $where = [];
            if ($pid) $where[] = ['pid', '=', $pid];
            if($level) $where[] = ['level', '=', $level];
            $list = $this->areaModel->where($where)->get()->toArray();
            ini_set('max_execution_time', '0');
            switch ($level) {
                case 1:
                    $this->redis::set('cn_area:province:' . $pid, json_encode($list));
                    break;
                case 2:
                    $this->redis::set('cn_area:city:' . $pid, json_encode($list));
                    break;
                case 3:
                    $this->redis::set('cn_area:district:' . $pid, json_encode($list));
                    break;
                default:
                    $this->redis::set('cn_area:all', json_encode($list));
            }
            $data['lists'] = $list;
        } else {
            $data['lists'] = json_decode($list, true);
        }
        return $data;
    }
}
