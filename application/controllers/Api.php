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
                    $contentStr = "欢迎订阅php自学开发！\n\r 每天进步一点，小学习大成就!请持续关注php自学开发。
                    回复以下内容有你想要的：
                    1:回复'天气'查看天气查询
                    2:回复'说'查看我想对你说...
                    3:回复新闻，查看今日新闻
                    4:回复引入查看音乐列表 回复相应列表数字 听音乐
                    5:上传你的位置，有更多惊喜...";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }
                break;
            case 'text':
                if (!empty($keyword)){
                       if($keyword=='天气'){
                            $contentStr = "今天天气很好...";
                            $msgType = 'text';
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                            break;
                        }else if($keyword=='说'){
                            $contentStr = "每天学习一点，你越牛逼就有越多的人尊重你！";
                            $msgType = 'text';
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                            break;
                        }else if($keyword=='新闻'){
                            $this->backNews($fromUsername,$toUsername,$time);
                            break;
                        }else if($keyword=='音乐'){
                            $contentStr = "欢迎来到php自学开发在线音乐点播教程\n\r歌曲列表如下：\n\r 1、那英-默 \n\r 2、G.M.E.喜欢你 \n\r 3、G.M.E.泡沫";
                            $msgType = 'text';
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                            break;
                       }else if(preg_match('/^[1-9](\d){0,2}$/',$keyword)){
                            if ($keyword=='1'){
                                $desc = "那英-默";
                            }else if ($keyword=='2'){
                                $desc = "G.M.E.喜欢你";
                            }else if ($keyword=='3'){
                                $desc = "G.M.E.泡沫";
                            }else{
                                $desc = "那英-默";
                            }
                            $musicTpl = '<xml>
                                        <ToUserName><![CDATA[%s]]></ToUserName>
                                        <FromUserName><![CDATA[%s]]></FromUserName>
                                        <CreateTime>%s</CreateTime>
                                        <MsgType><![CDATA[music]]></MsgType>
                                        <Music>
                                        <Title><![CDATA[音乐]]></Title>
                                        <Description><![CDATA[%s]]></Description>
                                        <MusicUrl><![CDATA[%s]]></MusicUrl>
                                        <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                                        </Music>
                                        </xml>';
                            $musicUrl = "http://wx-test.lylucky.com/mp3/{$desc}.mp3";
                            $resultStr = sprintf($musicTpl, $fromUsername, $toUsername, $time,$desc,$musicUrl,$musicUrl);
                            echo $resultStr;
                       }else if (preg_match('/^CXWZ([\x{4e00}-\x{9fa5}]+)/ui',$keyword,$res)){
                           $this->load->model("Location_model",'location');
                           $d = $this->location->getLocation($fromUsername);
                           $contentStr = "请点击下面链接查看位置详情\n\r"."http://api.map.baidu.com/place/search?query=".urlencode($res[1])."&location={$d['longitude']},{$d['latitude']}&radius=1000&output=html&coord_type=bd09";
                           $msgType = 'text';
                           $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                           echo $resultStr;
                       }else{
                           $url = "http://www.tuling123.com/openapi/api";
                           $data = "key=7aa2a54501124c25b9dd833735cf7605&info=".urlencode($keyword)."&userid={$fromUsername}";
                           $re = http_post($url,$data);
                           $content = json_decode($re,true);
                           if ($content['code']=='100000'){
                               $msgType = 'text';
                               $contentStr = $content['text'];
                           }elseif ($content['code']=='200000'){
                               $msgType = 'text';
                               $contentStr = $content['text'] . "点击下面链接查看图片：".
                               $content['url'];
                           }
                           $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr['text']);
                           echo $resultStr;
                       }
                }else{
                    echo "说点什么吧...";
                    exit;
                }
            case 'location':
                //获取经纬度
                $location_Y = $postObj->Location_Y;
                $location_X = $postObj->Location_X;
                $msgType = 'text';
                $contentStr = "您好！已经收到您上传的地理位置信息。\n\r 经度是:{$location_Y} \n\r 维度是:{$location_X}
                输入\"CXWZ位置\"，如：\"CXWZ肯德基\",即可查看该店最近的位置信息！";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
                $data = [
                    'longitude'=> $location_Y,
                    'latitude' => $location_X,
		    'join_time'=> date("Y-m-d H:i:s",time())
                ];
                $this->load->model("Location_model",'location');
                $this->location->saveLocation($data,$fromUsername);
                break;
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
        $a='CXWZ北京';
        preg_match('/^CXWZ([\X{4e00}-\x{9fa5}]+)/ui',$a,$res);
        $url = "http://www.tuling123.com/openapi/api";
        $data = "key=7aa2a54501124c25b9dd833735cf7605&info=".urlencode("你好")."&userid=1234";
        $re = http_post($url,$data);
        var_dump($re);
        die;
        echo "<pre>";
        print_r($d);
        echo "</pre>";
    }
}
