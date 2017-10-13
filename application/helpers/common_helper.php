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
    if (!function_exists('get_access_token')){
        function get_access_token(){
            //从微信服务器获取access_token  并保留一个小时
            $old_filename = APPPATH."cache/".md5(date("YmdH",time()-3600)).".php";
            @unlink($old_filename);
            $filename = APPPATH."cache/".md5(date("YmdH",time())).".php";
            if(is_file($filename)){
                $r = include($filename);
            }else{
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".APPID."&secret=".APPSECRET;
                $access_token =  file_get_contents($url);
                $res = "<?php return ".var_export(json_decode($access_token,1),1).";";
                file_put_contents($filename,$res);
                $r =  include($filename);
            }
            return ($r['access_token']);
        }
    }
}