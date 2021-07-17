<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\PageModel;
use App\Models\TemplateModel;

class PageService extends Service
{
    protected $pageModel;
    protected $templateModel;

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new PageModel();
        $this->templateModel = new TemplateModel();
    }

    public function lists($uid)
    {
        $lists = $this->redis::HGETALL('page:' . $uid);
        if (empty($lists)) {
            $where[] = ['user_id', '=', $uid];
            $where[] = ['is_delete', '=', 0];
            $lists = $this->pageModel->where($where)->select('id', 'follow', 'is_open', 'head_config', 'created_at')->get()->toArray();
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
            $lists = json_encode($lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }

    public function info($id)
    {
        $info = $this->pageModel->whereKey($id)->first();
        $info['head_config'] = json_decode($info['head_config'], true);
        $info['center_config'] = json_decode($info['center_config'], true);
        $info['bottom_config'] = json_decode($info['bottom_config'], true);
        return $info;
    }

    public function add($request, $uid)
    {
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
        $center['button'] = $request->input('center_config');
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
            $bottom['icon'] = $bottomConfig;
            $insert['bottom_config'] = json_encode($bottom, JSON_UNESCAPED_UNICODE);
        }
        $this->redis::HDEL('page:' . $uid);
        return $this->pageModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $update = [];
        $id = $request->input('id');
        $info = $this->info($id);
        $headConfig = $request->input('head_config', '');
        if ($headConfig) {
            $head = $info['head_config'];
            $head['avatar']['image'] = $headConfig['avatar'];
            $head['nickname']['content'] = $headConfig['nickname'];
            $head['signature']['content'] = $headConfig['signature'] ?? '这个人还没写介绍呢~';
            $head['badge']['image'] = $headConfig['badge'] ?? '';
            $head['pendant']['image'] = $headConfig['pendant'] ?? '';
            $update['head_config'] = json_encode($head, JSON_UNESCAPED_UNICODE);
        }
        $centerConfig = $request->input('center_config', '');
        if ($centerConfig) {
            $center = $info['center_config'];
            $center['button'] = $centerConfig;
            $update['center_config'] = json_encode($center, JSON_UNESCAPED_UNICODE);
        }
        $bottomConfig = $request->input('bottom_config', '');
        if ($bottomConfig) {
            $bottom = $info['bottom_config'];
            $bottom['icon'] = $bottomConfig;
            $update['bottom_config'] = json_encode($bottom, JSON_UNESCAPED_UNICODE);
        }
        $this->redis::HDEL('page:' . $info['user_id']);
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
        if ($delete) $update['is_delete'] = $delete;
        $this->redis::HDEL('page:' . $uid);
        return $this->pageModel->whereKey($id)->where($where)->update($update);
    }
}
