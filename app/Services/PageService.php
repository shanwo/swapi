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
        $lists = $this->redis::GET('page:' . $uid);
        if (empty($lists)) {
            $where[] = ['uid', '=', $uid];
            $where[] = ['is_delete', '=', 0];
            $lists = $this->pageModel->where($where)->select('id', 'name', 'signature', 'follow', 'is_open', 'head_config', 'created_at')->get()->toArray();
            foreach ($lists as $k => &$v) {
                $v['head_config'] = json_decode($v['head_config']);
            }
            $lists = json_encode($lists);
            $this->setData('page:' . $uid, $lists);
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
        $tid = $request->input('tid');
        $template = $this->templateModel->whereKey($tid)->first();
        $head = $request->input('head_config');
        $center = $request->input('center_config');
        $bottom = $request->input('bottom_config',[]);
        $insert = [
            'uid'           => $uid,
            'template_id'   => $tid,
            'background'    => $template['background'],
            'name'          => $request->input('name'),
            'signature'     => $request->input('signature','这个人还没写介绍呢~'),
            'head_config'   => json_encode($head,JSON_UNESCAPED_UNICODE),
            'center_config' => json_encode($center,JSON_UNESCAPED_UNICODE),
            'bottom_config' => json_encode($bottom,JSON_UNESCAPED_UNICODE),
        ];
        return $this->pageModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $info = $this->info($request['id']);
        $tid = $request->input('tid');
        $template = $this->templateModel->whereKey($tid)->first();
        $update = [
            'template_id'   => $tid,
            'background'    => $template['background'],
        ];
        $name = $request->input('name','');
        if($name) $update['head_config'] = $name;
        $signature = $request->input('signature','');
        if($signature) $update['signature'] = $signature;
        $head = $request->input('head_config','');
        if($head) $update['head_config'] = json_encode($head,JSON_UNESCAPED_UNICODE);
        $center = $request->input('center_config');
        if($center) $update['head_config'] = json_encode($center,JSON_UNESCAPED_UNICODE);
        $bottom = $request->input('bottom_config',[]);
        if($bottom) $update['head_config'] = json_encode($bottom,JSON_UNESCAPED_UNICODE);
        return $this->pageModel->whereKey($request['id'])->update($update);
    }

    public function status($request)
    {
        $update = [];
        $open = $request->input('is_open','');
        if($open) $update['is_open'] = $open;
        $delete = $request->input('is_delete','');
        if($delete) $update['is_delete'] = $delete;
        return $this->pageModel->whereKey($request['id'])->update($update);
    }
}
