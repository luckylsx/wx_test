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
        $data = ["msgtype"=>"end"];
        $res = json_encode($data,JSON_UNESCAPED_UNICODE);
        var_dump($res);
        return $res;
    }
}