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
            'pid'  => 'filled|integer|gt:0|exists:sw_page,id',
            'page' => 'required|integer|gt:0',
        ], [
            'pid'  => '页面ID',
            'page' => '页码',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->attentionService->lists($this->request, $this->uid);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function add()
    {
        $this->validate([
            'pid' => 'required|integer|gt:0|exists:sw_page,id',
        ], [
            'pid' => '页面ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->attentionService->add($this->request, $this->uid);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function status()
    {
        $this->validate([
            'id'     => 'required|integer|gt:0|exists:sw_attention,id',
            'status' => 'required|integer|between:0,3',
        ], [
            'id'     => 'ID',
            'status' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->attentionService->status($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
