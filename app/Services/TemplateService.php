<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\TemplateModel;

class TemplateService extends Service
{
    protected $templateModel;

    public function __construct()
    {
        parent::__construct();
        $this->templateModel = new TemplateModel();
    }

    public function lists($request, $sids = [])
    {
        $sort = $request->input('sort_id', 0);
        $data = $this->hGetCache('template', $sort, $sids);
        if (!$data) {
            $where[] = ['is_delete', '=', 0];
            $data = $this->templateModel->select('id', 'sort_id', 'name', 'preview', 'type', 'created_at')->where($where)->get()->toArray();
            $this->hSetCache('template', $data);
            $data = $this->hGetCache('template', $sort, $sids);
        }
        foreach ($data as $k => &$v) {
            $v = json_decode($v, true);
        }
        return array_values($data);
    }

    public function info($id)
    {
        $info = $this->templateModel->whereKey($id)->first();
        $info['head_config'] = json_decode($info['head_config'], true);
        $info['center_config'] = json_decode($info['center_config'], true);
        $info['bottom_config'] = json_decode($info['bottom_config'], true);
        return $info;
    }
}
