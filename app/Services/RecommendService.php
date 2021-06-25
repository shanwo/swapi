<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\RecommendModel;

class RecommendService extends Service
{
    protected $recommendModel;

    public function __construct()
    {
        parent::__construct();
        $this->recommendModel = new RecommendModel();
    }

    public function lists($uid)
    {
        $lists = $this->redis::GET('recommend:' . $uid);
        if (empty($lists)) {
            $where[] = ['uid', '=', $uid];
            $lists = $this->recommendModel->where($where)->get()->toJson();
            $this->setData('recommend:' . $uid, $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
    public function info($id)
    {
        return $this->recommendModel->whereKey($id)->first();
    }
}
