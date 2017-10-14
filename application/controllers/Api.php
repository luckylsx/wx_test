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
        $echoStr = $this->input->get('echostr');
        if ($echoStr){
            $this->valid($echoStr);
        }else{
            $this->responseMsg();
        }
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
                $contentStr = "Welcome to wechat world!";
                $this->backMsg($postObj,$keyword,$fromUsername,$toUsername,$time,$textTpl);
                /*switch ($postObj->MsgType){
                    case 'event';
                        if ($postObj->Event=='subscribe'){
                            $msgType = "text";
                            $contentStr = "欢迎订阅php自学开发！\n\r 每天进步一点，小学习大成就!请持续关注php自学开发。
                        回复以下内容有你想要的：\n\r1:天气查询\n\r2:我想对你说...";
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                        }
                        break;
                    case 'text':
                        switch ($keyword){
                            case '1':
                                $contentStr = "今天天气很好...";
                                break;
                            case '2':
                                $contentStr = "每天学习一点，你越牛逼就有越多的人尊重你！";
                                break;
                            default:
                                break;
                        }
                        $msgType = 'text';
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                    default:
                        break;
                }*/
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
    protected function backMsg($postObj,$keyword,$fromUsername,$toUsername,$time,$textTpl)
    {
        $contentStr = "Welcome to wechat world!";
        switch ($postObj->MsgType){
            case 'event';
                if ($postObj->Event=='subscribe'){
                    $msgType = "text";
                    $contentStr = "欢迎订阅php自学开发！\n\r 每天进步一点，小学习大成就!请持续关注php自学开发。
                        回复以下内容有你想要的：\n\r1:天气查询\n\r2:我想对你说...";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }
                break;
            case 'text':
                switch ($keyword){
                    case '1':
                        $contentStr = "今天天气很好...";
                        break;
                    case '2':
                        $contentStr = "每天学习一点，你越牛逼就有越多的人尊重你！";
                        break;
                    default:
                        break;
                }
                $msgType = 'text';
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            default:
                break;
        }
    }
}