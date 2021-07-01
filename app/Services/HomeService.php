<?php
/**
 * User: Powerless
 * Date: 21/07/01
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\PageModel;

class HomeService extends Service
{
    protected $pageModel;

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new PageModel();
    }

    public function hot($request)
    {
        $lists = $this->redis::GET('hot');
        if (empty($lists)) {
            $where[] = ['is_delete', '=', 0];
            $lists = $this->pageModel->where($where)->select('id', 'name', 'signature', 'follow', 'is_open', 'head_config', 'created_at')->get()->toArray();
            foreach ($lists as $k => &$v) {
                $v['head_config'] = json_decode($v['head_config']);
            }
            $lists = json_encode($lists);
            $this->setData('hot', $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }

    public function recommend($request)
    {
        $lists = $this->redis::GET('recommend');
        if (empty($lists)) {
            $where[] = ['is_delete', '=', 0];
            $lists = $this->pageModel->where($where)->select('id', 'name', 'signature', 'follow', 'is_open', 'head_config', 'created_at')->get()->toArray();
            foreach ($lists as $k => &$v) {
                $v['head_config'] = json_decode($v['head_config']);
            }
            $lists = json_encode($lists);
            $this->setData('recommend', $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
}
