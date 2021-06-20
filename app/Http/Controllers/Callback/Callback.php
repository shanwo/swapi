<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers\Callback;

use App\Http\Controllers\Controller;

class Callback extends Controller
{
    public function __construct($request)
    {
        $this->request = $request;
        parent::__construct();
    }

    /**
     * 功能：对字符串进行加密处理
     * @param string $str
     * 需要加密的内容.
     * @param string $key
     * 加密字符串
     * @return string
     * 加密后的内容
     */
    public function passportEncrypt($str, $key)
    {
        srand((double)microtime() * 1000000);
        $encrypt_key = md5(rand(0, 32000));
        $ctr = 0;
        $tmp = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $encrypt_key[$ctr] . ($str[$i] ^ $encrypt_key[$ctr++]);
        }
        return base64_encode($this->passportKey($tmp, $key));
    }

    /**
     * 功能：对字符串进行解密处理
     * @param string $str
     * 需要解密的密文.
     * @param string $key
     * 加密字符串
     * @return string
     * 解出的内容
     */
    public function passportDecrypt($str, $key)
    {
        $str = $this->passportKey(base64_decode($str), $key);
        $tmp = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $md5 = $str[$i];
            $tmp .= $str[++$i] ^ $md5;
        }
        return $tmp;
    }

    /**
     *辅助函数
     */
    private function passportKey($str, $encrypt_key)
    {
        $encrypt_key = md5($encrypt_key);
        $ctr = 0;
        $tmp = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $str[$i] ^ $encrypt_key[$ctr++];
        }
        return $tmp;
    }
}
