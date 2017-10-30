<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 微信开发接口配置
 */
class Wxtest extends CI_Controller
{
    //定义token
    const TOKEN = "luckylsx";
    //测试
    const APPID = 'wx3f2070ceecbb4b40';
    //订阅号
    //const APPID = 'wxfbce10c1ed40ade0';
    //测试
    const APPSECRET = '0cc0e0e4bcac4049ac0e0308ddf8c320';
    //订阅号
    //const APPSECRET = 'aa6d1ff646c282ca7529cc3690eb054b';

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
            $event = $postObj->Event;
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
            $this->backMsg($postObj,$keyword,$fromUsername,$toUsername,$time,$textTpl,$event);
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
    protected function backMsg($postObj,$keyword,$fromUsername,$toUsername,$time,$textTpl,$event)
    {
        $contentStr = "Welcome to wechat world!";
        switch ($postObj->MsgType){
            case 'event':
                if ($event == 'subscribe'){
                    $msgType = "text";
                    $contentStr = "欢迎订阅php自学开发！\n\r 每天进步一点，小学习大成就!请持续关注php自学开发。
                    回复以下内容有你想要的：
                    1:回复'天气'查看天气查询
                    2:回复'说'查看我想对你说...
                    3:回复新闻，查看今日新闻
                    4:回复引入查看音乐列表 回复相应列表数字 听音乐";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }else if ($event == 'TEMPLATESENDJOBFINISH'){
                    $msgType = "text";
                    if ($postObj->Status=='success'){
                        $contentStr = "用户接收成功";
                    }else{
                        $contentStr = "用户接收失败";
                    }
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
                    }else if($keyword=='模板'){
                        $this->send_tmp($fromUsername);
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
                        $contentStr = json_decode($re)->text;
                        $msgType = 'text';
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
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
                $contentStr = "您好！已经收到您上传的地理位置信息。\n\r 经度是:{$location_Y} \n\r 维度是:{$location_X} \n\r 输入您关心的地方，即可查看其最近的位置信息！";
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
    /**
     * 获取accesstoken
     */
    public function getAccessToke()
    {
        //加载缓存驱动
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        $key = md5(self::APPID . self::APPSECRET);
        //access_token未过期
        if ($access_token = $this->cache->get($key))
        {
            return $access_token;
        }
        //access_token过期重新获取
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".self::APPID."&secret=".self::APPSECRET;
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        $tokenData = file_get_contents($url,false,stream_context_create($arrContextOptions));
        $token = json_decode($tokenData,true);
        $access_token = $this->cache->save($key, $token['access_token'], 7000);
        return $access_token;
    }
    /**
     * 获取模板消息列表
     */
    public function gettemplatelist($access_token)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token={$access_token}";
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        $d = file_get_contents($url,false,stream_context_create($arrContextOptions));
        $template = json_decode($d,true);
        if ($template){
            return $template['template_list'];
        }else{
            return false;
        }

    }

    public function send_tmp($oppenid)
    {
        $access_token = $this->getAccessToke();
        $tempTpl = '{
           "touser":"%s",
           "template_id":"%s",
           "url":"http://www.soso.com",  
           "data":{
                   "first": {
                       "value":"%s！",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"%s！",
                       "color":"#173177"
                   }
           }
       }';
        $template = $this->gettemplatelist($access_token);
        if (!$template){
            return "消息模板列表获取失败";
        }
        $tmp = $template[0];
        $contentStr = sprintf($tempTpl,$oppenid,$tmp['template_id'],$tmp['title'],$tmp['content']);
        $send_url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";
        $d = http_post($send_url,$contentStr);
        $status = json_decode($d,true);
        $this->load->helper("array");
        if (element('errcode',$status)==0){
            $this->logger("模板消息发送成功！");
        }
    }
    public function logger($content)
    {
        $logSize=100000;
        $log="log.txt";
        if(file_exists($log) && filesize($log)>$logSize){
            unlink($log);
        }
        file_put_contents($log,date("Y-m-d H:i:s",time())." ".$content."\n",FILE_APPEND);
    }
    public function check()
    {
        if (strtolower($_SERVER['REQUEST_METHOD'])=='post'){
            session_start();

            $data = $this->input->post();
            //var_dump($data);
            //die;
            $co = $data['co'];
            $se = $data['se'];
            $t = strtotime("+1 day");
            //echo $t;
            //die;
            setcookie("co",$co,$t);
            $_SESSION['se'] = $se;

        }
        $this->load->view("test");
    }
    public function getinfo(){
        session_start();
        echo $_COOKIE['co'] , $_SESSION['se'];
    }
}
