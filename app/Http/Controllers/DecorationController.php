<?php
/**
 * User: Powerless
 * Date: 2021/7/17
 * Blog: https://wzxaini9.cn
 * Tips: 装饰模块
 */

namespace App\Http\Controllers;

use App\Services\DecorationService;
use Illuminate\Http\Request;

class DecorationController extends Controller
{
    protected $decorationService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->decorationService = new DecorationService();
    }

    public function lists()
    {
        $this->validate([
            'sort_id' => 'nullable|integer|gt:0|exists:sw_sort,id',
        ], [
            'sort_id' => '分类ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->decorationService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
