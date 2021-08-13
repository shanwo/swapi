<?php
/**
 * User: Powerless
 * Date: 21/08/09
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\PayService;
use Illuminate\Http\Request;

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
        $data = $this->payService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function send()
    {
        $this->validate([
        ], [
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->payService->send($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function result()
    {
        $this->validate([
        ], [
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->payService->result($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
