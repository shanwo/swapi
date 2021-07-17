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

    public function lists($request,$sids=[])
    {
        $sort = $request->input('sort_id', 0);
        $lists = $this->redis::GET('template:' . $sort);
        if (empty($lists)) {
            if($sort) $sids = [$sort];
            $lists = $this->templateModel->select('id', 'sort_id', 'name', 'preview', 'type', 'created_at')
                ->whereIn('sort_id', $sids)->orderBy('created_at', 'DESC')->get()->toJson();
            $this->setData('template:' . $sort, $lists);
        }
        return json_decode($lists, true);
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
