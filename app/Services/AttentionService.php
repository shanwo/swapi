<?php
/**
 * User: Powerless
 * Date: 2021/7/10
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\AttentionModel;

class AttentionService extends Service
{
    protected $attentionModel;

    public function __construct()
    {
        parent::__construct();
        $this->attentionModel = new AttentionModel();
    }

    public function lists($request)
    {
        $page = $request->input('page', $this->page);
        $start = ($page - 1) * $this->size;
        $stop = $start + $this->size - 1;
        $pid = $request->input('page_id', 0);
        $lists = [];
        if ($pid) {
            $pages = $this->redis::LRANGE('page_attentions:' . $pid, $start, $stop);
            if (!$start && !$pages) $this->setPage($pid);
            $total = $this->redis::LLEN('page_attentions:' . $pid);
            foreach ($pages as $k => $v) {
                $info = $this->redis::GET('list_pages:' . $v);
                $info = json_decode($info, true);
                if ($info) {
                    $lists[] = ['id' => $v, 'avatar' => $info['avatar'], 'nickname' => $info['nickname']];
                }
            }
        } else {
            $uid = $request->user()->id;
            $users = $this->redis::LRANGE('user_attentions:' . $uid, $start, $stop);
            if (!$start && !$users) $this->setUser($uid);
            $total = $this->redis::LLEN('user_attentions:' . $uid);
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

    public function status($request)
    {
        $uid = $request->user()->id;
        $pid = $request->input('page_id');
        $status = $request->input('status');
        if ($status) {
            $this->redis::LPUSH('user_attentions:' . $uid, $pid);
            $this->redis::LPUSH('page_attentions:' . $pid, $uid);
        } else {
            $this->redis::LREM('user_attentions:' . $uid, 0, $pid);
            $this->redis::LREM('page_attentions:' . $pid, 0, $uid);
        }
    }

    private function setPage($pid)
    {
        if ($this->redis::GET('set_page_attentions')) return ;
        $where[] = ['status', '<>', 0];
        $where[] = ['page_id', '=', $pid];
        $all = $this->attentionModel->select('user_id')->where($where)->get()->toArray();
        foreach ($all as $k => $v) {
            $this->redis::LPUSH('page_attentions:' . $pid, $v['user_id']);
        }
        $this->redis::SETEX('set_page_attentions', 15, date('Y-m-d H:i:s'));
    }

    private function setUser($uid)
    {
        if ($this->redis::GET('set_user_attentions')) return ;
        $where[] = ['status', '<>', 0];
        $where[] = ['user_id', '=', $uid];
        $all = $this->attentionModel->select('page_id')->where($where)->get()->toArray();
        foreach ($all as $k => $v) {
            $this->redis::LPUSH('user_attentions:' . $uid, $v['page_id']);
        }
        $this->redis::SETEX('set_user_attentions', 15, date('Y-m-d H:i:s'));
    }
}
