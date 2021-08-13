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
        if ($pid) {
            $users = $this->redis::LRANGE('page_attentions:' . $pid, $start, $stop);
        } else {
            $uid = $request->user()->id ?? 0;
            $users = $this->redis::LRANGE('user_attentions:' . $uid, $start, $stop);
        }
        $data = [
            'total' => $this->redis::ZCARD('hot'),
            'page'  => $page,
            'size'  => $this->size
        ];
        foreach ($users as $k => $v) {
            $user = $this->redis::GET('user_infos:' . $v);
            if ($user) {
                $user = json_decode($user, true);
                $data['lists'][] = ['id' => $user['id'], 'avatar' => $user['avatar'], 'nickname' => $user['nickname']];
            }
        }
        return $data;
    }

    public function status($request)
    {
        $uid = $request->user()->id ?? 0;
        $pid = $request->input('page_id');
        $status = $request->input('status');
        if ($status) {
            $this->redis::RPUSH('user_attentions:' . $uid, $pid);
            $this->redis::RPUSH('page_attentions:' . $pid, $uid);
        } else {
            $this->redis::LREM('user_attentions:' . $uid, 0, $pid);
            $this->redis::LREM('page_attentions:' . $pid, 0, $uid);
        }
    }
}
