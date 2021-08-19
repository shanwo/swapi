<?php
/**
 * User: Powerless
 * Date: 2021/6/3
 * Blog: https://wzxaini9.cn
 * Tips: 测试模块
 */

namespace App\Http\Controllers;

use App\Services\PageService;
use App\Services\Service;
use App\Services\UserService;
use Illuminate\Http\Request;

class TestController extends Controller
{
    protected $pageService;
    protected $userService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->service = new Service();
        $this->userService = new UserService();
        $this->pageService = new PageService();
    }

    public function initial()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_user,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = $this->request->input('id');
        $user = $this->userService->info($id);
        $data = $this->pageService->initial(1,$user);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function check()
    {
        return $this->returnApi($this->request->all());
    }

    public function info()
    {
        phpinfo();
    }

    public function all()
    {
        $this->validate([
            'dbId' => 'filled|integer|between:0,3',
        ], [
            'dbId' => '库ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->code = 200;
        $db = $this->request->input('dbId', 0);
        $this->service->redis::SELECT($db);
        $number = $this->service->redis::DBSIZE();
        [, $data] = $this->service->redis::SCAN('*', $number);
        return $this->returnApi($data);
    }

    public function image()
    {
        $array = [
            'n'      => 8,
            'format' => 'js',
        ];
        $param = http_build_query($array);
        $url = "https://www.bing.com/HPImageArchive.aspx?" . $param;
        $data = null;
        $api = $this->client->get($url);
        if ($api->getStatusCode() == 200) {
            $body = $api->getBody()->getContents();
            $body = json_decode($body, true);
            $key = array_rand($body['images'], 1);
            $image = $body['images'][$key];
            $data['url'] = 'https://cn.bing.com' . $image['url'];
            $this->code = 200;
        }
        return $this->returnApi($data);
    }
}
