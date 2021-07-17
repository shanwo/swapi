<?php
/**
 * User: Powerless
 * Date: 2021/7/17
 * Blog: https://wzxaini9.cn
 * Tips: 装饰模块
 */

namespace App\Http\Controllers;

use App\Services\DecorationService;
use App\Services\SortService;
use Illuminate\Http\Request;

class DecorationController extends Controller
{
    protected $sortService;
    protected $decorationService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->sortService = new SortService();
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
        $data['sort'] = $this->sortService->lists(1);
        $data['lists'] = $this->decorationService->lists($this->request,array_column($data['sort'], 'id'));
        $this->code = 200;
        return $this->returnApi($data);
    }
}
