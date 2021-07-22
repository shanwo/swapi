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

    public function lists($uid)
    {
        $data = $this->redis::HGETALL('page:' . $uid);
        if (empty($data)) {
            $where[] = ['user_id', '=', $uid];
            $where[] = ['is_delete', '=', 0];
            $lists = $this->pageModel->select('id', 'follow', 'is_open', 'head_config', 'created_at')->where($where)->get()->toArray();
            foreach ($lists as $k => &$v) {
                $config = json_decode($v['head_config'], true);
                $v['avatar'] = $config['avatar']['image'];
                $v['badge'] = $config['badge']['image'];
                $v['pendant'] = $config['pendant']['image'];
                $v['nickname'] = $config['nickname']['content'];
                $v['signature'] = $config['signature']['content'];
                unset($v['head_config']);
                $this->redis::HSET('page:' . $uid, $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
            }
            $data = $this->redis::HGETALL('page:' . $uid);
        }
        foreach ($data as $k => &$v) {
            $v = json_decode($v, true);
        }
        return ['lists' => array_values($data)];
    }

    public function info($id)
    {
        $info = $this->pageModel->whereKey($id)->first();
        $info['head_config'] = json_decode($info['head_config'], true);
        $info['center_config'] = json_decode($info['center_config'], true);
        $info['bottom_config'] = json_decode($info['bottom_config'], true);
        return $info;
    }

    public function initial($tid, $user)
    {
        $incLcIds = $incLbIds = [];
        $template = $this->templateModel->whereKey($tid)->first();
        $head = json_decode($template['head_config'], true);
        $head['avatar']['image'] = $user['avatar'];
        $head['nickname']['content'] = $user['nickname'];
        $center = json_decode($template['center_config'], true);
        foreach ($center as $k => $v) {
            if (is_array($v)) {
                $incLcIds = array_column($v, 'id');
                break;
            }
        }
        $bottom = json_decode($template['bottom_config'], true);
        foreach ($bottom as $k => $v) {
            if (is_array($v)) {
                $incLbIds = array_column($v, 'id');
                break;
            }
        }
        $insert = [
            'user_id'       => $user['id'],
            'template_id'   => $tid,
            'background'    => $template['background'],
            'head_config'   => json_encode($head, JSON_UNESCAPED_UNICODE),
            'center_config' => $template['center_config'],
            'bottom_config' => $template['bottom_config']
        ];
        $this->redis::HDEL('page:' . $user['id']);
        $this->linkModel->whereIn('id', $incLcIds)->increment('number');
        $this->linkModel->whereIn('id', $incLbIds)->increment('number');
        $this->userModel->whereKey($user['id'])->increment('pages');
        return $this->pageModel->insertGetId($insert);
    }

    public function add($request, $uid)
    {
        $incLcIds = $incLbIds = [];
        $tid = $request->input('template_id');
        $template = $this->templateModel->whereKey($tid)->first();
        $head = json_decode($template['head_config'], true);
        $headConfig = $request->input('head_config');
        $head['avatar']['image'] = $headConfig['avatar'];
        $head['nickname']['content'] = $headConfig['nickname'];
        $head['signature']['content'] = $headConfig['signature'] ?? '这个人还没写介绍呢~';
        $head['badge']['image'] = $headConfig['badge'] ?? '';
        $head['pendant']['image'] = $headConfig['pendant'] ?? '';
        $center = json_decode($template['center_config'], true);
        foreach ($center as $k => $v) {
            if (is_array($v)) {
                $center[$k] = $request->input('center_config');
                $incLcIds = array_column($center[$k],'id');
                break;
            }
        }
        $insert = [
            'user_id'       => $uid,
            'template_id'   => $tid,
            'background'    => $template['background'],
            'head_config'   => json_encode($head, JSON_UNESCAPED_UNICODE),
            'center_config' => json_encode($center, JSON_UNESCAPED_UNICODE),
        ];
        $bottomConfig = $request->input('bottom_config', []);
        if ($bottomConfig) {
            $bottom = json_decode($template['bottom_config'], true);
            foreach ($bottom as $k => $v) {
                if (is_array($v)) {
                    $bottom[$k] = $bottomConfig;
                    $incLbIds = array_column($bottom[$k],'id');
                    break;
                }
            }
            $insert['bottom_config'] = json_encode($bottom, JSON_UNESCAPED_UNICODE);
        }
        $this->redis::HDEL('page:' . $uid);
        $this->linkModel->whereIn('id',$incLcIds)->increment('number');
        $this->linkModel->whereIn('id',$incLbIds)->increment('number');
        $this->userModel->whereKey($uid)->increment('pages');
        return $this->pageModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $update = $incLcIds = $incLbIds = $decLcIds = $decLbIds = [];
        $id = $request->input('id');
        $info = $this->info($id);
        $headConfig = $request->input('head_config', []);
        if ($headConfig) {
            $head = $info['head_config'];
            $head['avatar']['image'] = $headConfig['avatar'];
            $head['nickname']['content'] = $headConfig['nickname'];
            $head['signature']['content'] = $headConfig['signature'] ?? '这个人还没写介绍呢~';
            $head['badge']['image'] = $headConfig['badge'] ?? '';
            $head['pendant']['image'] = $headConfig['pendant'] ?? '';
            $update['head_config'] = json_encode($head, JSON_UNESCAPED_UNICODE);
        }
        $centerConfig = $request->input('center_config', []);
        if ($centerConfig) {
            $center = $info['center_config'];
            foreach ($center as $k => $v) {
                if (is_array($v)) {
                    $center[$k] = $centerConfig;
                    $incLcIds = array_column($center[$k],'id');
                    $decLcIds = array_column($center[$k],'id');
                    break;
                }
            }
            $update['center_config'] = json_encode($center, JSON_UNESCAPED_UNICODE);
        }
        $bottomConfig = $request->input('bottom_config', []);
        if ($bottomConfig) {
            $bottom = $info['bottom_config'];
            foreach ($bottom as $k => $v) {
                if (is_array($v)) {
                    $bottom[$k] = $bottomConfig;
                    $incLbIds = array_column($bottom[$k],'id');
                    $decLbIds = array_column($bottom[$k],'id');
                    break;
                }
            }
            $update['bottom_config'] = json_encode($bottom, JSON_UNESCAPED_UNICODE);
        }
        $this->redis::HDEL('page:' . $info['user_id']);
        $this->linkModel->whereIn('id',$incLcIds)->increment('number');
        $this->linkModel->whereIn('id',$incLbIds)->increment('number');
        $this->linkModel->whereIn('id',$decLcIds)->decrement('number');
        $this->linkModel->whereIn('id',$decLbIds)->decrement('number');
        return $this->pageModel->whereKey($id)->update($update);
    }

    public function status($request, $uid)
    {
        $where[] = ['user_id', '=', $uid];
        $update = [];
        $id = $request->input('id');
        $open = $request->input('is_open', '');
        if ($open) $update['is_open'] = $open;
        $delete = $request->input('is_delete', '');
        if ($delete){
            $update['is_delete'] = $delete;
            $this->userModel->whereKey($uid)->decrement('pages');
        }
        $this->redis::HDEL('page:' . $uid);
        return $this->pageModel->whereKey($id)->where($where)->update($update);
    }
}
