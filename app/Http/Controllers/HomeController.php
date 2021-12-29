<?php
/**
 * User: Powerless
 * Date: 21/07/01
 * Blog: https://wzxaini9.cn
 * Tips: 首页模块
 */

namespace App\Http\Controllers;

use App\Services\AdService;
use App\Services\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $adService;
    protected $homeService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->adService = new AdService();
        $this->homeService = new HomeService();
    }

    public function hot()
    {
        $this->validate([
            'page' => 'required|integer|gt:0',
        ], [
            'page' => '页码',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->homeService->hot($this->request);
        $data['ad'] = $this->adService->info($this->request,1);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function recent()
    {
        $this->validate([
            'page' => 'required|integer|gt:0',
        ], [
            'page' => '页码',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->homeService->recent($this->request);
        $data['ad'] = $this->adService->info($this->request,2);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'code' => 'required|string|between:4,16',
        ], [
            'code' => '闪码',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->homeService->info($this->request);
        $data['ad'] = $this->adService->info($this->request,3);
        if(!empty($data['msg'])){
            $this->msg = $data['msg'];
            return $this->returnApi();
        }
        $this->code = 200;
        return $this->returnApi($data);
    }
}
