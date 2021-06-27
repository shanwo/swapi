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

    public function lists()
    {
        $lists = $this->redis::GET('template');
        if (empty($lists)) {
            $lists = $this->templateModel->select('category','name','image','type','created_at')->get()->toJson();
            $this->setData('template', $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
    public function info($id)
    {
        $info = $this->templateModel->whereKey($id)->first();
        $info['top_config'] = json_decode($info['top_config'], true);
        $info['center_config'] = json_decode($info['center_config'], true);
        $info['bottom_config'] = json_decode($info['bottom_config'], true);
        return $info;
    }
}
