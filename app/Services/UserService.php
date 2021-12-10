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

class UserService extends Service
{
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
            $update['sex'] = $sex;
        }
        $birthday = $request->input('birthday', '');
        if ($birthday) {
            $update['birthday'] = $birthday;
        }
        return $this->userModel->whereKey($uid)->update($update);
    }

    public function wechat($uid, $data)
    {
        $original = $data['original'];
        $config = $this->configInfo($this->configName);
        $where[] = ['source', '=', $this->configName];
        $where[] = ['open_id', '=', $original['openid']];
        if($this->configName == 'official'){
            $app_id = $config['app_id'];
        }else{
            $app_id = $config['key'];
        }
        $where[] = ['app_id', '=', $app_id];
        $where[] = ['status', '=', 1];
        $userThird = $this->userThirdModel->where($where)->first(['id', 'user_id', 'logins_number']);
        if ($userThird) {
            if (!empty($uid) && $uid != $userThird['user_id']) {
                return ['msg' => '当前微信已绑定其他账号'];
            }
            $update = [
                'login_ip'      => $data['ip'],
                'logins_number' => $userThird['logins_number'] + 1
            ];
            $this->userThirdModel->whereKey($userThird['id'])->update($update);
            $uid = $userThird['user_id'];
        } else {
            if ($original['unionid']) {
                $userThird = $this->userThirdModel->where('status', 1)->where('union_id', $original['unionid'])->first(['id', 'user_id']);
                $uid = $userThird['user_id'];
            }
            $token = Crypt::encryptString($original['openid']);
            if (!$uid) {
                $insertUser = [
                    'nickname'  => htmlspecialchars($original['nickname']),
                    'sex'       => $original['sex'],
                    'avatar'    => $original['headimgurl'] ?? 'https://cdn.shanwo.cc/img/avatar.jpg',
                    'api_token' => $token
                ];
                $uid = $this->userModel->insertGetId($insertUser);
                $insertUser['id'] = $uid;
                $pageService = new PageService();
                $pageService->initial(env('USER_TEMPLATE', 1), $insertUser);
            } else {
                $updateUser = [
                    'api_token' => $token
                ];
                $this->userModel->whereKey($uid)->update($updateUser);
            }
            $insertThird = [
                'user_id'  => $uid,
                'nickname' => htmlspecialchars($original['nickname']),
                'union_id' => $original['unionid'],
                'open_id'  => $original['openid'],
                'source'   => $this->configName,
                'app_id'   => $app_id,
                'login_ip' => $data['ip'],
                'original' => json_encode($original)
            ];
            $this->userThirdModel->insertGetId($insertThird);
        }
        return $uid;
    }
}
