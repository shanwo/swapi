<?php
/**
 * User: Powerless
 * Date: 21/07/15
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\UserService;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    protected $uid = 0;
    protected $userService;
    protected $orderService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userService = new UserService();
        $this->orderService = new OrderService();

    }

    private function getUser($state, $name)
    {
        if ($state) {
            $this->uid = $this->service->redis::GET('signIn:' . $name . ':' . $state);
            $this->service->redis::DEL('signIn:' . $name . ':' . $state);
        }
    }

    /**
     * 第三方登录回调
     */
    public function douban()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function douyin()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function facebook()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function feishu()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function github()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function google()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function linkedin()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function official()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $state = $this->request->input('state', 0);
        $this->userService->configName = 'official';
        $this->getUser($state, $this->userService->configName);
        $user = $this->driver($this->request, $this->userService->configName)->stateless()->user();
        $user['ip'] = $this->request->ip();
        $uid = $this->userService->wechat($this->uid, $user);
        $data = $this->userService->info($uid);
        if (empty($data['msg'])) {
            $this->service->redis::SETEX('user_tokens:' . $data['api_token'], $this->service->timeOut, $uid);
            $this->service->redis::SET('user_infos:' . $uid, json_encode($data, JSON_UNESCAPED_UNICODE));
//            $arr = [$data['id'] => md5(dechex($data['id']))];
//            $this->service->redis::setex('token:' . $data['api_token'], $this->service->timeOut, json_encode($arr));
//            $this->service->redis::setex('user:' . $data['id'], $this->service->timeOut, json_encode($data, JSON_UNESCAPED_UNICODE));
            $this->code = 200;
        } else {
            $this->msg = $data['msg'];
        }
        return $this->returnApi($data);
    }

    public function outlook()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function qq()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function taobao()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function wechat()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $state = $this->request->input('state', 0);
        $this->userService->configName = 'wechat';
        $this->getUser($state, $this->userService->configName);
        $user = $this->driver($this->request, $this->userService->configName)->stateless()->user();
        $user['ip'] = $this->request->ip();
        $uid = $this->userService->wechat($this->uid, $user);
        $data = $this->userService->info($uid);
        if (empty($data['msg'])) {
            $this->service->redis::SETEX('user_tokens:' . $data['api_token'], $this->service->timeOut, $uid);
            $this->service->redis::SET('user_infos:' . $uid, json_encode($data, JSON_UNESCAPED_UNICODE));
//            $arr = [$data['id'] => md5(dechex($data['id']))];
//            $this->service->redis::setex('token:' . $data['api_token'], $this->service->timeOut, json_encode($arr));
//            $this->service->redis::setex('user:' . $data['id'], $this->service->timeOut, json_encode($data, JSON_UNESCAPED_UNICODE));
            $this->code = 200;
        } else {
            $this->msg = $data['msg'];
        }
        return $this->returnApi($data);
    }

    public function weibo()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function wework()
    {
        $this->validate([
            'code'  => 'required|alpha_num|size:32',
            'state' => 'filled|alpha_num|between:32,64',
        ], [
            'code'  => '编码',
            'state' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->msg = '暂未开通此平台登录';
        return $this->returnApi();
    }

    public function officialBind()
    {
        $this->validate([
            'echostr'   => 'required|alpha_num|between:16,32',
            'nonce'     => 'required|alpha_num|between:8,16',
            'signature' => 'required|alpha_num|between:32,64',
            'timestamp' => 'required|alpha_num|between:8,16',
        ], [
            'echostr'   => '随机字符串',
            'nonce'     => '随机数',
            'signature' => '加密签名',
            'timestamp' => '时间戳',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $echoStr = $this->request->input('echostr');
        $signature = $this->request->input('signature');
        $timestamp = $this->request->input('timestamp');
        $nonce = $this->request->input('nonce');
        $tmpArr = ['rRmxsWtzpNeePu85mDNGtHODlkSt32lD', $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        # 打印返回结果
        if ($tmpStr == $signature) {
            return response($echoStr);
        } else {
            return response();
        }
    }

    public function officialServer()
    {
        $config = $this->service->configInfo('official');
        $app = Factory::officialAccount($config);
        $app->server->push(function ($message) {
            switch ($message['MsgType']) {
                case 'event':
                    return '收到事件消息';
                    break;
                case 'text':
                    return '收到文字消息';
                    break;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                case 'file':
                    return '收到文件消息';
                default:
                    return '收到其它消息';
                    break;
            }
        });
        $response = $app->server->serve();
        return $response;
    }

    /**
     * 支付回调
     */
    public function payment()
    {
//        $this->validate([
//        ], [
//        ]);
//        if ($this->msg) {
//            return $this->returnApi();
//        }
        $this->service->redis::SET('request_log:' . time(), 600, json_encode($this->request->all(), JSON_UNESCAPED_UNICODE));
        $config = $this->service->configInfo('official');
        $payment = Factory::Payment($config);
        $response = $payment->handlePaidNotify(function($message, $fail){
            $info = $this->service->redis::GET('pay_order:' . $message['out_trade_no']);
            if(!$info) return $fail('订单支付超时');
            $info = json_decode($info,true) ?? [];
            $res = $this->orderService->info(['order_sn'=>$message['out_trade_no']]);
            if($res) return true;
            $this->orderService->add($info);

            // 首先查看 order 表, 如果 order 表有记录, 表示已经支付过了
//            $order = Order::where('order_sn', $message['out_trade_no'])->first();
//            if ($order) {
//                return true; // 如果已经生成订单, 表示已经处理完了, 告诉微信不用再通知了
//            }
//            // 查看支付日志
//            $payLog = PayLog::where('out_trade_no', $message['out_trade_no'])->first();
//            if (!$payLog || $payLog->paid_at) { // 如果订单不存在 或者 订单已经支付过了
//                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
//            }
            // return_code 表示通信状态，不代表支付状态
//            if ($message['return_code'] === 'SUCCESS') {
//                // 用户是否支付成功
//                if ($message['result_code'] === 'SUCCESS') {
//                    // 更新支付时间为当前时间
//                    $payLog->paid_at = now();
//                    $post_id = $payLog->post_id;
//                    // 联表查询 post 的相关信息
//                    $post_title = $payLog->post->title;
//                    $post_price = $payLog->post->price;
//                    $post_original_price = $payLog->post->original_price;
//                    $post_cover = $payLog->post->post_cover;
//                    $post_description = $payLog->post->description;
//                    $user_id = $payLog->post->user_id;
//
//                    // 创建订单记录
//                    Order::create([
//                        'order_sn' => $message['out_trade_no'],
//                        'total_fee' => $message['total_fee'],
//                        'pay_log_id' => $payLog->id,
//                        'status' => 1,
//                        'user_id' => $user_id,
//                        'paid_at' => $payLog->paid_at,
//                        'post_id' => $post_id,
//                        'post_title' => $post_title,
//                        'post_price' => $post_price,
//                        'post_original_price' => $post_original_price,
//                        'post_cover' => $post_cover,
//                        'post_description' => $post_description,
//                    ]);
//
//                    // 更新 PayLog, 这里的字段都是根据微信支付结果通知的字段设置的(https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=9_7&index=8)
//                    PayLog::where('out_trade_no', $message['out_trade_no'])->update([
//                        'appid' => $message['appid'],
//                        'bank_type' => $message['bank_type'],
//                        'total_fee' => $message['total_fee'],
//                        'trade_type' => $message['trade_type'],
//                        'is_subscribe' => $message['is_subscribe'],
//                        'mch_id' => $message['mch_id'],
//                        'nonce_str' => $message['nonce_str'],
//                        'openid' => $message['openid'],
//                        'sign' => $message['sign'],
//                        'cash_fee' => $message['cash_fee'],
//                        'fee_type' => $message['fee_type'],
//                        'transaction_id' => $message['transaction_id'],
//                        'time_end' => $payLog->paid_at,
//                        'result_code' => $message['result_code'],
//                        'return_code' => $message['return_code'],
//                    ]);
//                }
//            } else {
//                // 如果支付失败, 也更新 PayLog, 跟上面一样, 就是多了 error 信息
//                PayLog::where('out_trade_no', $message['out_trade_no'])->update([
//                    'appid' => $message['appid'],
//                    'bank_type' => $message['bank_type'],
//                    'total_fee' => $message['total_fee'],
//                    'trade_type' => $message['trade_type'],
//                    'is_subscribe' => $message['is_subscribe'],
//                    'mch_id' => $message['mch_id'],
//                    'nonce_str' => $message['nonce_str'],
//                    'openid' => $message['openid'],
//                    'sign' => $message['sign'],
//                    'cash_fee' => $message['cash_fee'],
//                    'fee_type' => $message['fee_type'],
//                    'transaction_id' => $message['transaction_id'],
//                    'time_end' => $payLog->paid_at,
//                    'result_code' => $message['result_code'],
//                    'return_code' => $message['return_code'],
//                    'err_code' => $message['err_code'],
//                    'err_code_des' => $message['err_code_des'],
//                ]);
//                return $fail('通信失败，请稍后再通知我');
//            }
            $this->service->redis::DEL('pay_order:' . $message['out_trade_no']);
            return true; // 返回处理完成
        });
        return $response;
    }
}
