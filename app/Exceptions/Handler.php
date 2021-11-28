<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];
    protected $code = 0;
    protected $levels = [
        100 => 'debug',
        200 => 'info',
        300 => 'notice',
        400 => 'notice',
        500 => 'warning',
        600 => 'error',
        700 => 'critical',
        800 => 'alert',
        900 => 'emergency',
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $classArray = explode(DIRECTORY_SEPARATOR, get_class($exception));
        if(count($classArray) == 1) $classArray = explode('\\', reset($classArray));
        switch (end($classArray)) {
            case 'MethodNotAllowedHttpException':
                $this->code = 500;
                break;
            case 'NotFoundHttpException':
                $this->code = 600;
                break;
            case 'QueryException':
                $this->code = 700;
                break;
            case 'ReflectionException':
                $this->code = 800;
                break;
            default :
                $this->code = 900;
        }
        if ($this->code) {
            $msg = [
                500 => '请求类型不匹配',
                600 => '请求地址未开放',
                700 => '数据库语句错误',
                800 => '服务端接口异常',
                900 => '程序错误请重试',
            ];
            $result = [
                'request' => $request->input(),
                'data'    => $exception->getMessage(),
                'code'    => $this->code,
                'msg'     => $msg[$this->code],
                'uuid'    => Uuid::uuid6(),
            ];
            Log::log($this->levels[$this->code], 'Render Data', $result);
            unset($result['request']);
            return response()->json($result);
        }
        return parent::render($request, $exception);
    }
}
