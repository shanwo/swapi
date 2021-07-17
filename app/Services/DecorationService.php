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

    //使用hash结构
    public function lists($request)
    {
        $sort = $request->input('sort_id', 0);
        $lists = $this->redis::GET('decoration:' . $sort);
        if (empty($lists)) {
            $where[] = ['ss.type', '=', 1];
            if ($sort) $where[] = ['ss.id', '=', $sort];
            $lists = $this->decorationModel->from('sw_decoration as sd')
                ->select('sd.id', 'sd.sort_id', 'ss.name as sort_name', 'sd.image')
                ->leftJoin('sw_sort as ss', 'sd.sort_id', '=', 'ss.id')
                ->where($where)->orderBy('sd.order', 'DESC')->get()->toJson();
            $this->setData('decoration:' . $sort, $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
}
