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
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function driver($request)
    {
        $config = $this->configInfo('wx');
        $socialite = new SocialiteManager([
            $this->pluginName => [
                'client_id'     => $config['key'],
                'client_secret' => $config['secret'],
                'redirect'      => $config['callback'],
            ],
        ], $request);
        return $socialite->driver($this->pluginName);
    }
}
