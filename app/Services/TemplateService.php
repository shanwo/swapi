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
            foreach ($data as $k => $v) {
                $this->redis::HSET('template:' . $v['sort_id'], $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
            }
            $data = $this->hGetCache('template', $sort, $sids);
        }
        foreach ($data as $k => &$v) {
            $v = json_decode($v, true);
        }
        return array_values($data);
    }

    public function info($id)
    {
        $info = $this->redis::GET('template_infos:' . $id);
        $info = json_decode($info, true);
        if (!$info) {
            $info = $this->templateModel->whereKey($id)->first();
            $info['head_schema'] = json_decode($info['head_schema'], true);
            $info['head_data'] = json_decode($info['head_data'], true);
            $info['center_schema'] = json_decode($info['center_schema'], true);
            $info['center_data'] = json_decode($info['center_data'], true);
            $info['bottom_schema'] = json_decode($info['bottom_schema'], true);
            $info['bottom_data'] = json_decode($info['bottom_data'], true);
            $this->redis::SET('template_infos:' . $id, json_encode($info, JSON_UNESCAPED_UNICODE));
        }
        return $info;
    }
}
