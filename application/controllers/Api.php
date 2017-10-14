<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 微信开发接口配置
 */
class Api extends CI_Controller
{
    //定义token
    const TOKEN = "luckylsx";

    public function __construct()
    {
        parent::__construct();
        $this->load->helper("url");
    }
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
            $this->backMsg($postObj,$keyword,$fromUsername,$toUsername,$time,$textTpl);
            /*if(!empty( $keyword ))
            {
                $contentStr = "Welcome to wechat world!";
                $this->backMsg($postObj,$keyword,$fromUsername,$toUsername,$time,$textTpl);
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
            }else{
                echo "Input something...";
            }*/

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
                    $contentStr = "欢迎订阅php自学开发！\n\r 每天进步一点，小学习大成就!请持续关注php自学开发。\n\r回复以下内容有你想要的：\n\r1:天气查询\n\r2:我想对你说...";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }
                break;
            case 'text':
                if (!empty($keyword)){
                    switch ($keyword){
                        case '1':
                            $contentStr = "今天天气很好...";
                            $msgType = 'text';
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                            break;
                        case '2':
                            $contentStr = "每天学习一点，你越牛逼就有越多的人尊重你！";
                            $msgType = 'text';
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                            break;
                        case '新闻':
                            $this->backNews($fromUsername,$toUsername,$time);
                            break;
                        case '音乐':
                            $contentStr = "欢迎来到php自学开发在线音乐点播教程\n\r歌曲列表如下：\n\r 1、周杰伦-告白气球 \n\r 2、汪峰-北京 \n\r 3、那英-默";
                            $msgType = 'text';
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                            break;
                        default:
                            break;
                    }
                }else{
                    echo "说点什么吧...";
                    exit;
                }
            default:
                break;
        }
    }
    protected function backNews($fromUsername,$toUsername,$time)
    //public function backNews()
    {
        //$fromUsername=1;$toUsername=2;$time=time();
        $textTplHeader = '<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>%d</ArticleCount>
                    <Articles>';
        $textTplItem = '<item>
                    <Title><![CDATA[%s]]></Title> 
                    <Description><![CDATA[%s]]></Description>
                    <PicUrl><![CDATA[%s]]></PicUrl>
                    <Url><![CDATA[%s]]></Url>
                    </item>';
        $textTplFoot = '</Articles>
                    </xml>';
        $this->load->model("news_model",'news');
        $d = $this->news->getNewslist();
        $textItem = '';
        foreach ($d as $item) {
            $textItem .= sprintf($textTplItem,$item['title'],$item['description'],$item['picUrl'],$item['url']);
        }
        $textHeader = sprintf($textTplHeader,$fromUsername,$toUsername,$time,count($item));
        $resultStr = $textHeader . $textItem . $textTplFoot;
        /*$title1 = "今日新闻一...";
        $description1 = "华为手机国庆后价格暴跌，这4款旗舰跌至“白菜价”！";
        $picurl1 ='http://wx-test.lylucky.com/uploads/image/1.jpg';
        $url1 = "www.news.baidu.com";
        $title2 = "今日新闻二...";
        $description2 = "iPhone8售价跌破五千，黄牛血亏，曾经苹果的辉煌将不复存在";
        $picurl2 = 'http://wx-test.lylucky.com/uploads/image/2.jpg';
        $url2 = "www.news.qq.com";
        $resultStr = sprintf($textTplFoot, $fromUsername, $toUsername, $time, $title1,$description1,$picurl1,$url1,$title2,
        $description2,$picurl2,$url2);*/
        echo $resultStr;
    }

    public function test()
    {
        $this->load->model("news_model",'news');
        $d = $this->news->getNewslist();
        echo "<pre>";
        print_r($d);
        echo "</pre>";
    }
}
