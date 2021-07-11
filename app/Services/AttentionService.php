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

    public function lists($request, $uid)
    {
        $page = $request->input('page', $this->page);
        $lists = $this->redis::GET('attention:' . $page);
        if (empty($lists)) {
            $where[] = ['s_a.status', '=', 1];
            if ($uid) $where[] = ['s_a.user_id', '=', $uid];
            $pid = $request->input('page_id', 0);
            if ($pid) $where[] = ['s_a.page_id', '=', $pid];
            $lists = $this->attentionModel->from('sw_attention as s_a')
                ->select('s_a.id', 's_p.head_config')
                ->leftJoin('sw_page as s_p', 's_a.page_id', '=', 's_p.id')
                ->where($where);
            $data['total'] = $lists->count();
            $lists = $lists->orderBy('sort', 'DESC')->orderBy('s_a.created_at', 'DESC')->get()->toArray();
            foreach ($lists as $k => &$v) {
                $v['head_config'] = json_decode($v['head_config']);
            }
            $lists = json_encode($lists);
            $this->setData('attention:' . $page, $lists);
        }
        $data['page'] = (int)$page;
        $data['size'] = $this->size;
        $data['lists'] = json_decode($lists, true);
        return $data;
    }

    public function add($request, $uid)
    {
        $pid = $request->input('pid');
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
