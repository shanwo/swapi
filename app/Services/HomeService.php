<?php
/**
 * User: Powerless
 * Date: 21/07/01
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\PageModel;

class HomeService extends Service
{
    protected $pageModel;
    protected $pageService;

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new PageModel();
        $this->pageService = new PageService();
    }

    public function hot($request)
    {
        $page = $request->input('page', $this->page);
        $start = ($page - 1) * $this->size;
        $stop = $start + $this->size - 1;
        $ids = $this->redis::ZREVRANGE('hot', $start, $stop);
        if (!$start && !$ids) $this->setPage();
        $data = [
            'total' => $this->redis::ZCARD('hot'),
            'page'  => $page,
            'size'  => $this->size,
            'lists' => []
        ];
        foreach ($ids as $k => $v) {
            $info = $this->redis::GET('list_pages:' . $v);
            if ($info) $data['lists'][] = json_decode($info, true);
        }
        return $data;
    }

    public function recent($request)
    {
        $page = $request->input('page', $this->page);
        $ids = $this->redis::SRANDMEMBER('recent', $this->size);
        if (!($page - 1) && !$ids) $this->setPage();
        $data = [
            'total' => $this->redis::SCARD('recent'),
            'page'  => $page,
            'size'  => $this->size,
            'lists' => []
        ];
        foreach ($ids as $k => $v) {
            $info = $this->redis::GET('list_pages:' . $v);
            if ($info) $data['lists'][] = json_decode($info, true);
        }
        return $data;
    }

    public function info($request)
    {
        $code = $request->input('code');
        $id = $this->redis::HGET('pages_code', $code);
        $page = $this->redis::GET('info_pages:' . $id);
        $info = json_decode($page, true) ?? [];
        if (!$info) {
            $info = $this->pageService->info($id);
            if (!$info) {
                $this->redis::SETEX('info_pages:' . $id, 60, '{}');
                return ['msg' => '没有找到要访问的页面'];
            }
        }
        if (!$this->redis::GET('click:' . $id . ':' . $request->ip())) {
            $this->redis::ZINCRBY('hot', 1, $id);
            $this->redis::SETEX('click:' . $id . ':' . $request->ip(), 15, date('Y-m-d H:i:s'));
            $uid = $request->user()->id;
            if ($uid) {
                $this->redis::ZINCRBY('user_browses_click:' . $uid, 1, $id);
                $this->redis::ZINCRBY('page_browses_click:' . $id, 1, $uid);
                $this->redis::LREM('user_browses:' . $uid, 0, $id);
                $this->redis::LREM('page_browses:' . $id, 0, $uid);
                $this->redis::LPUSH('user_browses:' . $uid, $id);
                $this->redis::LPUSH('page_browses:' . $id, $uid);
            }
        }
        return $info;
    }

    private function setPage()
    {
        if ($this->redis::GET('set_home')) return;
        $where[] = ['is_delete', '=', Service::DELETE_NO];
        $data = $this->pageModel->select('id', 'user_id', 'follow', 'browse', 'is_open', 'head_data')->where($where)->get()->toArray();
        foreach ($data as $k => &$v) {
            $tmp = json_decode($v['head_data'], true);
            $v = array_merge($v, $tmp);
            unset($v['head_data']);
            $this->redis::HSET('user_pages:' . $v['user_id'], $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
        }
        $this->redis::SETEX('set_home', 15, date('Y-m-d H:i:s'));
    }
}
