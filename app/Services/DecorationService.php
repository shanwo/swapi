<?php
/**
 * User: Powerless
 * Date: 2021/7/17
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\DecorationModel;

class DecorationService extends Service
{
    protected $decorationModel;

    public function __construct()
    {
        parent::__construct();
        $this->decorationModel = new DecorationModel();
    }

    //使用hash结构
    public function lists($request)
    {
        $type = $request->input('type');
        $lists = $this->redis::GET('decoration:' . $type);
        if (empty($lists)) {
            $where[] = ['type', '=', $type];
            $lists = $this->decorationModel->select('id', 'image', 'created_at')->where($where)->orderBy('order', 'DESC')->get()->toJson();
            $this->setData('decoration:' . $type, $lists);
        }
        $data['lists'] = json_decode($lists, true);
        return $data;
    }
}
