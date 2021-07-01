<?php
/**
 * User: Powerless
 * Date: 21/07/01
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $homeService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->homeService = new HomeService();
    }

    public function hot()
    {
        $this->validate([
        ], [
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->homeService->hot($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function recommend()
    {
        $this->validate([
        ], [
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->homeService->recommend($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
