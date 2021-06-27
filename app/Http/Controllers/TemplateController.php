<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\TemplateService;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    protected $templateService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->templateService = new TemplateService();
    }

    public function lists()
    {
        $this->validate([], []);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->templateService->lists();
        return $this->returnApi($data);
    }

     public function info()
     {
         $this->validate([
             'id' => 'required|integer|gt:0|exists:sw_template,id',
         ], [
             'id' => 'ID',
         ]);
         if ($this->msg) {
             return $this->returnApi();
         }
         $id = $this->request->input('id');
         $data = $this->templateService->info($id);
         return $this->returnApi($data);
     }
}
