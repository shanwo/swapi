<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\PageModel;

class PageService extends Service
{
    protected $pageModel;

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new PageModel();
    }

    public function lists($uid)
    {
        $lists = $this->redis::GET('page:' . $uid);
        if (empty($lists)) {
            $where[] = ['uid', '=', $uid];
            $lists = $this->pageModel->where($where)->get()->toJson();
            $this->setData('page:' . $uid, $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
    public function info($id)
    {
        return $this->pageModel->whereKey($id)->first();
    }
}
