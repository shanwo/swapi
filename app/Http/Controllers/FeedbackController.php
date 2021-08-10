<?php
/**
 * User: Powerless
 * Date: 2021/7/11
 * Blog: https://wzxaini9.cn
 * Tips: 反馈模块
 */

namespace App\Http\Controllers;

use App\Services\FeedbackService;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    protected $feedbackService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->feedbackService = new FeedbackService();
    }

    public function add()
    {
        $this->validate([
            'contacts' => 'nullable|string|max:32',
            'content'  => 'required|string|max:65535',
        ], [
            'contacts' => '联系方式',
            'content'  => '反馈内容',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->feedbackService->add($this->request, $this->user['id']);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
