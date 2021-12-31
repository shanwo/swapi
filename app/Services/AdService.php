<?php
/**
 * User: Powerless
 * Date: 2021/9/12
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\AdModel;

class AdService extends Service
{
    protected $adModel;

    public function __construct()
    {
        parent::__construct();
        $this->adModel = new AdModel();
    }

    /**
     * @param $request
     * @param $location 1:热门浏览2:最近更新3闪页详情4我的闪页5模板列表6链接列表7教程列表8关注记录9浏览记录10支付
     * @return array
     */
    public function info($request, $location)
    {
        return [];
    }
}
