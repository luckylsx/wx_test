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
        //$url = "http://php.net/manual/zh/function.pathinfo.php";
        $url = "http://localhost/study/wx_test/index.php/MemberCard/test";
        echo $url;
        p(parse_url($url));
        echo $_SERVER['HTTP_HOST'];
        echo "<br>";
        echo $_SERVER['REQUEST_URI'];
        echo "<br>";
        $a = 9;
        try{
            if ($a>6){
                throw new Exception("变量不存在");
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }

        //echo sha1("limuz888");
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
    public function send_card()
    {
        //$card_id = "p1eypwuo_irv71ztx6nTV_VF3-yI";
        $card_id = "p1eypwnudlxyL-vz3sh7riBFYSKM";
        $access_token = $this->getAccessToke();
        //$preUrl = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token={$access_token}";
        $preUrl = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token={$access_token}";
        //$url="https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token={$access_token}";
        /*$data = '{
        "card_id":'.$card_id.'
        }';*/
//        '{
//                        "touser":[%s,%s],
//                         "wxcard":{"card_id":"%s"},
//                         "msgtype":"wxcard"
//                      }';
        $wxcardTpl = [
            "touser"=>'o1eypwn9DxGuI7iB2yk0xTrp5OUw',
            "wxcard"=>["card_id"=>"p1eypwnudlxyL-vz3sh7riBFYSKM"],
            "msgtype"=>"wxcard"
        ];
        //"o1eypwpEdZ3V4iHSaSNN797lto88"
        //$wxcardTpl = file_get_contents('card.json');
        //$wxcard = sprintf($wxcardTpl,'o1eypwn9DxGuI7iB2yk0xTrp5OUw','o1eypwpEdZ3V4iHSaSNN797lto88',$card_id);
        $wxcard = json_encode($wxcardTpl);
        var_dump($wxcard);
//        die;
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
    /**
     * 查询code接口，查询卡的状态
     */
    public function checkCode()
    {
//        $access_token = $this->getAccessToke();
//        $url = "https://api.weixin.qq.com/card/code/get?access_token={$access_token}";
        $wh = [
            'card_id'=>"p1eypwuo_irv71ztx6nTV_VF3-yI",
            'code'=>"280710807188",
            'check_consume'=>true,
        ];
        echo urldecode($wh['code']);
        die;
        $cdata = json_encode($wh);
        $cstatus = http_post($url,$cdata);
        $d = json_decode($cstatus,true);
        var_dump($d);
    }

    public function consume()
    {
        $access_token = $this->getAccessToke();
        $url = "https://api.weixin.qq.com/card/code/consume?access_token={$access_token}";
        $cw = ['code'=>"280710807188"];
        $d = http_post($url,json_encode($cw));
        var_dump($d);
    }

    /**
     * 更新会员（卡券）信息
     */
    public function upMemCard()
    {
        $access_token = $this->getAccessToke();
        $url = "https://api.weixin.qq.com/card/membercard/updateuser?access_token={$access_token}";
        $ud = [
            "code"=>"139625574332",
            "card_id"=>"p1eypwnudlxyL-vz3sh7riBFYSKM",
            "background_pic_url"=>"http://wx.qlogo.cn/mmopen/DQgGDOqYFIbFaWV0EoC6k7iap5gHgX4VxLNULx2s1vYBLKoiajAjZ288xTNianxEpoeT6SDdJtNBY8FNwvr3szUqN8ds3FLddl5/0",
            "bonus"=>"3000",
            "add_bonus"=>"30",
            //"balance" => "3000",
            //"add_balance"=>"-30",
            "record_balance"=>"购买焦糖玛琪朵一杯，扣除金额30元。",
            "custom_field_value1"=>"xxxxx",
            "custom_field_value2"=>"xxxxx",
            "notify_optional"=>[
                "is_notify_bonus"=>true,
                "is_notify_balance"=>true,
                "is_notify_custom_field1"=>false
            ]
        ];
        $upda = http_post($url,json_encode($ud,true));
        p($upda);
    }

    /**
     * 更改会员卡信息
     */
    public function updateCardMsg()
    {
        $access_token = $this->getAccessToke();
        $url = "https://api.weixin.qq.com/card/update?access_token={$access_token}";
        $updata = [
            "card_id"=>"p1eypwnudlxyL-vz3sh7riBFYSKM",
            "member_card"=>[
                "base_info"=>[
                    "logo_url"=>"http://wx.qlogo.cn/mmopen/DQgGDOqYFIbFaWV0EoC6k7iap5gHgX4VxLNULx2s1vYBLKoiajAjZ288xTNianxEpoeT6SDdJtNBY8FNwvr3szUqN8ds3FLddl5/0",
                   "color"=>"Color010",
                   "notice"=>"使用时向服务员出示此券",
                   "service_phone"=> "020-88888888",
                   "description"=> "不可与其他优惠同享\n如需团购券发票，请在消费时向商户提出\n店内均可使用，
                   仅限堂食\n餐前不可打包，餐后未吃完，可打包\n本团购券不限人数，建议2人使用，
                   超过建议人数须另收酱料费5元/位\n本单谢绝自带酒水饮料",
                   "location_id_list"=> [123, 12321, 345345]
                ],
                "bonus_cleared"=>"每年12月30号积分清0。",
                "bonus_rules"=> "每消费1元增加1积分。",
                "prerogative"=> "XX会员可享有全场商品8折优惠。"
            ]
        ];
        //p(json_encode($updata,JSON_UNESCAPED_UNICODE));
        $chda = http_post($url,json_encode($updata,JSON_UNESCAPED_UNICODE));
        $upstatus = json_decode($chda,true);
        p($upstatus);
    }

    /**
     * 查询用户已领取的卡券
     */
    public function getUserCard()
    {
        $access_token = $this->getAccessToke();
        $url = "https://api.weixin.qq.com/card/user/getcardlist?access_token={$access_token}";
        $u = ['openid'=>'o1eypwn9DxGuI7iB2yk0xTrp5OUw'];
        $cd = http_post($url,json_encode($u));
        $card = json_decode($cd,true);
        echo "<pre>";
        print_r($card);
        echo "</pre>";
    }

    public function selectCard()
    {
        $access_token = $this->getAccessToke();
        $url = "https://api.weixin.qq.com/card/batchget?access_token={$access_token}";
        $pd = [
            'offset'=>0,
            "count"=>10,
            "status_list"=>[
                "CARD_STATUS_VERIFY_OK","CARD_STATUS_DISPATCH",'CARD_STATUS_VERIFY_FAIL'
            ]
        ];
        $card = http_post($url,json_encode($pd));
        $cardMsg = json_decode($card,true);
        echo "<pre>";
        print_r($cardMsg);
        echo "</pre>";
    }
    /**
     * 查询关注用户信息
     * @return string
     */
    public function getUserlist()
    {
        $access_token = $this->getAccessToke();
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$access_token}";
        $u = file_get_contents($url);
        $udata = json_decode($u,true);
        if (isset($udata['errcode'])){
            return "用户列表获取失败！";
        }
        $ulist = $udata['data'];
        foreach($ulist['openid'] as $openid){
            $userMsgUrl = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN ";
            $usermsg = file_get_contents($userMsgUrl);
            $umsg = json_decode($usermsg,true);
            echo "<pre>";
            print_r($umsg);
            echo "</pre>";
        }
    }

    public function checkCardBonus()
    {
        $access_token = $this->getAccessToke();
//        echo $access_token;
//        die;
        $url = "https://api.weixin.qq.com/card/membercard/userinfo/get?access_token={$access_token}";
        $chda = [
            "card_id" => "p1eypwnudlxyL-vz3sh7riBFYSKM",
            "code" => "139625574332",
        ];
        $da = http_post($url,json_encode($chda));
        $checkStatus = json_decode($da,true);
        p($checkStatus);

    }

    /**
     * 创建货架接口
     */
    public function showShave()
    {
        $access_token = $this->getAccessToke();
        $url = "https://api.weixin.qq.com/card/landingpage/create?access_token={$access_token}";
        $da = [
            "banner"=>"http://wx.qlogo.cn/mmopen/DQgGDOqYFIbFaWV0EoC6k7iap5gHgX4VxLNULx2s1vYBLKoiajAjZ288xTNianxEpoeT6SDdJtNBY8FNwvr3szUqN8ds3FLddl5/0",
            "page_title"=>"惠城优惠大派送",
            "can_share"=>true,
            "scene"=>"SCENE_NEAR_BY",
            "card_list"=>[
                [
                    "card_id" => "p1eypwnudlxyL-vz3sh7riBFYSKM",
                    "thumb_url" => "http://wx.qlogo.cn/mmopen/DQgGDOqYFIbFaWV0EoC6k7iap5gHgX4VxLNULx2s1vYBLKoiajAjZ288xTNianxEpoeT6SDdJtNBY8FNwvr3szUqN8ds3FLddl5/0"
                ],[
                    "card_id" => "p1eypwuo_irv71ztx6nTV_VF3-yI",
                    "thumb_url"=>"http://wx.qlogo.cn/mmopen/DQgGDOqYFIbFaWV0EoC6k7iap5gHgX4VxLNULx2s1vYBLKoiajAjZ288xTNianxEpoeT6SDdJtNBY8FNwvr3szUqN8ds3FLddl5/0"
                ]
            ]
        ];
        $shave = http_post($url,json_encode($da,JSON_UNESCAPED_UNICODE));
        $status = json_decode($shave,true);
        p($status);

    }

    /**
     * 设置所属行业
     */
    public function setIndustry()
    {
        $a = urlencode("你好，这是urlencode测试方法！");
        echo urldecode($a);
    }

}