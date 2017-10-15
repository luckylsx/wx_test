<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 自定义菜单
 */
class Menu extends CI_Controller
{
    const APPID = 'wx3f2070ceecbb4b40';
    const APPSECRET = '0cc0e0e4bcac4049ac0e0308ddf8c320';
    /**
     * 自定义菜单
     */
    public function setMenu()
    {
        $access_token = $this->getAccessToken();
        var_dump($access_token);
        $this->logger($access_token);
        die;
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $menuTpl = '{
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
                                   "type":"click",
                                   "name":"今日天气",
                                   "key":"todat_weathor"
                                },
                                {
                                   "type":"click",
                                   "name":"关于我们",
                                   "key":"V1001_GOOD"
                                }]
                          },
                          {
                                "type":"view",
                                "name":"搜索",
                                "url":"http://www.soso.com/"
                          }]
                    }';
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        die;
//        var_dump($menuTpl);
        //{"errcode":0,"errmsg":"ok"}
        $menu = http_post($url,$menuTpl);
        var_dump($menu);
        //$menu = file_get_contents($url,false, stream_context_create($arrContextOptions));
        $d = json_decode($menu,true);
//        var_dump($d);
        die;
        if ($d['errmsg']){
            echo "自定义菜单创建成功";
        }else{
            echo "自定义菜单创建失败";
        }
    }
    /**
     * 获取access_token
     */
    public function getAccessToken()
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
        $access_token = $this->cache->save($key, $token['access_token'], 2*60*60);
        return $access_token;
    }

    /**
     * 记录日志
     * @param $content
     */
    public function logger($content)
    {
        $logSize=100000;
        $log="log.txt";
        if(file_exists($log) && filesize($log)>$logSize){
            unlink($log);
        }
        file_put_contents($log,date("Y-m-d H:i:s",time())." ".$content."\n",FILE_APPEND);
}

}