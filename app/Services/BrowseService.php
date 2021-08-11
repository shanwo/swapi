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

    //使用zset
    public function lists($request)
    {
        $uid = $request->user()->id ?? 0;
        $page = $request->input('page', $this->page);
        $lists = $this->redis::GET('browse:' . $page);
        if (empty($lists)) {
            $where[] = ['sa.status', '=', 1];
            $pid = $request->input('page_id', 0);
            if ($pid) {
                $where[] = ['sa.page_id', '=', $pid];
                $lists = $this->browseModel->from('sw_attention as sa')
                    ->select('sa.id', 'su.nickname', 'su.avatar')
                    ->leftJoin('sw_user as su', 'sa.user_id', '=', 'su.id')
                    ->where($where);
            } else {
                $where[] = ['sa.user_id', '=', $uid];
                $lists = $this->browseModel->from('sw_attention as sa')
                    ->select('sa.id', 'sp.head_data')
                    ->leftJoin('sw_page as sp', 'sa.page_id', '=', 'sp.id')
                    ->where($where);
            }
            $data['total'] = $lists->count();
            $lists = $lists->forPage($page, $this->size)->orderBy('sa.created_at', 'DESC')->get()->toArray();
            if (!$pid) {
                foreach ($lists as $k => &$v) {
                    $v['head_data'] = json_decode($v['head_data'], true);
                }
            }
            $lists = json_encode($lists);
            $this->setData('browse:' . $page, $lists);
        }
        $data['page'] = (int)$page;
        $data['size'] = $this->size;
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
}
