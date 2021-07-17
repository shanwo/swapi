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
        $lists = $this->redis::GET('decoration:' . $sort);
        if (empty($lists)) {
            if ($sort) $sids = [$sort];
            $lists = $this->decorationModel->select('id', 'sort_id', 'image')
                ->whereIn('sort_id', $sids)->orderBy('order', 'DESC')->get()->toJson();
            $this->setData('decoration:' . $sort, $lists);
        }
        return json_decode($lists, true);
    }
}
