<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 自定义菜单功能
 */
class Makemenu
{
    public $menustr;
    public function __construct(){
    }
    public function init(){
        $this->dolist();
        return  $this->setmenu();
    }
    private function dolist(){
        $CI =& get_instance();
        $CI -> load ->model("Menu_model","menu");
        $plist = $CI->menu ->isplist();
        foreach($plist as $pid){
            $pidarr[] = $pid['pid'];
        }
        $list = $CI->menu ->maketree($CI->menu->getlist());
        foreach($list as $btn){
            if(in_array($btn['id'],$pidarr)){
                //生成不带key和url的链接作为父级菜单
                $btn_arr[$btn['id']] = array("type"=>$btn['menutype'],
                    "name"=>$btn['content']);
            }elseif($btn['pid'] == 0){
                //生成有操作的一级菜单
                $btn_arr[$btn['id']] = array("type"=>$btn['menutype'],
                    "name"=>$btn['content'],
                    "key"=>$btn['clickkey'],
                    "url"=>$btn['url']);
            }else{
                //生成子菜单
                $btn_arr[$btn['pid']]['sub_button'][] = array("type"=>$btn['menutype'],
                    "name"=>$btn['content'],
                    "key"=>$btn['clickkey'],
                    "url"=>$btn['url']);
            }
        }
        $btnarr['button'] = array_values($btn_arr);
        $r = $this->menustr = json_encode($btnarr,JSON_UNESCAPED_UNICODE);
        return $r;
    }
    private function setmenu(){
        $accesstoken = get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$accesstoken}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->menustr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $info = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $info;
    }
}