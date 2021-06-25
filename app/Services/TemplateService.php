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

    public function lists($uid)
    {
        $lists = $this->redis::GET('template:' . $uid);
        if (empty($lists)) {
            $where[] = ['uid', '=', $uid];
            $lists = $this->templateModel->where($where)->get()->toJson();
            $this->setData('template:' . $uid, $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
    public function info($id)
    {
        return $this->templateModel->whereKey($id)->first();
    }
}
