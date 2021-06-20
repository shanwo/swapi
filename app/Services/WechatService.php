<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\UserModel;
use Overtrue\Socialite\SocialiteManager;

class WechatService extends Service
{
    protected $pluginName = 'wechat';
    protected $config;
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function driver($request)
    {
        $plugin = $this->pluginModel->where(['name' => $this->pluginName, 'status' => 1])->first();
        $this->config = json_decode($plugin['config'], true);
        $config = [
            $this->pluginName => [
                'client_id'     => $this->config['app_key'],
                'client_secret' => $this->config['app_secret'],
                'redirect'      => $this->config['callback_url'],
            ],
        ];
        $socialite = new SocialiteManager($config, $request);
        return $socialite->driver($this->pluginName);
    }
}
