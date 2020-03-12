<?php

/**
 * Php Crypt function
 */
class PhpCrypt
{
	private  static $m_php_mcrypt_decrypt_key = 'e1e121da-7a1f-471e-abc3-b9f077baf6c4';

	public static function PHP_Encrypt($string)
	{
		$key = md5(self::$m_php_mcrypt_decrypt_key);
		$crypttext = base64_encode( openssl_encrypt($string, "AES-256-CBC", $key, null, substr(md5($key), 0, 16)) );

        return trim(self::Safe_B64Encode($crypttext));
	}

	public static function PHP_Decrypt($encrypted)
	{
		$key = md5(self::$m_php_mcrypt_decrypt_key);
		$crypttexttb = self::Safe_B64Decode($encrypted);

		return rtrim( openssl_decrypt(base64_decode($crypttexttb), "AES-256-CBC", $key, 0, substr(md5($key), 0, 16)) );
	}

	// 安全编码
	public static  function Safe_B64Encode($string)
	{
		$data = base64_encode($string);
		$data = str_replace(array('+','/','='), array('-','_',''),$data);
		return $data;
	}

	// 安全解码
	public static function Safe_B64Decode($string)
	{
		$data = str_replace(array('-','_'),array('+','/'),$string);
		$mod4 = strlen($data) % 4;
		if ($mod4) {
			$data .= substr('====', $mod4);
		}
		return base64_decode($data);
	}

    /**
     * 随机码
     *
     * @param int  $length
     * @param bool $bOnlyNum
     *
     * @return string
     */
	public  static function Random_Pwd( $length = 4 , $bOnlyNum=false)
	{
		// 产生随机密码
		// !@#$%^&*()-_ []{}<>~`+=,.;:/?|
        if($bOnlyNum)
            $chars    = '123456789';
        else
            $chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ23456789';

        $password = '';
        $len      = strlen($chars);
        for ($i = 0; $i < $length; $i++)
        {
            $password .= $chars[mt_rand(0, $len - 1)];
        }

		return $password;
	}
}