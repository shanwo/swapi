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
        $data['sort'] = $this->sortService->lists($this->service::SORT_lINT);
        $data['lists'] = $this->linkService->lists($this->request, array_column($data['sort'], 'id'));
        $this->code = 200;
        return $this->returnApi($data);
    }
}
