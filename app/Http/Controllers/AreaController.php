<?php
/**
 * User: Powerless
 * Date: 21/06/23
 * Blog: https://wzxaini9.cn
 * Tips: 地区模块
 */

namespace App\Http\Controllers;

use App\Services\AreaService;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    protected $areaService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->areaService = new AreaService();
    }

    public function lists()
    {
        $this->validate([
            'pid' => 'nullable|integer|between:0,3750|exists:cn_area,pid',
        ], [
            'pid' => '父ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->areaService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
