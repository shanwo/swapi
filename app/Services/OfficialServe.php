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
            $keyword = substr_replace('。','',$keyword);
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
                $v['comment'] = json_decode($v['comment'], true);
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
        if (strcmp($sw, '#酱紫#') == 0) {
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
            return '你提交的信息小闪已经记下啦[耶]';
        }
        return "小闪搞没懂你的意思哇[委屈]\n如果你有什么话，可以这样告诉我：\n#酱紫#[想说的话]\n例如：#酱紫#期待新功能哦";
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
                return $text['comment'];
            case'unsubscribe':             //取消订阅
                break;
            case'SCAN':                    //扫描带参数二维码
                break;
            case'LOCATION':                 //上报地理位置
                break;
            default:
                return '暂不支持的事件类型';
        }
        return '暂不支持的事件类型';
    }

    public function textType($message, $config)
    {
        $text = $this->reply($message);
        if (!$text) return $this->feedback($message, $config);
        return $text['comment'];
    }

    public function imageType($message)
    {
        return "识别结果\n" . $message['PicUrl'];
    }

    public function voiceType($message)
    {
        if ($message['Recognition']) {
            $message['Content'] = $message['Recognition'];
            $voice = $this->reply($message);
            if (!$voice) return "识别结果\n" . $message['Recognition'];
            return $voice['comment'];
        }
        return '语音信息识别失败';
    }

    public function locationType($message)
    {
        return "此位置是：" . $message['Label'] .
            "\n经度:" . $message['Location_Y'] .
            "\n纬度:" . $message['Location_X'];
    }

    public function linkType($message)
    {
        if ($message['Title']) {
            $message['Content'] = $message['Title'];
            $link = $this->reply($message);
            if (!$link) return "识别结果\n" . $message['Title'] . "\n" . $message['Url'];
            return $link['comment'];
        }
        return '这个链接有问题啊,小闪识别不出来吖';
    }
}
