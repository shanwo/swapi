<?php
/**
 * User: Powerless
 * Date: 2021/7/11
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\BrowseModel;

class BrowseService extends Service
{
    protected $browseModel;

    public function __construct()
    {
        parent::__construct();
        $this->browseModel = new BrowseModel();
    }

    public function lists($request)
    {
        $page = $request->input('page', $this->page);
        $start = ($page - 1) * $this->size;
        $stop = $start + $this->size - 1;
        $pid = $request->input('page_id', 0);
        $lists = [];
        if ($pid) {
            $pages = $this->redis::LRANGE('page_browses:' . $pid, $start, $stop);
            if (!$start && !$pages) $this->setPage($pid);
            $total = $this->redis::LLEN('page_browses:' . $pid);
            foreach ($pages as $k => $v) {
                $info = $this->redis::GET('list_pages:' . $v);
                $info = json_decode($info, true);
                if ($info) {
                    $lists[] = ['id' => $v, 'avatar' => $info['avatar'], 'nickname' => $info['nickname']];
                }
            }
        } else {
            $uid = $request->user()->id;
            $users = $this->redis::LRANGE('user_browses:' . $uid, $start, $stop);
            if (!$start && !$users) $this->setUser($uid);
            $total = $this->redis::LLEN('user_browses:' . $uid);
            foreach ($users as $k => $v) {
                $info = $this->redis::GET('user_infos:' . $v);
                if ($info) {
                    $info = json_decode($info, true);
                    $lists[] = ['id' => $info['id'], 'avatar' => $info['avatar'], 'nickname' => $info['nickname']];
                }
            }
        }
        return [
            'total' => $total,
            'page'  => $page,
            'size'  => $this->size,
            'lists' => $lists
        ];
    }

    private function setPage($pid)
    {
        if ($this->redis::GET('set_page_browses')) return;
        $where[] = ['page_id', '=', $pid];
        $all = $this->browseModel->select('user_id')->where($where)->get()->toArray();
        foreach ($all as $k => $v) {
            $this->redis::LPUSH('page_browses:' . $pid, $v['user_id']);
        }
        $this->redis::SETEX('set_page_browses', 15, date('Y-m-d H:i:s'));
    }

    private function setUser($uid)
    {
        if ($this->redis::GET('set_user_browses')) return;
        $where[] = ['user_id', '=', $uid];
        $all = $this->browseModel->select('page_id')->where($where)->get()->toArray();
        foreach ($all as $k => $v) {
            $this->redis::LPUSH('user_browses:' . $uid, $v['page_id']);
        }
        $this->redis::SETEX('set_user_browses', 15, date('Y-m-d H:i:s'));
    }
}
