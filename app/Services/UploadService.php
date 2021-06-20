<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

class UploadService extends Service
{
    private $oss;
    private $bucket = 'cdn-1305937303';
    private $ossUrl = 'https://cdn.shanwo.cc/';

    public function __construct()
    {
        $this->oss = new \Qcloud\Cos\Client([
            'region' => 'ap-beijing',
            'credentials' => array(
                'secretId' => '<SecretId>',
                'secretKey' => '<SecretKey>'
            )
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
     * @return array
     * 上传文件
     */
    public function update($request)
    {
        $file = $request->file('file');
        $fileName = dechex(microtime(true) * 10000);
        $ext = $file->getClientOriginalExtension();//得到后缀；
        $type = $this->type($ext);
        $pathName = env('APP_ENV') . '/' . $type . date('/Y/m/');
        if ($ext) {
            $fileName = $fileName . '.' . $ext;
        }
        $receive = $this->oss->upload($this->bucket, $pathName . $fileName, $file['tmp_name']);
        if ($receive['info']['http_code'] == 200) {
            $this->data['url'] = $this->ossUrl . $pathName . $fileName;
            $this->data['type'] = $type;
        } else {
            $this->data['msg'] = '上传失败';
        }
        return $this->serviceResponse();
    }
}
