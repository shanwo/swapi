<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 * Tips: 链接模块
 */

namespace App\Http\Controllers;

use App\Services\LinkService;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    protected $linkService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->linkService = new LinkService();
    }

    public function lists()
    {
        $this->validate([
            'type' => 'required|integer|between:1,10',
        ], [
            'type' => '链接类型',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->linkService->lists($this->request);
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
