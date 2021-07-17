<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 * Tips: 模板模块
 */

namespace App\Http\Controllers;

use App\Services\SortService;
use App\Services\TemplateService;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    protected $sortService;
    protected $templateService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->sortService = new SortService();
        $this->templateService = new TemplateService();
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
        $data['sort'] = $this->sortService->lists(3);
        $data['lists'] = $this->templateService->lists($this->request,array_column($data['sort'], 'id'));
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_template,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = $this->request->input('id');
        $data = $this->templateService->info($id);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
