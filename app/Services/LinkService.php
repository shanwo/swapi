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
    public function lists($request)
    {
        $type = $request->input('type');
        $lists = $this->redis::GET('link:' . $type);
        if (empty($lists)) {
            $where[] = ['type', '=', $type];
            $lists = $this->linkModel->select('id', 'name', 'image', 'url', 'created_at')->where($where)->get()->toJson();
            $this->setData('link:' . $type, $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }

    public function info($id)
    {
        return $this->linkModel->whereKey($id)->first();
    }
}
