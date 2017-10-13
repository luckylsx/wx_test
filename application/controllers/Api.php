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
}