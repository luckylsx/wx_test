<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 微信开发
 */
class Wxdevelop extends CI_Controller
{
    protected $wxdata;
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
        //https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        $key = "user_access_token";
        $access_token = $this->cache->get($key);
        //如果access_token存在，直接返回
        if ($access_token){
            $token_arr = $this->str2arr($access_token);
            return $token_arr;
        }
        //不存在，重新获取access_token
        $wx = $this->wxdata;
        //获取appid
        $appid = $wx['appID'];
        //appsecret
        $appsecret = $wx['appsecret'];
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
        $contents = file_get_contents($url);
        $token_arr = $this->str2arr($contents);
        // 将access_token缓存两个小时
        $this->cache->save($key, $contents, 2*60*60);
        return $token_arr;
    }

    public function str2arr($str)
    {
        $arr=[];
        $tokenData = substr($str,1,-1);
        //eval("\$arr = ".$access_token.';');
        $d = $token = explode(",",$tokenData);
        $token = explode(',',$d[0]);
        $tk = explode(":",$token[0]);
        $arr[substr($tk[0],1,-1)] = substr($tk[1],1,-1);
        $expire = explode(',',$d[1]);
        $ex = explode(":",$expire[0]);
        $arr[substr($ex[0],1,-1)] = substr($ex[1],1,-1);
        return $arr;
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


}