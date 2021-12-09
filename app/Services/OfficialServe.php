<?php

namespace App\Services;

class OfficialServe extends Service
{
    /**
     * 事件类型
     * @param $data
     * @param $user
     *
     * @return string
     */
    private function msgType($data,$user)
    {
        switch ($data['MsgType']) {
            case 'event':                   //事件推送
                $arr = $this->eventType($data,$user);
                break;
            case 'text':                    //文本消息
                $arr = $this->textType($data,$user);
                break;
            case 'image':                   //图片消息
                $arr = $this->imageType($data,$user);
                break;
            case 'voice':                   //语音消息
                $arr = $this->voiceType($data,$user);
                break;
            case 'video':                   //视频消息
                $arr = $this->videoType($data,$user);
                break;
            case 'shortvideo':              //小视频消息
                $arr = $this->shortvideoType($data,$user);
                break;
            case 'location':                //地理位置
                $arr = $this->locationType($data,$user);
                break;
            case 'link':                    //链接消息
                $arr = $this->linkType($data,$user);
                break;
            default:
                $arr = [];
        }
        if($arr){
            $xml = $this->arrXml($arr);
        }else{
            $xml = 'success';
        }
        return $xml;
    }

    /**
     * 事件回复
     * @param $event
     * @param $user
     *
     * @return array|mixed
     */
    public function eventType($Content)
    {
        $data = [];
        $info = $this->userModel->getUser($user['openid']);
        if($user['subscribe']){
            if($info){
                $this->userModel->editUser($info['id'],$user);
            }else{
                $this->userModel->addUser($user);
                $info = $this->userModel->getUser($user['openid']);
            }
        }
        switch ($event['Event']) {
            case 'subscribe':               //订阅事件
                $data = $this->subscribe($info,$event['EventKey']);
                break;
            case 'unsubscribe':             //取消订阅事件
                if($info) {
                    $this->unsubscribe($info);
                }
                break;
            case 'SCAN':                    //扫描带参数二维码
                $data = $this->scan($user['openid'],$event['EventKey']);
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

    private function subscribe($info,$key)
    {
        $reply = $this->replyModel->reply([['id','=',1]]);
        $content = json_decode($reply['comment'],true);
        $data['FromUserName'] = $this->wxid;
        $data['ToUserName'] = $info['openid'];
        $data['CreateTime'] = time();
        $data['MsgType'] = 'text';
        $data['Content'] = $content['Content'];
        if(!empty($key) && substr_count($key,'_') === 1){
            list($qrscene,$scene) = explode('_',$key);
            $channel = $this->model->getChannel($scene);
            $this->model->incChannel($channel['id'],'subscribe');
            $insert['subscribe'] = 1;
            $insert['uid'] = $info['id'];
            $insert['openid'] = $info['openid'];
            $insert['cid'] = $channel['id'];
            $insert['scene'] = $channel['scene'];
            $this->model->addSubscribe($insert);
        }
        return $data;
    }

    private function unsubscribe($info)
    {
        $where[] = ['uid','=',$info['id']];
        $where[] = ['subscribe','=',1];
        $channel = $this->model->subscribeInfo($where);
        $where[] = ['cid','=',$channel['cid']];
        $this->model->subscribeStatus($where,0);
        $this->model->incChannel($channel['cid'],'unsubscribe');
    }

    private function scan($openid,$key)
    {
        $data = [];
        $channel = $this->model->getChannel($key);
        $this->model->incChannel($channel['id'],'number');
        $where[] = ['status','=',1];
        $where[] = ['keyword','=',$key];
        $reply = $this->replyModel->reply($where);
        if($reply){
            $type = new ReplyController($this->replyModel);
            $data = $type->replyType($reply,$openid);
        }
        return $data;
    }
}
