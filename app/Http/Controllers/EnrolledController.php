<?php

namespace App\Http\Controllers;

use App\Services\UserAttributeService;
use Illuminate\Http\Request;

class EnrolledController extends Controller
{
    protected $userAttributeService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userAttributeService = new UserAttributeService();
    }

    public function index()
    {
        $this->userAttributeService->add($this->request);
        $this->code = 200;
        return $this->returnApi();
    }
}
