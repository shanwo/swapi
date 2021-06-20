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
    public function __construct()
    {
        $this->oss = new \Qcloud\Cos\Client([
            'region' => '<Region>',
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
     * @param $name
     * 返回当前文件的后缀
     * @return mixed|string|null
     */
    protected function getExt($name)
    {
        $ext = null;
        $intercept = mb_substr($name, '-6');
        $str = strpos($intercept, '.');
        if ($str !== false) {
            $exts = explode('.', $intercept);
            $ext = end($exts);
            $ext = strtolower($ext);
        }
        return $ext;
    }

    /**
     * @return array
     * 上传文件
     */
    public function update()
    {
        $file = $_FILES['file'] ?? $this->params['file'];
        $fileName = dechex(NOW_TIME * 10000);
        $ext = $this->getExt($file['name']);
        $type = $this->type($ext);
        if ($ext) {
            $fileName = $fileName . '.' . $ext;
        }
        $pathName = YII_ENV . '/' . $type . date('/Y/m/');
        $receive = $this->oss->uploadFile($this->bucket, $pathName . $fileName, $file['tmp_name']);
        if ($receive['info']['http_code'] == 200) {
            $this->data['url'] = $this->ossUrl . $pathName . $fileName;
            $this->data['type'] = $type;
        } else {
            $this->data['msg'] = '上传失败';
        }
        return $this->serviceResponse();
    }
}
