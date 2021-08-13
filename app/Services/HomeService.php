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
    protected $pageService;

    public function __construct()
    {
        parent::__construct();
        $this->pageService = new PageService();
    }

    public function hot($request)
    {
        $page = $request->input('page', $this->page);
        $start = ($page - 1) * $this->size;
        $stop = $start + $this->size - 1;
        $ids = $this->redis::ZREVRANGE('hot', $start, $stop);
        $data = [
            'total' => $this->redis::ZCARD('hot'),
            'page'  => $page,
            'size'  => $this->size
        ];
        foreach ($ids as $k => $v) {
            $page = $this->redis::GET('list_pages:' . $v);
            if ($page) $data['lists'][] = json_decode($page, true);
        }
        return $data;
    }

    public function recent($request)
    {
        $page = $request->input('page', $this->page);
        $ids = $this->redis::SRANDMEMBER('recent', $this->size);
        $data = [
            'total' => $this->redis::SCARD('recent'),
            'page'  => $page,
            'size'  => $this->size
        ];
        foreach ($ids as $k => $v) {
            $page = $this->redis::GET('list_pages:' . $v);
            if ($page) $data['lists'][] = json_decode($page, true);
        }
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id', 0);
        $page = $this->redis::GET('info_pages:' . $id);
        $info = json_decode($page, true) ?? [];
        if (!$info) {
            if ($id) {
                $where[] = ['id', '=', $id];
            } else {
                $code = $request->input('code', '');
                $where[] = ['code', '=', $code];
            }
            $where[] = ['is_delete', '=', 0];
            $info = $this->pageService->info($id);
            if (!$info) {
                $info = ['msg' => '页面不存在'];
                $this->redis::SETEX('info_pages:' . $id, 60, json_encode($info, JSON_UNESCAPED_UNICODE));
                return $info;
            }
        }
        if (!$this->redis::GET('click:' . $id . ':' . $request->ip())) {
            $this->redis::ZINCRBY('hot', 1, $id);
            $this->redis::SETEX('click:' . $id . ':' . $request->ip(), 30, date('Y-m-d H:i:s'));
            $uid = $request->user()->id;
            if ($uid) $this->redis::ZINCRBY('user_browses:' . $uid, 1, $id);
        }
        return $info;
    }
}
