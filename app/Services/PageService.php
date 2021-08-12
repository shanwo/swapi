<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\LinkModel;
use App\Models\PageModel;
use App\Models\TemplateModel;
use App\Models\UserModel;

class PageService extends Service
{
    protected $pageModel;
    protected $linkModel;
    protected $userModel;
    protected $templateModel;

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new PageModel();
        $this->linkModel = new LinkModel();
        $this->userModel = new UserModel();
        $this->templateModel = new TemplateModel();
    }

    public function lists($request)
    {
        $uid = $request->user()->id ?? 0;
        $data = $this->redis::HGETALL('user_pages:' . $uid);
        if (empty($data)) {
            $where[] = ['user_id', '=', $uid];
            $where[] = ['is_delete', '=', 0];
            $data = $this->pageModel->select('id', 'follow', 'is_open', 'head_data', 'created_at')->where($where)->get()->toArray();
            foreach ($data as $k => &$v) {
                $tmp = json_decode($v['head_data'], true);
                $v = array_merge($v, $tmp);
                unset($v['head_data']);
                $this->redis::HSET('user_pages:' . $uid, $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
            }
        } else {
            foreach ($data as $k => &$v) {
                $v = json_decode($v, true);
            }
        }
        return ['lists' => array_values($data)];
    }

    public function info($id)
    {
        $info = $this->redis::GET('info_pages:' . $id);
        if (!$info) {
            $info = $this->pageModel->whereKey($id)->first();
            if(!$info) return false;
            $info['head_schema'] = json_decode($info['head_schema'], true);
            $info['head_data'] = json_decode($info['head_data'], true);
            $info['center_schema'] = json_decode($info['center_schema'], true);
            $info['center_data'] = json_decode($info['center_data'], true);
            $info['bottom_schema'] = json_decode($info['bottom_schema'], true);
            $info['bottom_data'] = json_decode($info['bottom_data'], true);
            $this->redis::SET('info_pages:' . $id, json_encode($info, JSON_UNESCAPED_UNICODE));
            $this->redis::SET('list_pages:' . $id, json_encode(['head_data' => $info['head_data'], 'head_schema' => $info['head_schema']], JSON_UNESCAPED_UNICODE));
        }
        return $info;
    }

    public function initial($tid, $user)
    {
        $template = $this->templateModel->whereKey($tid)->first();
        $head = json_decode($template['head_data'], true);
        $head['avatar'] = $user['avatar'];
        $head['nickname'] = $user['nickname'];
        $head['signature'] = '这个人还没写介绍呢~';
        $center = json_decode($template['center_data'], true);
        $incLcIds = array_column($center, 'id');
        $bottom = json_decode($template['bottom_data'], true);
        $incLbIds = array_column($bottom, 'id');
        $insert = [
            'user_id'       => $user['id'],
            'template_id'   => $tid,
            'background'    => $template['background'],
            'head_schema'   => $template['head_schema'],
            'head_data'     => json_encode($head, JSON_UNESCAPED_UNICODE),
            'center_schema' => $template['center_schema'],
            'center_data'   => $template['center_data'],
            'bottom_schema' => $template['bottom_schema'],
            'bottom_data'   => $template['bottom_data'],
        ];
        $this->linkModel->whereIn('id', $incLcIds)->increment('number');
        $this->linkModel->whereIn('id', $incLbIds)->increment('number');
        $this->userModel->whereKey($user['id'])->increment('pages');
        return $this->pageModel->insertGetId($insert);
    }

    public function add($request)
    {
        $uid = $request->user()->id ?? 0;
        $tid = $request->input('template_id');
        $template = $this->templateModel->whereKey($tid)->first();
        $head = json_decode($template['head_data'], true);
        $headData = $request->input('head_data');
        $head['avatar'] = $headData['avatar'];
        $head['nickname'] = $headData['nickname'];
        $head['signature'] = $headData['signature'] ?? '';
        $head['badge'] = $headData['badge'] ?? '';
        $head['pendant'] = $headData['pendant'] ?? '';
        $center = $request->input('center_data');
        $incLcIds = array_column($center, 'id');
        $bottom = $request->input('bottom_data');
        $incLbIds = array_column($bottom, 'id');
        $insert = [
            'user_id'       => $uid,
            'template_id'   => $tid,
            'background'    => $template['background'],
            'head_schema'   => $template['head_schema'],
            'head_data'     => json_encode($head, JSON_UNESCAPED_UNICODE),
            'center_schema' => $template['center_schema'],
            'center_data'   => json_encode($center, JSON_UNESCAPED_UNICODE),
            'bottom_schema' => $template['bottom_schema'],
            'bottom_data'   => json_encode($bottom, JSON_UNESCAPED_UNICODE),
        ];
        $this->linkModel->whereIn('id', $incLcIds)->increment('number');
        $this->linkModel->whereIn('id', $incLbIds)->increment('number');
        $this->userModel->whereKey($uid)->increment('pages');
        $id = $this->pageModel->insertGetId($insert);
        return $this->redis::HDEL('user_pages:' . $uid, $id);
    }

    public function edit($request)
    {
        $update = $incLcIds = $incLbIds = $decLcIds = $decLbIds = $head = [];
        $id = $request->input('id');
        $info = $this->info($id);
        $headData = $request->input('head_data', []);
        if ($headData) {
            $head['avatar'] = $headData['avatar'];
            $head['nickname'] = $headData['nickname'];
            $head['signature'] = $headData['signature'] ?? '';
            $head['badge'] = $headData['badge'] ?? '';
            $head['pendant'] = $headData['pendant'] ?? '';
            $update['head_data'] = json_encode($head, JSON_UNESCAPED_UNICODE);
        }
        $centerData = $request->input('center_data', []);
        if ($centerData) {
            $incLcIds = array_column($centerData, 'id');
            $decLcIds = array_column($info['center_data'], 'id');
            $update['center_data'] = json_encode($centerData, JSON_UNESCAPED_UNICODE);
        }
        $bottomData = $request->input('bottom_data', []);
        if ($bottomData) {
            $incLbIds = array_column($bottomData, 'id');
            $decLbIds = array_column($info['bottom_data'], 'id');
            $update['bottom_data'] = json_encode($bottomData, JSON_UNESCAPED_UNICODE);
        }
        $where[] = ['user_id', '=', $info['user_id']];
        $this->pageModel->whereKey($id)->where($where)->update($update);
        $this->linkModel->whereIn('id', $incLcIds)->increment('number');
        $this->linkModel->whereIn('id', $incLbIds)->increment('number');
        $this->linkModel->whereIn('id', $decLcIds)->decrement('number');
        $this->linkModel->whereIn('id', $decLbIds)->decrement('number');
        return $this->redis::HDEL('user_pages:' . $info['user_id'], $id);
    }

    public function status($request)
    {
        $uid = $request->user()->id ?? 0;
        $where[] = ['user_id', '=', $uid];
        $update = [];
        $id = $request->input('id');
        $open = $request->input('is_open');
        if (is_numeric($open)) {
            $update['is_open'] = $open;
            if ($open) {
                $this->redis::SADD('recent', $id);          //TODO::多久内的页面在最近更新
                $this->redis::ZADD('hot', 1, $id);          //TODO::访问量达到多少可进热门
            } else {
                $this->redis::SREM('recent', $id);
                $this->redis::ZREM('hot', $id);
            }
        }
        $delete = $request->input('is_delete');
        if ($delete) {
            $this->redis::ZREM('hot', $id);
            $this->redis::SREM('recent', $id);
            $this->redis::DEL('list_pages:' . $id);
            $this->redis::DEL('info_pages:' . $id);
            $this->redis::HDEL('user_page:' . $uid, $id);
            $this->userModel->whereKey($uid)->decrement('pages');
            $update['is_delete'] = $delete;
        }
        return $this->pageModel->whereKey($id)->where($where)->update($update);
    }
}
