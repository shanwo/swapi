<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\UploadService;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    protected $uploadService;
    protected $request;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->request = $request;
        $this->uploadService = new UploadService();
    }

    public function file()
    {
        $this->validate([
            'file'      =>'required|file|max:102400',
            'title'     =>'nullable|string|max:64',
            'desc'      =>'nullable|string|max:255',
            'tag'       =>'nullable|string|max:64',
        ]);
        $data = $this->uploadService->update($this->request);
        return $this->returnApi($data);
    }
}
