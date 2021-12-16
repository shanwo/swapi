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

class PageService extends Service
{
    protected $pageModel;
    protected $linkModel;
    protected $templateModel;

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new PageModel();
        $this->linkModel = new LinkModel();
        $this->templateModel = new TemplateModel();
    }

    public function lists($request)
    {
        $user = $request->user();
        $data = $this->redis::HGETALL('user_pages:' . $user->id);
        if (empty($data)) {
            $where[] = ['user_id', '=', $user->id];
            $data = $this->pageModel->select('id', 'user_id', 'follow', 'is_open', 'head_data', 'created_at')->where($where)->get()->toArray();
            foreach ($data as $k => &$v) {
                $tmp = json_decode($v['head_data'], true);
                $v = array_merge($v, $tmp);
                unset($v['head_data']);
                $this->redis::HSET('user_pages:' . $user->id, $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
            }
        } else {
            foreach ($data as $k => &$v) {
                $v = json_decode($v, true);
            }
        }
        $edit = Service::PAGE_MIN;
        if ($user->type == Service::USER_VIP && in_array($user->vip_type, [1, 2, 3, 4])) {
            $edit = (Service::PAGE_MAX - $user->vip_type) + Service::PAGE_MIN;
        }
        foreach ($data as $k => &$v) {
            $v['edit'] = 0;
            if ($edit) {
                $v['edit'] = 1;
                $edit--;
            }
        }
        return ['lists' => array_values($data)];
    }

    public function info($id)
    {
        $info = $this->redis::GET('info_pages:' . $id);
        $info = json_decode($info, true);
        if (!$info) {
            $info = $this->pageModel->whereKey($id)->first();
            if (!$info) return false;
            $info['head_schema'] = json_decode($info['head_schema'], true);
            $info['head_data'] = json_decode($info['head_data'], true);
            $info['center_schema'] = json_decode($info['center_schema'], true);
            $info['center_data'] = json_decode($info['center_data'], true);
            $info['bottom_schema'] = json_decode($info['bottom_schema'], true);
            $info['bottom_data'] = json_decode($info['bottom_data'], true);
            $this->redis::SET('info_pages:' . $id, json_encode($info, JSON_UNESCAPED_UNICODE));
            $head = $info['head_data'];
            $head['id'] = $id;
            $this->redis::SET('list_pages:' . $id, json_encode($head, JSON_UNESCAPED_UNICODE));
        }
        return $info;
    }

    public function initial($tid, $user)
    {
        $template = $this->templateModel->whereKey($tid)->first();
        if (!$template) return false;
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
        return $this->pageModel->insertGetId($insert);
    }

    public function add($request)
    {
        $uid = $request->user()->id;
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
            'background'    => $request->input('background', $template['background']),
            'head_schema'   => $template['head_schema'],
            'head_data'     => json_encode($head, JSON_UNESCAPED_UNICODE),
            'center_schema' => $template['center_schema'],
            'center_data'   => json_encode($center, JSON_UNESCAPED_UNICODE),        //todo 链接替换
            'bottom_schema' => $template['bottom_schema'],
            'bottom_data'   => json_encode($bottom, JSON_UNESCAPED_UNICODE),
        ];
        $this->linkModel->whereIn('id', $incLcIds)->increment('number');
        $this->linkModel->whereIn('id', $incLbIds)->increment('number');
        $id = $this->pageModel->insertGetId($insert);
        $data = [
            'id'         => $id,
            'follow'     => 0,
            'is_open'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->redis::HSET('user_pages:' . $uid, $id, json_encode(array_merge($data, $head), JSON_UNESCAPED_UNICODE));
        $head['id'] = $id;
        return $this->redis::SET('list_pages:' . $id, json_encode($head, JSON_UNESCAPED_UNICODE));

    }

    public function edit($request)
    {
        $incLcIds = $incLbIds = $decLcIds = $decLbIds = $head = [];
        $id = $request->input('id');
        $info = $this->info($id);
        $tid = $request->input('template_id');
        $template = $this->templateModel->whereKey($tid)->first();
        $update = [
            'template_id'   => $tid,
            'background'    => $request->input('background', $template['background']),
            'head_schema'   => $template['head_schema'],
            'center_schema' => $template['center_schema'],
            'bottom_schema' => $template['bottom_schema'],
        ];
        $headData = $request->input('head_data', []);
        if ($headData) {
            $head['avatar'] = $headData['avatar'];
            $head['nickname'] = $headData['nickname'];
            $head['signature'] = $headData['signature'] ?? '';
            $head['badge'] = $headData['badge'] ?? '';
            $head['pendant'] = $headData['pendant'] ?? '';
            $update['head_data'] = json_encode($head, JSON_UNESCAPED_UNICODE);
            $data = [
                'id'         => $id,
                'follow'     => $info['follow'],
                'is_open'    => $info['is_open'],
                'created_at' => $info['created_at'],
            ];
            $this->redis::HSET('user_pages:' . $info['user_id'], $id, json_encode(array_merge($data, $head), JSON_UNESCAPED_UNICODE));
            $head['id'] = $id;
            $this->redis::SET('list_pages:' . $id, json_encode($head, JSON_UNESCAPED_UNICODE));
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
        $this->linkModel->whereIn('id', $incLcIds)->increment('number');
        $this->linkModel->whereIn('id', $incLbIds)->increment('number');
        $this->linkModel->whereIn('id', $decLcIds)->decrement('number');
        $this->linkModel->whereIn('id', $decLbIds)->decrement('number');
        $this->redis::DEL('info_pages:' . $id);
        $where[] = ['user_id', '=', $info['user_id']];
        return $this->pageModel->whereKey($id)->where($where)->update($update);
    }

    public function status($request)
    {
        $uid = $request->user()->id;
        $where[] = ['user_id', '=', $uid];
        $update = [];
        $id = $request->input('id');
        $open = $request->input('is_open');
        if (is_numeric($open)) {
            $update['is_open'] = $open;
            if ($open) {
//                $info = $this->info($id);
//                $this->redis::SADD('recent', $id);
//                $this->redis::ZADD('hot', $info['browse'] + $info['sort'], $id);
            } else {
                $this->redis::SREM('recent', $id);
                $this->redis::ZREM('hot', $id);
            }
        }
        $status = $request->input('status');
        if ($status) {
            $this->redis::ZREM('hot', $id);
            $this->redis::SREM('recent', $id);
            $this->redis::DEL('list_pages:' . $id);
            $this->redis::DEL('info_pages:' . $id);
            $this->redis::HDEL('user_pages:' . $uid, $id);
            $update['status'] = $status;
        }
        return $this->pageModel->whereKey($id)->where($where)->update($update);
    }
}
