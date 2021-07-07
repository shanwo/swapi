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

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->uploadService = new UploadService();
    }

    public function file()
    {
        $this->validate([
            'file' => 'required',
        ], [
            'file' => '文件'
        ]);
        $data = [];
        try {
            $data = $this->uploadService->update($this->request);
            $this->code = 200;
            $this->msg = '';
        } catch (\Exception $e) {
            $this->msg = $e->getMessage();
            $this->code = 400;
        }
        return $this->returnApi($data);
    }
}
