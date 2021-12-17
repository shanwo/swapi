<?php
/**
 * User: Powerless
 * Date: 21/06/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\AreaModel;
use App\Models\UserModel;
use App\Models\UserThirdModel;
use Illuminate\Support\Facades\Crypt;
use Overtrue\Socialite\AuthorizeFailedException;
use Overtrue\Socialite\SocialiteManager;

class UserService extends Service
{
    public $uid = 0;
    public $configName;
    protected $areaModel;
    protected $userModel;
    protected $userThirdModel;

    public function __construct()
    {
        parent::__construct();
        $this->areaModel = new AreaModel();
        $this->userModel = new UserModel();
        $this->userThirdModel = new UserThirdModel();
    }

    public function info($id)
    {
        $info = $this->redis::GET('user_infos:' . $id);
        $info = json_decode($info, true) ?? [];
        if(!$info){
            $info = $this->userModel->whereKey($id)->first();
            $info['contact'] = json_decode($info['contact'], true);
            $info['third'] = $this->userThirdModel->select('id', 'logins_number', 'source', 'open_id', 'login_ip', 'created_at')->where(['user_id' => $id])->get()->toArray();
            $this->redis::SET('user_infos:' . $id, json_encode($info, JSON_UNESCAPED_UNICODE));
        }
        return $info;
    }

    public function edit($request)
    {
        $uid = $request->user()->id;
        $aid = $request->input('area_id');
        $area = $this->areaModel->whereKey($aid)->first();
        list($province,$city) = explode('-',$area['merger_name']);
        $this->setTag('province',$uid,$province);
        $this->setTag('city',$uid,$city);
        $contact = $request->input('contact');
        $update = [
            'nickname'    => $request->input('nickname'),
            'avatar'      => $request->input('avatar'),
            'area_id'     => $aid,
            'merger_name' => $area['merger_name'],
            'contact'     => json_encode($contact, JSON_UNESCAPED_UNICODE),
        ];
        $sex = $request->input('sex', '');
        if (is_numeric($sex)) {
            $this->setTag('sex',$uid,$sex);
            $update['sex'] = $sex;
        }
        $birthday = $request->input('birthday', '');
        if ($birthday) {
            $this->setTag('birthday',$uid,substr($birthday,5));
            $age = date('Y') - substr($birthday,0,4);
            $this->setTag('age',$uid,$age);
            $update['birthday'] = $birthday;
        }
        return $this->userModel->whereKey($uid)->update($update);
    }

    public function driver($request, $name)
    {
        $config = $this->configInfo($name);
        //公众号数据，使用微信驱动
        if ($name == 'official') {
            $socialite = new SocialiteManager([
                'wechat' => [
                    'client_id'     => $config['app_id'],
                    'client_secret' => $config['secret'],
                    'redirect'      => $config['callback'],
                ],
            ], $request);
            return $socialite->driver('wechat')->scopes(['snsapi_userinfo']);
        } else {
            $socialite = new SocialiteManager([
                $name => [
                    'client_id'     => $config['key'],
                    'client_secret' => $config['secret'],
                    'redirect'      => $config['callback'],
                ],
            ], $request);
            return $socialite->driver($name);
        }
    }

    public function signInResult($request,$configName)
    {
        $data = [];
        try {
            $state = $request->input('state', 0);
            if ($state) {
                $this->uid = $this->redis::GET('signIn:' . $configName . ':' . $state);
                $this->redis::DEL('signIn:' . $configName . ':' . $state);
            }
            $user = $this->driver($request, $configName)->stateless()->user();
            $user['ip'] = $request->ip();
            $config = $this->configInfo($configName);
            $config['name'] = $configName;
            $uid = $this->wechat($user,$config);
            $data = $this->info($uid);
            $this->redis::SETEX('user_tokens:' . $data['api_token'], $this->oneDay * 30, $uid);
        } catch (AuthorizeFailedException $e) {
            $data['msg'] = '(' . $e->body['errcode'] . '):' . $e->body['errmsg'];
        }
        return $data;
    }

    public function wechat($user,$config)
    {
        $original = $user['original'];
        $where[] = ['source', '=', $config['name']];
        $where[] = ['open_id', '=', $original['openid']];
        if($config['name'] == 'official'){
            $app_id = $config['app_id'];
        }else{
            $app_id = $config['key'];
        }
        $where[] = ['app_id', '=', $app_id];
        $where[] = ['status', '=', 1];
        $userThird = $this->userThirdModel->where($where)->first(['id', 'user_id', 'logins_number']);
        if ($userThird) {
            if (!empty($this->uid) && $this->uid != $userThird['user_id']) {
                return ['msg' => '当前微信已绑定其他账号'];
            }
            $update = [
                'login_ip'      => $user['ip'],
                'logins_number' => $userThird['logins_number'] + 1
            ];
            $this->userThirdModel->whereKey($userThird['id'])->update($update);
            $this->uid = $userThird['user_id'];
        } else {
            if ($original['unionid']) {
                $userThird = $this->userThirdModel->where('status', 1)->where('union_id', $original['unionid'])->first(['id', 'user_id']);
                $this->uid = $userThird['user_id'];
            }
            $token = Crypt::encryptString($original['openid']);
            if (!$this->uid) {
                $insertUser = [
                    'nickname'  => htmlspecialchars($original['nickname']),
                    'sex'       => $original['sex'],
                    'avatar'    => $original['headimgurl'] ?? 'https://cdn.shanwo.cc/img/avatar.jpg',
                    'api_token' => $token
                ];
                $this->uid = $this->userModel->insertGetId($insertUser);
                $insertUser['id'] = $this->uid;
                $pageService = new PageService();
                $pageService->initial(env('USER_TEMPLATE', 1), $insertUser);
            } else {
                $updateUser = [
                    'api_token' => $token
                ];
                $this->userModel->whereKey($this->uid)->update($updateUser);
            }
            $insertThird = [
                'user_id'  => $this->uid,
                'nickname' => htmlspecialchars($original['nickname']),
                'union_id' => $original['unionid'],
                'open_id'  => $original['openid'],
                'source'   => $config['name'],
                'app_id'   => $app_id,
                'login_ip' => $user['ip'],
                'original' => json_encode($original)
            ];
            $this->userThirdModel->insertGetId($insertThird);
        }
        return $this->uid;
    }
}
