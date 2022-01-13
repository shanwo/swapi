<?php

namespace App\Services;

use App\Models\FeedbackModel;
use App\Models\ReplyModel;
use App\Models\UserThirdModel;

class OfficialServe extends Service
{
    protected $replyModel;
    protected $feedbackModel;
    protected $userThirdModel;

    public function __construct()
    {
        parent::__construct();
        $this->replyModel = new ReplyModel();
        $this->feedbackModel = new FeedbackModel();
        $this->userThirdModel = new UserThirdModel();
    }

    private function reply($message)
    {
        $keyword = '关注';
        if (!empty($message['Content'])) {
            $keyword = trim($message['Content']);
        }
        $data = [];
        $reply = $this->redis::HGET('reply', $keyword);
        if ($reply) {
            $data = json_decode($reply, true);
        } else {
            $where[] = ['status', '=', 1];
            $where[] = ['is_delete', '=', Service::DELETE_NO];
            $lists = $this->replyModel->select('id', 'type', 'keyword', 'comment')->where($where)->get()->toArray();
            foreach ($lists as $k => $v) {
                $number = $this->redis::ZSCORE('reply_number', $v['keyword']);
                if ($number) {
                    $this->replyModel->whereKey($v['id'])->increment('number', $number);
                    $this->redis::ZREM('reply_number', $v['keyword']);
                }
                $v['comment'] = json_decode($v['comment'],true);
                $this->redis::HSET('reply', $v['keyword'], json_encode($v));
                if ($v['keyword'] == $keyword) $data = $v;
            }
        }
        if ($data) $this->redis::ZINCRBY('reply_number', 1, $keyword);
        return $data;
    }

    private function feedback($message, $config)
    {
        $keyword = trim($message['Content']);
        $sw = mb_substr($keyword, 0, 4);
        if (strcmp($sw, '#闪我#') == 0) {
            $where = [
                ['source', '=', $config['name']],
                ['open_id', '=', $message['FromUserName']],
                ['app_id', '=', $config['app_id']],
                ['status', '=', 1]
            ];
            $userThird = $this->userThirdModel->where($where)->first(['id', 'user_id']);
            $insert = [
                'user_id'  => $userThird['user_id'],
                'contacts' => '微信公众号',
                'content'  => mb_substr($keyword, 4)
            ];
            $this->feedbackModel->insertGetId($insert);
            return '您提交的信息我已经记下啦';
        }
        return "如果您有什么想说的，请发送\n#闪我#[您想说的话]";
    }

    /**
     * 事件回复
     * @param $message
     * @param $user
     * @param $config
     * @return string|void
     */
    public function eventType($message, $user, $config)
    {
        switch ($message['Event']) {
            case'subscribe':               //订阅
                (new UserService)->wechat($user, $config);
                $text = $this->reply($message);
                return $this->replyMsg($text);
            case'unsubscribe':             //取消订阅
                break;
            case'SCAN':                    //扫描带参数二维码
                break;
            case'LOCATION':                 //上报地理位置
                break;
            case'VIEW':                     //菜单 - 点击菜单跳转链接
                break;
            case'CLICK':                    //菜单 - 点击菜单拉取消息
                break;
            case'scancode_push':            //菜单 - 扫码推事件(客户端跳URL)
                break;
            case'scancode_waitmsg':         //菜单 - 扫码推事件(客户端不跳URL)
                break;
            case'pic_sysphoto':             //菜单 - 弹出系统拍照发图
                break;
            case'pic_photo_or_album':       //菜单 - 弹出拍照或者相册发图
                break;
            case'pic_weixin':               //菜单 - 弹出微信相册发图器
                break;
            case'location_select':          //菜单 - 弹出地理位置选择器
                break;
            case'MASSSENDJOBFINISH':        //发送结果 - 高级群发完成
                break;
            case'TEMPLATESENDJOBFINISH':    //发送结果 - 模板消息发送结果
                break;
            case'kfcreatesession':          //多客服 - 接入会话
                break;
            case'kfclosesession':           //多客服 - 关闭会话
                break;
            case'kfswitchsession':          //多客服 - 转接会话
                break;
            case'card_pass_check':          //卡券 - 审核通过
                break;
            case'card_not_pass_check':      //卡券 - 审核未通过
                break;
            case'user_get_card':            //卡券 - 用户领取卡券
                break;
            case'user_del_card':            //卡券 - 用户删除卡券
                break;
            case'merchant_order':           //微信小店 - 订单付款通知
                break;
            case'ShakearoundUserShake':     //摇一摇事件通知
                break;
            default:
        }
        return '';
    }

    public function textType($message, $config)
    {
        $text = $this->reply($message);
        if (!$text) return $this->feedback($message, $config);
        return $this->replyMsg($text);
    }

    public function imageType($message)
    {
        return '图片地址:' . $message['PicUrl'];
    }

    public function voiceType($message)
    {
        if ($message['Recognition']) {
            $message['Content'] = $message['Recognition'];
            $voice = $this->reply($message);
            return $this->replyMsg($voice);
        }
        return '语音信息识别失败';
    }

    public function videoType($message)
    {
        return '这是一个视频';
    }

    public function locationType($message)
    {
        return "此位置是：" . $message['Label'] .
            "\n位置经度:" . $message['Location_Y'] .
            "\n位置纬度:" . $message['Location_X'];
    }

    public function linkType($message)
    {
        if ($message['Title']) {
            $message['Content'] = $message['Title'];
            $link = $this->reply($message);
            return $this->replyMsg($link);
        }
        return '这个链接识别不出来啊';
    }

    public function fileType($message)
    {
        return '这是一个文件';
    }

    private function replyMsg($reply)
    {
        $comment = $reply['comment'];
        switch ($reply['type']) {
            case 1:                         //文本消息
                return $comment['Content'];
            case 2:                         //图片消息
                return new \EasyWeChat\Kernel\Messages\Image($comment['MediaId']);
            case 3:                         //语音消息
                return new \EasyWeChat\Kernel\Messages\Voice($comment['MediaId']);
            case 4:                         //视频消息
                return new \EasyWeChat\Kernel\Messages\Video($comment['MediaId'], [
                    'title'       => $comment['Title'],
                    'description' => $comment['Description'],
                ]);
            case 5:                         //音乐消息
                return new \EasyWeChat\Kernel\Messages\Music([
                    'title'          => $comment['Title'],
                    'description'    => $comment['Description'],
                    'url'            => $comment['MusicURL'],
                    'hq_url'         => $comment['HQMusicUrl'],
                    'thumb_media_id' => $comment['ThumbMediaId'],
                ]);
            case 6:                         //图文消息
                return new \EasyWeChat\Kernel\Messages\NewsItem([
                    'title'       => $comment['Title'],
                    'description' => $comment['Description'],
                    'url'         => $comment['Url'],
                    'picurl'      => $comment['PicUrl'],
                ]);
            case 7:                         //文章消息
                return new \EasyWeChat\Kernel\Messages\Article([
                    'title'          => $comment['Title'],
                    'author'         => $comment['Author'],
                    'content'        => $comment['Description'],
                    'thumb_media_id' => $comment['ThumbMediaId'],
                    'digest'         => $comment['digest'],
                    'source_url'     => $comment['source_url'],
                    'show_cover'     => $comment['show_cover'],
                ]);
        }
    }
}
