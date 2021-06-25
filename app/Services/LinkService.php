<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\LinkModel;

class LinkService extends Service
{
    protected $linkModel;

    public function __construct()
    {
        parent::__construct();
        $this->linkModel = new LinkModel();
    }

    public function lists($uid)
    {
        $lists = $this->redis::GET('link:' . $uid);
        if (empty($lists)) {
            $where[] = ['uid', '=', $uid];
            $lists = $this->linkModel->where($where)->get()->toJson();
            $this->setData('link:' . $uid, $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
    public function info($id)
    {
        return $this->linkModel->whereKey($id)->first();
    }
}
