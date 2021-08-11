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

    //使用zset
    public function lists($request)
    {
        $uid = $request->user()->id ?? 0;
        $page = $request->input('page', $this->page);
        $data = $this->redis::GET('attention:' . $page);
        if (empty($data)) {
            $where[] = ['sa.status', '=', 1];
            $pid = $request->input('page_id', 0);
            if ($pid) {
                $where[] = ['sa.page_id', '=', $pid];
                $lists = $this->attentionModel->from('sw_attention as sa')
                    ->select('sa.id', 'su.nickname', 'su.avatar')
                    ->leftJoin('sw_user as su', 'sa.user_id', '=', 'su.id')
                    ->where($where);
            } else {
                $where[] = ['sa.user_id', '=', $uid];
                $lists = $this->attentionModel->from('sw_attention as sa')
                    ->select('sa.id', 'sp.head_data')
                    ->leftJoin('sw_page as sp', 'sa.page_id', '=', 'sp.id')
                    ->where($where);
            }
            $data['page'] = (int)$page;
            $data['size'] = $this->size;
            $data['total'] = $lists->count();
            $data['lists'] = $lists->forPage($page, $this->size)->orderBy('sa.created_at', 'DESC')->get()->toArray();
            if (!$pid) {
                foreach ($lists as $k => &$v) {
                    $v['head_data'] = json_decode($v['head_data'], true);
                }
            }
            if (!$data['total']) $this->timeOut = 30;
            $this->redis::SETEX('attention:' . $page, $this->timeOut, json_encode($data));
        } else {
            $data = json_decode($data, true);
        }
        return $data;
    }

    public function add($request)
    {
        $pid = $request->input('pid');
        $uid = $request->user()->id ?? 0;
        $insert = [
            'user_id' => $uid,
            'page_id' => $pid,
            'status'  => 1
        ];
        return $this->attentionModel->insertGetId($insert);
    }

    public function status($request)
    {
        $id = $request->input('id');
        $update['status'] = $request->input('status');
        return $this->attentionModel->whereKey($id)->update($update);
    }
}
