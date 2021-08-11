<?php
/**
 * User: Powerless
 * Date: 2021/7/11
 * Blog: https://wzxaini9.cn
 * Tips: 浏览记录
 */

namespace App\Http\Controllers;

use App\Services\BrowseService;
use Illuminate\Http\Request;

class BrowseController extends Controller
{
    protected $browseService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->browseService = new BrowseService();
    }

    public function lists()
    {
        $this->validate([
            'page_id' => 'filled|integer|gt:0|exists:sw_page,id',
            'page'    => 'required|integer|gt:0',
        ], [
            'page_id' => '页面ID',
            'page'    => '页码',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->browseService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
