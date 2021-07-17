<?php
/**
 * User: Powerless
 * Date: 2021/7/18
 * Blog: https://wzxaini9.cn
 * Tips: 教程模块
 */

namespace App\Http\Controllers;

use App\Services\SortService;
use App\Services\TutorialsService;
use Illuminate\Http\Request;

class TutorialsController extends Controller
{
    protected $sortService;
    protected $tutorialsService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->sortService = new SortService();
        $this->tutorialsService = new TutorialsService();
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
        $data['sort'] = $this->sortService->lists(2);
        $data['lists'] = $this->tutorialsService->lists($this->request, array_column($data['sort'], 'id'));
        $this->code = 200;
        return $this->returnApi($data);
    }
}
