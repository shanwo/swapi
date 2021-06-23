<?php
/**
 * User: Powerless
 * Date: 21/06/23
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\AreaService;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    protected $areaService;
    protected $request;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->request = $request;
        $this->areaService = new AreaService();
    }

    public function lists()
    {
        $this->validate([
            'level' => 'nullable|integer|between:1,3',
            'pid'   => 'nullable|integer|between:0,3750',
        ], [
            'level' => '层级',
            'pid'   => '父ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->areaService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
