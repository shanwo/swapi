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
            $data = $this->redis::ZREVRANGE('page_attentions:' . $pid, $start, $stop);
            if (!$start && !$data) $data = $this->setPage($pid);
            $total = $this->redis::ZCARD('page_attentions:' . $pid);
            foreach ($data as $k => $v) {
                $info = $this->redis::GET('user_infos:' . $v);
                if ($info) {
                    $info = json_decode($info, true);
                    $lists[] = ['id' => $v, 'avatar' => $info['avatar'], 'nickname' => $info['nickname']];
                }
            }
        } else {
            $uid = $request->user()->id;
            $data = $this->redis::ZREVRANGE('user_attentions:' . $uid, $start, $stop);
            if (!$start && !$data) $data = $this->setUser($uid);
            $total = $this->redis::ZCARD('user_attentions:' . $uid);
            foreach ($data as $k => $v) {
                $info = $this->redis::GET('list_pages:' . $v);
                $info = json_decode($info, true);
                if ($info) {
                    $lists[] = ['id' => $v, 'avatar' => $info['avatar'], 'nickname' => $info['nickname']];
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
            $this->redis::ZADD('user_attentions:' . $uid, time(), $pid);
            $this->redis::ZADD('page_attentions:' . $pid, time(), $uid);
        } else {
            $this->redis::ZREM('user_attentions:' . $uid, $pid);
            $this->redis::ZREM('user_attentions:' . $pid, $uid);
        }
    }

    private function setPage($pid)
    {
        if ($this->redis::GET('set_page_attentions')) return;
        $where[] = ['status', '<>', Service::DISABLE];
        $where[] = ['page_id', '=', $pid];
        $all = $this->attentionModel->select('user_id','updated_at')->where($where)->get()->toArray();
        foreach ($all as $k => $v) {
            $this->redis::ZADD('page_attentions:' . $pid, strtotime($v['updated_at']), $v['user_id']);
        }
        $this->redis::SETEX('set_page_attentions', 15, date('Y-m-d H:i:s'));
        return array_column($all,'user_id');
    }

    private function setUser($uid)
    {
        if ($this->redis::GET('set_user_attentions')) return;
        $where[] = ['status', '<>', Service::DISABLE];
        $where[] = ['user_id', '=', $uid];
        $all = $this->attentionModel->select('page_id','updated_at')->where($where)->get()->toArray();
        foreach ($all as $k => $v) {
            $this->redis::ZADD('user_attentions:' . $uid, strtotime($v['updated_at']), $v['page_id']);
        }
        $this->redis::SETEX('set_user_attentions', 15, date('Y-m-d H:i:s'));
        return array_column($all,'page_id');
    }
}
