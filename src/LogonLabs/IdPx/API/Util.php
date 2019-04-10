<?php

namespace LogonLabs\IdPx\API;
/*
 *  Logon Labs Util Call
 */

class Util {
    public static function encrypt($passphrase, $value) {
        if (is_object($value) || is_array($value)) {
            $value = json_encode($value);
        }
        $salt = openssl_random_pseudo_bytes(32);
        $key = openssl_pbkdf2($passphrase, $salt, 32,1000);
        $iv =  openssl_random_pseudo_bytes(16);
        $encrypted_data = openssl_encrypt($value, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $hex = bin2hex($salt). bin2hex($iv) .bin2hex($encrypted_data);
        $return = base64_encode(pack('H*',$hex));
        return $return;
    }
}