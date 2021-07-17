<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 * Tips: 链接模块
 */

namespace App\Http\Controllers;

use App\Services\LinkService;
use App\Services\SortService;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    protected $sortService;
    protected $linkService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->sortService = new SortService();
        $this->linkService = new LinkService();
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
        $data['sort'] = $this->sortService->lists(4);
        $data['lists'] = $this->linkService->lists($this->request,array_column($data['sort'], 'id'));
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_link,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = $this->request->input('id');
        $data = $this->linkService->info($id);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
