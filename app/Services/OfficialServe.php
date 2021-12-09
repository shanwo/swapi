<?php

namespace App\Services;

use App\Models\ReplyModel;

class OfficialServe extends Service
{
    protected $replyModel;

    public function __construct()
    {
        parent::__construct();
        $this->replyModel = new ReplyModel();
    }

    /**
     * 事件回复
     * @param $event
     *
     * @return array|mixed
     */
    public function eventType($message)
    {
        $data = [];
        $info = $this->userModel->getUser($user['openid']);
        if ($user['subscribe']) {
            if ($info) {
                $this->userModel->editUser($info['id'], $user);
            } else {
                $this->userModel->addUser($user);
                $info = $this->userModel->getUser($user['openid']);
            }
        }
        switch ($message['Event']) {
            case 'subscribe':               //订阅事件
                $data = $this->reply($message);
                break;
            case 'unsubscribe':             //取消订阅事件
                if ($info) {
                    $this->unsubscribe($info);
                }
                break;
            case 'SCAN':                    //扫描带参数二维码
                $data = $this->scan($user['openid'], $event['EventKey']);
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
        return $data;
    }

    private function reply($message)
    {
        $where[] = ['status', '=', 1];
        if (empty($message['Content'])) {
            $where[] = ['type', '=', 0];
        } else {
            $where[] = ['keyword', '=', trim($message['Content'])];
        }
        return $this->replyModel->select('id', 'type', 'comment')->where($where)->first();
    }

    private function unsubscribe($info)
    {
        $where[] = ['uid', '=', $info['id']];
        $where[] = ['subscribe', '=', 1];
        $channel = $this->model->subscribeInfo($where);
        $where[] = ['cid', '=', $channel['cid']];
        $this->model->subscribeStatus($where, 0);
        $this->model->incChannel($channel['cid'], 'unsubscribe');
    }

    private function scan($openid, $key)
    {
        $data = [];
        $channel = $this->model->getChannel($key);
        $this->model->incChannel($channel['id'], 'number');
        $where[] = ['status', '=', 1];
        $where[] = ['keyword', '=', $key];
        $reply = $this->replyModel->reply($where);
        if ($reply) {
            $type = new ReplyController($this->replyModel);
            $data = $type->replyType($reply, $openid);
        }
        return $data;
    }

    public function textType($message)
    {
        return $this->reply($message);
    }

    public function imageType($message)
    {
        return '暂不支持图片消息处理';
    }

    public function voiceType($message)
    {
        return '暂不支持语音消息处理';
    }

    public function videoType($message)
    {
        return '暂不支持视频消息处理';
    }

    public function locationType($message)
    {
        return '暂不支持地理位置处理';
    }

    public function linkType($message)
    {
        return '暂不支持链接消息处理';
    }

    public function fileType($message)
    {
        return '暂不支持文件消息处理';
    }

    public function replyType($reply)
    {
        $comment = json_decode($reply['comment'], true);
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
                    'thumb_media_id' => $comment['thumb_media_id'],
                    'digest'         => $comment['digest'],
                    'source_url'     => $comment['source_url'],
                    'show_cover'     => $comment['show_cover'],
                ]);
            default:
                return '我好像不明白...';
        }
    }
}
