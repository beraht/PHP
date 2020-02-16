<?php
namespace app\common\aes;

/**
 * aes 加密 解密类库
 *
 */

class Aes {
 
    private $hex_iv = '00000000000000000000000000000000'; # converted JAVA byte code in to HEX and placed it here
 
    private $key = '397e2eb61307109f6e68006ebcb62f98'; #Same as in JAVA
 
    function __construct() {
        $this->key = '397e2eb61307109f6e68006ebcb62f98';
        $this->key = hash('sha256', $this->key, true);
    }

 
    public function encrypt($input)
    {
        $data = openssl_encrypt($input, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $this->hexToStr($this->hex_iv));
        $data = base64_encode($data);
        return $data;
    }
 
    public function decrypt($input)
    {
        $decrypted = openssl_decrypt(base64_decode($input), 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $this->hexToStr($this->hex_iv));
        return $decrypted;
    }
 
    /*
      For PKCS7 padding
     */
 
    private function addpadding($string, $blocksize = 16) {
 
        $len = strlen($string);
 
        $pad = $blocksize - ($len % $blocksize);
 
        $string .= str_repeat(chr($pad), $pad);
 
        return $string;
 
    }
 
    private function strippadding($string) {
 
        $slast = ord(substr($string, -1));
 
        $slastc = chr($slast);
 
        $pcheck = substr($string, -$slast);
 
        if (preg_match("/$slastc{" . $slast . "}/", $string)) {
 
            $string = substr($string, 0, strlen($string) - $slast);
 
            return $string;
 
        } else {
 
            return false;
 
        }
 
    }
 
    function hexToStr($hex)
    {
 
        $string='';
 
        for ($i=0; $i < strlen($hex)-1; $i+=2)
 
        {
 
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
 
        }
 
        return $string;
    }
 
}


?>