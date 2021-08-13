<?php
/**
 * User: Powerless
 * Date: 21/08/09
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

class PayService extends Service
{
    protected $payLists;
    protected $payment;

    public function __construct()
    {
        parent::__construct();
        $this->payment = $this->configInfo('payment');
    }

    public function lists($request)
    {
        $this->payLists = $this->configInfo('payLists');

    }

    public function send($request)
    {

    }

    public function result($request)
    {

    }
}
