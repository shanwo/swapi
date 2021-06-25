<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\RecommendService;
use Illuminate\Http\Request;

class RecommendController extends Controller
{
    protected $recommendService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->recommendService = new RecommendService();
    }

    public function lists()
    {
        $this->validate([], []);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->recommendService->lists($this->uid);
        return $this->returnApi($data);
    }
}
