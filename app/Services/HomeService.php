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
            $where[] = ['browse', '>', 0];
            $where[] = ['is_open', '=', 1];
            $where[] = ['is_delete', '=', 0];
            $lists = $this->pageModel->select('id', 'background', 'head_config')->where($where);
            $data['total'] = $lists->count();
            $lists = $lists->orderBy('sort', 'DESC')->orderBy('browse', 'DESC')->get()->toArray();
            foreach ($lists as $k => &$v) {
                $v['head_config'] = json_decode($v['head_config']);
            }
            $lists = json_encode($lists);
            $this->setData('hot:' . $page, $lists);
        }
        $data['page'] = $page;
        $data['size'] = $this->size;
        $data['lists'] = json_decode($lists, true);
        return $data;
    }

    //使用set结构
    public function recent($request)
    {
        $page = $request->input('page', $this->page);
        $lists = $this->redis::GET('recent:' . $page);
        if (empty($lists)) {
            $where[] = ['is_open', '=', 1];
            $where[] = ['is_delete', '=', 0];
            $lists = $this->pageModel->select('id', 'background', 'head_config')->where($where);
            $data['total'] = $lists->count();
            $lists = $lists->orderBy('updated_at', 'DESC')->orderBy('browse', 'ASC')->get()->toArray();
            foreach ($lists as $k => &$v) {
                $v['head_config'] = json_decode($v['head_config']);
            }
            $lists = json_encode($lists);
            $this->setData('recent:' . $page, $lists);
        }
        $data['page'] = $page;
        $data['size'] = $this->size;
        $data['lists'] = json_decode($lists, true);
        return $data;
    }

    //使用string结构
    public function info($request)
    {
        $id =  $request->input('id',0);
        if($id){
            $where[] = ['id', '=', $id];
        }else{
            $code =  $request->input('code',0);
            $where[] = ['code', '=', $code];
        }
        $info = $this->pageModel->where($where)->first();
        $info['head_config'] = json_decode($info['head_config'], true);
        $info['center_config'] = json_decode($info['center_config'], true);
        $info['bottom_config'] = json_decode($info['bottom_config'], true);
        return $info;
    }
}
