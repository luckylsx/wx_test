<?php
/**
 * Created by PhpStorm.
 * User: lucky.li
 * Date: 2017/10/12
 * Time: 17:10
 */
if (!function_exists('http_post')){
    function http_post( $url, $data ) {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        $resp = curl_exec( $ch );
        curl_close( $ch );
        return $resp;
    }
}