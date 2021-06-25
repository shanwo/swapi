<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
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
        $this->validate([], []);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->linkService->lists($this->uid);
        return $this->returnApi($data);
    }
}
