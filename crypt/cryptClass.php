<?php

class Crypt
{
    private $key;

    public function __construct()
    {
        $this->key = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a0");
    }

    public function enCrypt($str)
    {
        $key = $this->key;

        $plaintext = $str;

        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key,
            $plaintext, MCRYPT_MODE_CBC, $iv);

        $ciphertext = $iv . $ciphertext;

        $ciphertext_base64 = base64_encode($ciphertext);

        return  [
                $ciphertext_base64,
                $iv_size
        ];
    }

    public function deCrypt($ciphertext_base64, $iv_size)
    {  
        $key = $this->key;

        $ciphertext_dec = base64_decode($ciphertext_base64);

        # 初始向量大小，可以通过 mcrypt_get_iv_size() 来获得
        $iv_dec = substr($ciphertext_dec, 0, $iv_size);

        # 获取除初始向量外的密文
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);

        # 可能需要从明文末尾移除 0
        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key,
            $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);

        return  $plaintext_dec;
    }
}
$a = 1;
$b = 2;
$cryptInfo = $a.'|'.$b;

$crypt = new Crypt();
$result = $crypt->enCrypt($cryptInfo);
$result = $crypt->deCrypt($result[0], $result[1]);
echo $result;

$result = explode('|', $result);
var_dump($result);