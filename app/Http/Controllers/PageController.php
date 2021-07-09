<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\PageService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    protected $pageService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->pageService = new PageService();
    }

    public function lists()
    {
        $this->validate([
        ], [
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->lists($this->uid);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_page,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = $this->request->input('id');
        $data = $this->pageService->info($id);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function add()
    {
        $this->validate([
            'tid'                     => 'required|integer|gt:0|exists:sw_template,id',
            'background'              => 'nullable|url|max:128',
            'head_config'             => 'required|array',
            'head_config.avatar'      => 'required|url|max:128',
            'head_config.nickname'    => 'required|string|between:2,16',
            'head_config.signature'   => 'nullable|string|max:128',
            'head_config.badge'       => 'nullable|url|max:128',
            'head_config.pendant'     => 'nullable|url|max:128',
            'center_config'           => 'required|array|min:1',
            'center_config.*.url'     => 'required|url|max:128',
            'center_config.*.image'   => 'required|url|max:128',
            'center_config.*.content' => 'required|string|max:32',
            'bottom_config'           => 'nullable|array',
            'bottom_config.*.content' => 'required_with:bottom_config|string|max:128',
            'bottom_config.*.type'    => 'required_with:bottom_config|max:8',
            'bottom_config.*.image'   => 'required_with:bottom_config|url|max:128',
        ], [
            'tid'                     => '模板ID',
            'background'              => '背景图',
            'head_config'             => '顶部配置',
            'head_config.avatar'      => '头像',
            'head_config.nickname'    => '昵称',
            'head_config.signature'   => '签名',
            'head_config.badge'       => '徽章',
            'head_config.pendant'     => '挂件',
            'center_config'           => '中部配置',
            'center_config.*.url'     => '按钮地址',
            'center_config.*.image'   => '按钮图像',
            'center_config.*.content' => '按钮内容',
            'bottom_config'           => '底部配置',
            'bottom_config.*.content' => '图标内容',
            'bottom_config.*.type'    => '图标类型',
            'bottom_config.*.image'   => '图标图片',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->add($this->request, $this->uid);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function edit()
    {
        $this->validate([
            'id'                      => 'required|integer|gt:0|exists:sw_page,id',
            'background'              => 'nullable|url|max:128',
            'head_config'             => 'required_without_all:center_config,bottom_config|array',
            'head_config.avatar'      => 'required|url|max:128',
            'head_config.nickname'    => 'required|string|between:2,16',
            'head_config.signature'   => 'nullable|string|max:128',
            'head_config.badge'       => 'nullable|url|max:128',
            'head_config.pendant'     => 'nullable|url|max:128',
            'center_config'           => 'required_without_all:head_config,bottom_config|array|min:1',
            'center_config.*.url'     => 'required|url|max:128',
            'center_config.*.image'   => 'required|url|max:128',
            'center_config.*.content' => 'required|string|max:32',
            'bottom_config'           => 'required_without_all:head_config,center_config|array',
            'bottom_config.*.content' => 'required|string|max:128',
            'bottom_config.*.type'    => 'required|max:8',
            'bottom_config.*.image'   => 'required|url|max:128',
        ], [
            'id'                      => 'ID',
            'background'              => '背景图',
            'head_config'             => '顶部配置',
            'head_config.avatar'      => '头像',
            'head_config.nickname'    => '昵称',
            'head_config.signature'   => '签名',
            'head_config.badge'       => '徽章',
            'head_config.pendant'     => '挂件',
            'center_config'           => '中部配置',
            'center_config.*.url'     => '按钮地址',
            'center_config.*.image'   => '按钮图像',
            'center_config.*.content' => '按钮内容',
            'bottom_config'           => '底部配置',
            'bottom_config.*.content' => '图标内容',
            'bottom_config.*.type'    => '图标类型',
            'bottom_config.*.image'   => '图标图片',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->edit($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function status()
    {
        $this->validate([
            'id'        => 'required|integer|gt:0|exists:sw_page,id',
            'is_open'   => 'required_without:is_delete|integer|between:0,1',
            'is_delete' => 'required_without:is_open|integer|between:0,1',
        ], [
            'id'        => 'ID',
            'is_open'   => '广场',
            'is_delete' => '删除',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->status($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
