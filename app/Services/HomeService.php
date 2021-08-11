<?php
/**
 * User: Powerless
 * Date: 21/07/01
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\BrowseModel;
use App\Models\PageModel;

class HomeService extends Service
{
    protected $pageModel;
    protected $browseModel;

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new PageModel();
        $this->browseModel = new BrowseModel();
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
            $lists = $this->pageModel->select('id', 'background', 'head_data')->where($where);
            $data['total'] = $lists->count();
            $lists = $lists->forPage($page, $this->size)->orderBy('sort', 'DESC')->orderBy('browse', 'DESC')->get()->toArray();
            foreach ($lists as $k => &$v) {
                $tmp = json_decode($v['head_data'], true);
                $v = array_merge($v,$tmp);
                unset($v['head_data']);
            }
            $lists = json_encode($lists);
            $this->setData('hot:' . $page, $lists);
        }
        $data['page'] = (int)$page;
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
            $lists = $this->pageModel->select('id', 'background', 'head_data')->where($where);
            $data['total'] = $lists->count();
            $lists = $lists->forPage($page, $this->size)->orderBy('updated_at', 'DESC')->orderBy('browse', 'ASC')->get()->toArray();
            foreach ($lists as $k => &$v) {
                $tmp = json_decode($v['head_data'], true);
                $v = array_merge($v,$tmp);
                unset($v['head_data']);
            }
            $lists = json_encode($lists);
            $this->setData('recent:' . $page, $lists);
        }
        $data['page'] = (int)$page;
        $data['size'] = $this->size;
        $data['lists'] = json_decode($lists, true);
        return $data;
    }

    //使用string结构
    public function info($request)
    {
        $id = $request->input('id', 0);
        if ($id) {
            $where[] = ['id', '=', $id];
        } else {
            $code = $request->input('code', 0);
            $where[] = ['code', '=', $code];
        }
        $info = $this->pageModel->where($where)->first();
        $info['head_schema'] = json_decode($info['head_schema'], true);
        $info['head_data'] = json_decode($info['head_data'], true);
        $info['center_schema'] = json_decode($info['center_schema'], true);
        $info['center_data'] = json_decode($info['center_data'], true);
        $info['bottom_schema'] = json_decode($info['bottom_schema'], true);
        $info['bottom_data'] = json_decode($info['bottom_data'], true);
        $uid = $request->user()->id ?? 0;
        if ($info['user_id'] != $uid) {
            $browse = $this->browseModel->where(['user_id' => $uid, 'page_id' => $info['id']])->first();
            if (!$browse) {
                $insert = [
                    'user_id' => $uid,
                    'page_id' => $info['id'],
                    'click'   => 1
                ];
                $this->browseModel->insertGetId($insert);
            } else {
                $this->browseModel->whereKey($browse['id'])->increment('click');
            }
        }
        return $info;
    }
}
