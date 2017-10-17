<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 会员卡
 */
class MemberCard extends CI_Controller
{
    const APPID = "wx3f2070ceecbb4b40";
    const APPSECRET = "0cc0e0e4bcac4049ac0e0308ddf8c320";

    /**
     * 获取access_token
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

    public function setMenu()
    {
        $access_token = $this->getAccessToke();
        //创建菜单url
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
        //查询菜单url
        $seMenu = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$access_token}";
        $oldMenu = json_decode(file_get_contents($seMenu),JSON_UNESCAPED_UNICODE);
        /*$test = '{
 	                  "button":[
                        {	
                            "type":"click",
                            "name":"今日歌曲",
                            "key":"V1001_TODAY_MUSIC" 
                        },
                        { 
                            "name":"菜单",
                            "sub_button":[
                            {	
                                "type":"view",
                                "name":"搜索",
                                "url":"http://www.soso.com/"
                            },{
                                "type":"click",
                                "name":"赞一下我们",
                                "key":"V1001_GOOD"
                                }]
                     }]
                    }';*/
        //$menu = json_decode($test,true);
//        var_dump($menu);
        $cmenu = '{
                    "tag":0,
                    "con":{
                        "type":"scancode_push",
                        "name":"看一看",
                        "key":"V1001_GODF"
                    }
                   }';
        $cme = json_decode($cmenu,true);

        $oldMenu['menu']['button'][$cme['tag']] =  $cme['con'];
        $menu = $oldMenu['menu'];
//        var_dump(array_reverse($menu['button']));
//        die;
//        var_dump(json_encode($oldMenu,JSON_UNESCAPED_UNICODE));
//        die;
        $status =   http_post($url,json_encode($menu,JSON_UNESCAPED_UNICODE));

        $d = json_decode($status,true);
        var_dump($d);
    }

    public function delMenu($menuId)
    {
        $access_token = $this->getAccessToke();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delconditional?access_token={$access_token}";
        $delmenuId = [ "menuid"=> $menuId ];
        $status = http_post($url,json_encode($delmenuId,JSON_UNESCAPED_UNICODE));
        var_dump($status);
    }

    public function test()
    {
        echo sha1("limuz888");
    }
    public function json_response()
    {
        echo json_encode(array('msgtype'=>'end'));
        /*$data = ["msgtype"=>"end"];
        $res = json_encode($data,JSON_UNESCAPED_UNICODE);
        echo $res;
        return $res;*/
    }
    /**
     * 展示上传页面，上传会员卡logo
     */
    public function show()
    {
        $this->load->view("upload_logo");
    }
    /**
     * 会员卡上传方法
     */
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
            return;
//            $this->load->view('upload_form', $error);
        }
        $data = array('upload_data' => $this->upload->data());
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
        //$this->send_card($access_token,$card_id);
    }

    /**
     * 创建会员卡
     * @param $access_token
     * @param $logo_url
     * @return mixed
     */
    public function create_card($access_token,$logo_url)
    {
        $url = "https://api.weixin.qq.com/card/create?access_token={$access_token}";
        $bodyTpl =file_get_contents("test.json");
        $body = sprintf($bodyTpl,$logo_url);
        $status = http_post($url,$body);
        $card = json_decode($status,true);
        echo "创建会员卡信息：<br>";
        var_dump($card);
        $card_id = $card['card_id'];
        return $card_id;
//        echo "<pre>";
//        var_dump($status);
//        echo "</pre>";

    }

    /**
     * 投放会员卡
     * @param $access_token
     * @param $card_id
     */
    public function send_card($access_token,$card_id)
    {
        $preUrl = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token={$access_token}";
        //$url="https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token={$access_token}";
        /*$data = '{
        "card_id":'.$card_id.'
        }';*/
        $wxcardTpl = '{ 
                        "touser":["%s","%s"], 
                         "wxcard":{"card_id":"%s"},
                         "msgtype":"wxcard" 
                      }';
        //"o1eypwpEdZ3V4iHSaSNN797lto88"
        //$wxcardTpl = file_get_contents('card.json');
        $wxcard = sprintf($wxcardTpl,'o1eypwn9DxGuI7iB2yk0xTrp5OUw','o1eypwpEdZ3V4iHSaSNN797lto88',$card_id);
        $status = http_post($preUrl,$wxcard);
        $d = json_decode($status,true);
        echo "发送会员卡信息<br>";
        var_dump($d);
    }

    /**
     * 获取店铺信息
     */
    public function getPolist()
    {
        echo $_SERVER['HTTP_HOST'];
        die;
        $access_token = $this->getAccessToke();
        $url = "https://api.weixin.qq.com/cgi-bin/poi/getpoilist?access_token={$access_token}";
        $pol = '{
                    "begin":0,
                    "limit":2
                }';
        $data = http_post($url,$pol);
        $d = json_decode($data,true);
        var_dump($d);
    }

}