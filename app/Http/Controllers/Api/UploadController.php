<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers\Api;

use App\Services\UploadService;
use Illuminate\Http\Request;

class UploadController extends ApiController
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
            'file'      =>'required|file|max:102400',
            'title'     =>'nullable|string|max:64',
            'desc'      =>'nullable|string|max:255',
            'tag'       =>'nullable|string|max:64',
        ]);
        $product = env('APP_NAME');
        $file = $this->request->file('file');
        $name = dechex(microtime(true) * 10000);
        $ext = $file->getClientOriginalExtension();//得到后缀；
        $pathName = $product.'/'.env('APP_ENV'). $this->fileTypes[$type] . date('/Y/m/');
    }
}
