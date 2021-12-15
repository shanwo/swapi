<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 * Tips: 页面模块
 */

namespace App\Http\Controllers;

use App\Services\AdService;
use App\Services\PageService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    protected $adService;
    protected $pageService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->adService = new AdService();
        $this->pageService = new PageService();
    }

    public function lists()
    {
        $this->validate([
//            'page' => 'required|integer|gt:0',
        ], [
//            'page' => '页码',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->lists($this->request);
        $data['ad'] = $this->adService->info($this->request,4);
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
            'template_id'           => 'required|integer|gt:0|exists:sw_template,id',
            'background'            => 'nullable|url|max:128',
            'head_data'             => 'required|array',
            'head_data.avatar'      => 'required|url|max:128',
            'head_data.nickname'    => 'required|string|between:2,16',
            'head_data.signature'   => 'nullable|string|max:128',
            'head_data.badge'       => 'nullable|url|max:128',
            'head_data.pendant'     => 'nullable|url|max:128',
            'center_data'           => 'required|array|max:128',
            'center_data.*.id'      => 'required|integer|gt:0|exists:sw_link,id',
            'center_data.*.url'     => 'required|url|max:128',
            'center_data.*.image'   => 'required|url|max:128',
            'center_data.*.content' => 'required|string|max:32',
            'bottom_data'           => 'nullable|array|max:128',
            'bottom_data.*.id'      => 'required|integer|gt:0|exists:sw_link,id',
            'bottom_data.*.content' => 'required_with:bottom_data|string|max:128',
            'bottom_data.*.type'    => 'required_with:bottom_data|string|max:8',
            'bottom_data.*.image'   => 'required_with:bottom_data|url|max:128',
        ], [
            'template_id'           => '模板ID',
            'background'            => '背景图',
            'head_data'             => '顶部配置',
            'head_data.avatar'      => '头像',
            'head_data.nickname'    => '昵称',
            'head_data.signature'   => '签名',
            'head_data.badge'       => '徽章',
            'head_data.pendant'     => '挂件',
            'center_data'           => '中部配置',
            'center_data.*.id'      => '按钮ID',
            'center_data.*.url'     => '按钮地址',
            'center_data.*.image'   => '按钮图像',
            'center_data.*.content' => '按钮内容',
            'bottom_data'           => '底部配置',
            'bottom_data.*.id'      => '图标ID',
            'bottom_data.*.content' => '图标内容',
            'bottom_data.*.type'    => '图标类型',
            'bottom_data.*.image'   => '图标图片',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $pages = $this->service->redis::HKEYS('user_pages:' . $this->request->user()->id);
        if($this->request->user()->type != 2 || count($pages) >= $this->request->user()->pages) {
            $this->msg = '闪页数已达上限，开通会员创建更多页面';
            return $this->returnApi();
        }
        $this->code = 200;
        $data = $this->pageService->add($this->request);
        return $this->returnApi($data);
    }

    public function edit()
    {
        $this->validate([
            'id'                    => 'required|integer|gt:0|exists:sw_page,id',
            'background'            => 'nullable|url|max:128',
            'head_data'             => 'required_without_all:center_data,bottom_data|array',
            'head_data.avatar'      => 'required|url|max:128',
            'head_data.nickname'    => 'required|string|between:2,16',
            'head_data.signature'   => 'nullable|string|max:128',
            'head_data.badge'       => 'nullable|url|max:128',
            'head_data.pendant'     => 'nullable|url|max:128',
            'center_data'           => 'required_without_all:head_data,bottom_data|array|max:128',
            'center_data.*.id'      => 'required|integer|gt:0|exists:sw_link,id',
            'center_data.*.url'     => 'required|url|max:128',
            'center_data.*.image'   => 'required|url|max:128',
            'center_data.*.content' => 'required|string|max:32',
            'bottom_data'           => 'required_without_all:head_data,center_data|array|max:128',
            'bottom_data.*.id'      => 'required|integer|gt:0|exists:sw_link,id',
            'bottom_data.*.content' => 'required|string|max:128',
            'bottom_data.*.type'    => 'required|string|max:8',
            'bottom_data.*.image'   => 'required|url|max:128',
        ], [
            'id'                    => 'ID',
            'background'            => '背景图',
            'head_data'             => '顶部配置',
            'head_data.avatar'      => '头像',
            'head_data.nickname'    => '昵称',
            'head_data.signature'   => '签名',
            'head_data.badge'       => '徽章',
            'head_data.pendant'     => '挂件',
            'center_data'           => '中部配置',
            'center_data.*.id'      => '按钮ID',
            'center_data.*.url'     => '按钮地址',
            'center_data.*.image'   => '按钮图像',
            'center_data.*.content' => '按钮内容',
            'bottom_data'           => '底部配置',
            'bottom_data.*.id'      => '图标ID',
            'bottom_data.*.content' => '图标内容',
            'bottom_data.*.type'    => '图标类型',
            'bottom_data.*.image'   => '图标图片',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $pages = $this->service->redis::HKEYS('user_pages:' . $this->request->user()->id);
        sort($pages);
        if($this->request->user()->type != 2 && reset($pages) != $this->request->input('id')) {
            $this->msg = '非默认闪页开通会员后可修改';
            return $this->returnApi();
        }
        $data = $this->pageService->edit($this->request);
        return $this->returnApi($data);
    }

    public function status()
    {
        $this->validate([
            'id'        => 'required|integer|gt:0|exists:sw_page,id',
            'is_open'   => 'required_without:status|integer|between:0,1',
            'status'    => 'required_without:is_open|integer|between:-1,1',
        ], [
            'id'        => 'ID',
            'is_open'   => '广场',
            'status'    => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->status($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
