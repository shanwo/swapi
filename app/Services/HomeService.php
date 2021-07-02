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

    //使用zset结构
    public function hot($request)
    {
        $page = $request->input('page', $this->page);
        $lists = $this->redis::GET('hot:' . $page);
        if (empty($lists)) {
            $where[] = ['is_delete', '=', 0];
            $lists = $this->pageModel->where($where)->select('id', 'name', 'signature', 'background', 'head_config')->orderBy('browse', 'DESC')->orderBy('sort', 'DESC')->get()->toArray();
            foreach ($lists as $k => &$v) {
                $v['head_config'] = json_decode($v['head_config']);
            }
            $lists = json_encode($lists);
            $this->setData('hot:' . $page, $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
    //使用set结构
    public function recommend($request)
    {
        $page = $request->input('page', $this->page);
        $lists = $this->redis::GET('recommend:' . $page);
        if (empty($lists)) {
            $where[] = ['is_delete', '=', 0];
            $lists = $this->pageModel->where($where)->select('id', 'name', 'signature', 'background', 'head_config')->get()->toArray();
            foreach ($lists as $k => &$v) {
                $v['head_config'] = json_decode($v['head_config']);
            }
            $lists = json_encode($lists);
            $this->setData('recommend:' . $page, $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
    //使用string结构
    public function info()
    {

    }
}
