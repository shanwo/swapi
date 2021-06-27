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

    public function lists($request)
    {
        $category = $request->input('category');
        $lists = $this->redis::GET('template:'.$category);
        if (empty($lists)) {
            if($category){
                $where[] = ['category', '=', $category];
            }else{
                $where[] = ['hot', '=', 1];
            }
            $lists = $this->templateModel->select('name','preview','type','created_at')->where($where)->get()->toJson();
            $this->setData('template:'.$category, $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
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
