<?php
/**
 * User: Powerless
 * Date: 2021/7/10
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

class AttentionService extends Service
{

    public function __construct()
    {
        parent::__construct();
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
            $total = $this->redis::LLEN('page_attentions:' . $pid);
            foreach ($pages as $k => $v) {
                $info = $this->redis::GET('list_pages:' . $v);
                if ($info) {
                    $info = json_decode($info, true);
                    $data = $info['head_data'];
                    $lists[] = ['id' => $v, 'avatar' => $data['avatar'], 'nickname' => $data['nickname']];
                }
            }
        } else {
            $uid = $request->user()->id;
            $users = $this->redis::LRANGE('user_attentions:' . $uid, $start, $stop);
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
}
