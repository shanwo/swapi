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

    //使用hash结构
    public function lists($request, $sids = [])
    {
        $sort = $request->input('sort_id', 0);
        $lists = $this->redis::GET('link:' . $sort);
        if (empty($lists)) {
            $where[] = ['status', '=', 1];
            if ($sort) $sids = [$sort];
            $lists = $this->linkModel->select('id', 'sort_id', 'name', 'image', 'svg', 'url', 'created_at')
                ->where($where)->whereIn('sort_id', $sids)->orderBy('created_at', 'DESC')->get()->toJson();
            $this->setData('link:' . $sort, $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }

    public function info($id)
    {
        return $this->linkModel->whereKey($id)->first();
    }
}
