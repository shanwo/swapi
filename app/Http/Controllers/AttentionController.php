<?php
/**
 * User: Powerless
 * Date: 2021/7/10
 * Blog: https://wzxaini9.cn
 * Tips: 关注模块
 */

namespace App\Http\Controllers;

use App\Services\AttentionService;
use Illuminate\Http\Request;

class AttentionController extends Controller
{
    protected $attentionService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->attentionService = new AttentionService();
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
        $data = $this->attentionService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function status()
    {
        $this->validate([
            'page_id' => 'required|integer|gt:0|exists:sw_page,id',
            'status'  => 'required|integer|between:0,3',
        ], [
            'page_id' => '页面ID',
            'status'  => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->attentionService->status($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
