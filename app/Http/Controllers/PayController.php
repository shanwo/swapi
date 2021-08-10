<?php
/**
 * User: Powerless
 * Date: 21/08/09
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\PayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayController extends Controller
{
    protected $payService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->payService = new PayService();
    }

    public function lists()
    {
        $this->validate([
        ], [
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = Auth::user();
        dump($id);die;
        $data = $this->payService->lists($this->uid);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function pay()
    {
        $this->validate([
        ], [
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->payService->pay();
        $this->code = 200;
        return $this->returnApi($data);
    }
}
