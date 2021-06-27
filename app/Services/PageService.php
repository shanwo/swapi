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
            $lists = $this->pageModel->where($where)->select('id','name','signature','follow','is_open','head_config','created_at')->get()->toArray();
            foreach ($lists as $k=>&$v){
                $v['head_config'] = json_decode($v['head_config']);
            }
            $lists = json_encode($lists);
            $this->setData('page:' . $uid, $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
    public function info($id)
    {
        $info = $this->pageModel->whereKey($id)->first();
        $info['head_config'] = json_decode($info['head_config'], true);
        $info['center_config'] = json_decode($info['center_config'], true);
        $info['bottom_config'] = json_decode($info['bottom_config'], true);
        return $info;
    }
}
