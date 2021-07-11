<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\UploadModel;
use Qcloud\Cos\Client;

class UploadService extends Service
{
    protected $uploadModel;
    private $oss;
    private $domain = 'https://cdn.shanwo.cc/';

    public function __construct()
    {
        parent::__construct();
        $this->uploadModel = new UploadModel();
        $config = $this->configInfo('oss');
        $this->oss = new Client([
            'region'      => 'ap-beijing',
            'credentials' => [
                'secretId'  => $config['key'],
                'secretKey' => $config['secret'],
            ]
        ]);
    }

    /**
     * @param $ext
     * 返回后缀所对应的文件类型
     * @return int
     */
    private function type($ext)
    {
        $fileTypes = ['file', 'picture', 'compression', 'office', 'music', 'video', 'code'];
        $picture = ['bmp', 'gif', 'ico', 'jpeg', 'jpg', 'png', 'psd', 'swf', 'tif'];
        $compression = ['7z', 'apk', 'bz2', 'exe', 'gz', 'iso', 'jar', 'lz', 'mdf', 'rar', 'tar', 'xz', 'zip', 'zipx'];
        $office = ['csv', 'doc', 'docx', 'log', 'pdf', 'ppt', 'pptx', 'tmp', 'txt', 'wps', 'xls', 'xlsx'];
        $music = ['ape', 'flac', 'midi', 'mp2', 'mp3', 'mpa', 'ra', 'wav', 'wma', 'wv'];
        $video = ['asf', 'avi', 'flv', 'mkv', 'mp4', 'ram', 'rm', 'rmvb', 'rp', 'rpm', 'wm', 'wmp', 'wmv'];
        $code = ['conf', 'cpp', 'css', 'go', 'h', 'html', 'js', 'json', 'lock', 'md', 'mod', 'php', 'py', 'sh', 'sql', 'xml'];
        switch ($ext) {
            case in_array($ext, $picture):
                $type = 1;
                break;
            case in_array($ext, $compression):
                $type = 2;
                break;
            case in_array($ext, $office):
                $type = 3;
                break;
            case in_array($ext, $music):
                $type = 4;
                break;
            case in_array($ext, $video):
                $type = 5;
                break;
            case in_array($ext, $code):
                $type = 6;
                break;
            default:
                $type = 0;
        }
        return $fileTypes[$type];
    }

    /**
     * 上传文件
     * @param $request
     * @return mixed
     */
    public function update($request)
    {
        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();
        $type = $this->type($ext);
        if (!strcmp(app('env'), 'prod')) {
            $path = $type . date('/Y/m/');
        } else {
            $path = app('env') . '/' . $type . date('/Y/m/');
        }
        $name = dechex(LARAVEL_START * 10000);
        if ($ext) {
            $name = $name . '.' . $ext;
        }
        $this->oss->upload('cdn-1305937303', $path . $name, fopen($file->getRealPath(), "rb"));
        $data['url'] = $this->domain . $path;
        $insert = [
            'url'  => $path . $name,
            'type' => $type,
            'name' => $name
        ];
        $this->uploadModel->insertGetId($insert);
        return $data;
    }
}
