<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Create_Card extends CI_Controller
{
    protected $appid;
    protected $appsecret;
    protected $openid;
    const TOKEN = "luckylsx";
    public function __construct()
    {
        parent::__construct();
        $this->appid = 'wx3f2070ceecbb4b40';
        $this->appsecret = '0cc0e0e4bcac4049ac0e0308ddf8c320';
        $this->openid = 'o1eypwn9DxGuI7iB2yk0xTrp5OUw';
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

    public function getAccessToken() {
        $url = "https://api.weixin.qq.com/cgi-bin/token";
        $data = "grant_type=client_credential" . "&appid=" . $this->appid  . "&secret=" .  $this->appsecret ;
        $resp = http_post( $url, $data );
        $token = json_decode($resp,true);
        $access_token = $token['access_token'];
        //http请求方式: GET https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
        $ourl = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$this->openid.'&lang=zh_CN';
        $udata = http_post($ourl,'');
        $user = json_decode($udata,true);
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        //var_dump($user);
        die;
        //截取token
        $token=strstr( $resp, "\":\"" );
        $token=trim( $token, "\":\"" );
        $token=strstr( $token, "\",\"", true );
        echo $token;
        return $token;
    }
    //定义验证签名方法
    public function valid($echoStr)
    {
        if ($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    public function upload_logo(){
        // 上传图片获得logo链接
        $this->load->helper("url");
        $config['upload_path']  = './uploads/image/';
        $config['allowed_types']    = 'gif|jpg|png';
        $config['file_name'] = time().uniqid();
        //var_dump($_FILES['blob']);
        $this->load->library('upload', $config);
        //$blob = new CURLFile('C:/Users/Adminisrator/Desktop/11.jpg');
        $this->upload->do_upload('logo');
        if ( ! $this->upload->do_upload('logo'))
        {
            $error = array('error' => $this->upload->display_errors());
            var_dump($error);
            //$this->load->view('upload_form', $error);
        }
        else
        {
            $data = array('upload_data' => $this->upload->data());
            $this->load->view('upload_success', $data);
        }
        die;
        $url = "https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=" . urlencode( $access_token );
        $file = array( "buffer"=>new CURLFile('C:/Users/Adminisrator/Desktop/test.jpg') );
        $resp = http_post( $url, $file );
        $logo_url = strstr($resp,"\":\"");
        $logo_url = trim($logo_url,"\":\"");
        $logo_url = substr($logo_url,0,-2);
        return $logo_url;

    }
    public function upload()
    {
        $this->load->view("upload_logo");
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