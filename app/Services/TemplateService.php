<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\SortModel;
use App\Models\TemplateModel;

class TemplateService extends Service
{
    protected $sortModel;
    protected $templateModel;

    public function __construct()
    {
        parent::__construct();
        $this->sortModel = new SortModel();
        $this->templateModel = new TemplateModel();
    }

    public function lists($request, $sids = [])
    {
        $data = [];
        $sort = $request->input('sort_id', 0);
        if (!$sort) {
            foreach ($sids as $k => $v) {
                $tmp = $this->redis::HGETALL('template:' . $v);
                $data = array_merge($data, $tmp);
            }
        } else {
            $data = $this->redis::HGETALL('template:' . $sort);
        }
        if (!$data) {
            $this->setCache();
            $data = $this->redis::HGETALL('template:' . $sort);
        }
        foreach ($data as $k => &$v) {
            $v = json_decode($v, true);
            if ($sort && !in_array($v['sort_id'], $sids)) {
                unset($v);
            }
        }
        return array_values($data);
    }

    private function setCache()
    {
        $data = $this->templateModel->select('id', 'sort_id', 'name', 'preview', 'type', 'created_at')->get()->toArray();
        foreach ($data as $k => $v) {
            $this->redis::HSET('template:' . $v['sort_id'], $v['id'], json_encode($v, JSON_UNESCAPED_UNICODE));
        }
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
