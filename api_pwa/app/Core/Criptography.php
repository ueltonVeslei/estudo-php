<?php
class Criptography {

    public static function sha1($str){
        $token = sha1($str);
        return $token;
    }

    /**
     * action -> apenas os encrypt ou decrypt
     * string -> string a ser criptografada
     */
    public static function encrypt_decrypt($action, $string) {
        $output = false;
        
        $encrypt_method = "AES-256-CBC";
        $secret_key = Config::KEY;
        $secret_iv = Config::IV;
        
        // hash
        $key = hash("sha256", $secret_key);
        
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash("sha256", $secret_iv), 0, 16);
        
        if( $action == "encrypt" ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
        }
        else if( $action == "decrypt" ){
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        
        return $output;
    }

}

 