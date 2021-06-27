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
        $this->validate([], []);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->lists($this->uid);
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
        return $this->returnApi($data);
    }

    public function add()
    {
        
    }
}
