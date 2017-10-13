<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 微信开发接口配置
 */
class Api extends CI_Controller
{
    //定义token
    const TOKEN = "luckylsx";
    public function index()
    {
        //$wechatObj = new wechatCallbackapiTest();
        $this->createMenu();
        $echoStr = $this->input->get('echostr');
        if ($echoStr){
            $this->valid($echoStr);
        }else{
            $this->responseMsg();
        }
        //$this->load->library("Makemenu");
        //$this->makemenu->dolist();
    }
    //定义验证签名方法
    public function valid($echoStr)
    {
        if ($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = file_get_contents('php://input');

        //extract post data
        if (!empty($postStr)){

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
            if(!empty( $keyword ))
            {
                $msgType = "text";
                $contentStr = "Welcome to wechat world!";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }else{
                echo "Input something...";
            }

        }else {
            echo "";
            exit;
        }
    }
    //验证签名
    public function checkSignature()
    {
        //$signature = $_GET["signature"];
        $signature = $this->input->get("signature");
        //$timestamp = $_GET["timestamp"];
        $timestamp = $this->input->get("timestamp");
        //$nonce = $_GET["nonce"];
        $nonce = $this->input->get("nonce");

        $token = self::TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    public function createMenu(){
        $access_token = $this->getAccessToken(APPID,APPSECRET);

        $url = 'https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token='.'<span style="font-family: Arial, Helvetica, sans-serif;">'.$access_token.'</span>';
        $meaus['button']  = array();
        #菜单一
        $button[] = array(
            "name"=> "商家",
            "sub_button"=> array(
                array(
                    "type"=> "view",
                    "name"=> "管理入口",
                    //"url"=> BASE_DOMAIN."view/index"
                    "url"=> site_url()."view/index"
                )
            )
        );
        #菜单二
        $button[] = array(
            "name"=> "用户",
            "sub_button"=> array(
                array(
                    "type"=> "view",
                    "name"=> "去首页",
                    "url"=> site_url()."view/index"
                ),
                array(
                    "type"=> "view",
                    "name"=> "去会员中心",
                    //"url"=> BASE_DOMAIN."member/view/index"
                    "url"=> site_url()."member/view/index"
                ),
            )
        );
        #菜单三
        $button[] = array(
            "name"=> "快速付款",
            "sub_button"=> array(
                array(
                    "type"=> "view",
                    "name"=> "安卓扫一扫",
                    //"url" => TRADE_DOMAIN."sao/view/index/"
                    "url" => site_url()."sao/view/index/"
                ),
                array(
                    "type"=> "location_select",
                    "name"=> "发送位置",
                    "key"=> "rselfmenu_2_0"
                )
            )
        );
        $meaus['button'] = $button;
        $meaus['matchrule'] = array(
            #"group_id"=>"2",
            #"sex"=>"1",
            #"country"=>"中国",
            #"province"=>"广东",
            #"city"=>"广州",
            "client_platform_type"=>"2", # 1 苹果 2 安卓 3 其他手机
            #"language"=>"zh_CN"
        );
        $res = $this->curl->simple_post($url,json_encode($meaus,JSON_UNESCAPED_UNICODE));
        var_dump($res);exit;
    }
    //获取access_token
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
}