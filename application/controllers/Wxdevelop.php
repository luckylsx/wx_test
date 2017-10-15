<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 微信开发
 */
class Wxdevelop extends CI_Controller
{
    const APPID = 'wx3f2070ceecbb4b40';
    const APPSECRET = '0cc0e0e4bcac4049ac0e0308ddf8c320';
    protected $access_token;
    public function __construct()
    {
        parent::__construct();
        $this->config->load('wxtest', TRUE);
        $this->wxdata = $this->config->item('wx','wxtest');
    }

    /**
     * 获取accesstoken
     */
    public function getAccessToke()
    {
        //加载缓存驱动
        $this->load->driver('cache', array('adapter' => 'redis', 'backup' => 'file'));
        $key = "lucky_wx_access_token";
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
        $access_token = $this->cache->save($key, $token['access_token'], 1.5*60*60);
        return $access_token;
    }

    public function uploadLogo()
    {
        $data['access_token'] = $this->getAccessToke();
        $this->load->view("upload_logo",$data);
    }
    public function do_upload()
    {
        $config['upload_path']      = './upload/';
        $config['allowed_types']    = 'gif|jpg|png';
        $config['max_size']     = 100;
        $config['max_width']    = 1024;
        $config['max_height']   = 768;
        $config['file_name']   = uniqid();

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('blob'))
        {
            $error = array('error' => $this->upload->display_errors());
            echo $error;
//            $this->load->view('upload_form', $error);
        }else
        {
            $data = array('upload_data' => $this->upload->data());

            //$this->load->view('upload_success', $data);
        }
    }

    public function test()
    {
        echo time()+strtotime(date('Y-m-d',strtotime('+1 day')));
    }

    public function show()
    {
        $this->load->view("upload_logo");
    }
    public function upload()
    {
        $config['upload_path']      = './uploads/card_logo/';
        $config['allowed_types']    = 'gif|jpg|png';
        $config['max_size']     = 100;
        $config['max_width']    = 1024;
        $config['max_height']   = 768;
        $config['file_name']   = uniqid();

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('logo'))
        {
            $error = array('error' => $this->upload->display_errors());
            var_dump($error);
//            $this->load->view('upload_form', $error);
        }else{
            $data = array('upload_data' => $this->upload->data());
        }
        //var_dump($data);
        $logo_url = $data['upload_data']['full_path'];
        $access_token = $this->getAccessToke();
        $url = "https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=" .$access_token;
        $file = array( "buffer"=>new CURLFile($logo_url));
//        $file['buffer'] =new CURLFile($logo_url);
        $upStatus = http_post($url,$file);
        $logo = json_decode($upStatus,true);
        $logo_url = $logo['url'];
        $card_id = $this->create_card($access_token,$logo_url);
        $this->send_card($access_token,$card_id);
    }
    public function create_card($access_token,$logo_url)
    {
        $url = "https://api.weixin.qq.com/card/create?access_token={$access_token}";
        $bodyTpl =file_get_contents("test.json");
        $body = sprintf($bodyTpl,$logo_url);
        $status = http_post($url,$body);
        $card = json_decode($status,true);
        $card_id = $card['card_id'];
        return $card_id;
//        echo "<pre>";
//        var_dump($status);
//        echo "</pre>";

    }
    public function send_card($access_token,$card_id)
    {
        $url="https://api.weixin.qq.com/card/mpnews/gethtml?access_token={$access_token}";
        /*$data = '{
        "card_id":'.$card_id.'
        }';*/
        $wxcardTpl = '{
           "touser":[
            "%s"
           ],
            "wxcard": {"card_id":"%s"}
            "msgtype":"wxcard"
        }';
        $wxcard = sprintf($wxcardTpl,'o1eypwn9DxGuI7iB2yk0xTrp5OUw',$card_id);
        $status = http_post($url,$wxcard);
        $d = json_decode($status,true);
        var_dump($d);


    }

    public function te()
    {
        echo time();
        echo "<br>";
        echo strtotime("+3 days");
    }


}