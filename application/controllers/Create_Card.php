<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Create_Card extends CI_Controller
{
    public function getAccessToken( $appid, $secret ) {
        $url = "https://api.weixin.qq.com/cgi-bin/token";
        $data = "grant_type=client_credential" . "&appid=" . $appid  . "&secret=" .  $secret ;
        $resp = http_post( $url, $data );
        //截取token
        $token=strstr( $resp, "\":\"" );
        $token=trim( $token, "\":\"" );
        $token=strstr( $token, "\",\"", true );
        return $token;
    }
    public function upload_logo($access_token){
        // 上传图片获得logo链接
        $url = "https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=" . urlencode( $access_token );
        $file = array( "buffer"=>new CURLFile('C:/Users/lucky.li/Desktop/test.jpg') );
        $resp = http_post( $url, $file );
        $logo_url = strstr($resp,"\":\"");
        $logo_url = trim($logo_url,"\":\"");
        $logo_url = substr($logo_url,0,-2);
        return $logo_url;

    }
    public function create(){
        //创建卡卷
        $this->config->load("wxtest",TRUE);
        $toData = $this->config->item("wx","wxtest");
        //获取access_token
        $access_token = $this->getAccessToken($toData['appID'],$toData['appsecret']);
        // 上传图片获得logo链接
        $logo_url = $this->upload_logo($access_token);
        $url = "https://api.weixin.qq.com/card/create?access_token=" . urlencode( $access_token );
//        echo $logo_url;
        $this->load->helper('url');
        $file = file_get_contents(base_url()."test.json");
        $file = sprintf($file,$logo_url);
        var_dump($file);
        // 上传的json格式文件，文件头可能会出现多余的特殊字符
        while( $file[0] != '{' ){
            $file = substr( $file, 1 );
        }

//        echo "$url<br>";
        $resp = http_post( $url, $file );
        var_dump($resp);
//        echo "$resp<br>";
    }
}