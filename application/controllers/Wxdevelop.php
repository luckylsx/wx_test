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
        $this->create_card($access_token,$logo_url);
    }
    public function create_card($access_token,$logo_url)
    {
        $url = "https://api.weixin.qq.com/card/create?access_token={$access_token}";
        $body = '{
  "card": {
              "card_type": "GROUPON",
              "groupon": {
                  "base_info": {
                      "logo_url":  '.$logo_url.',
                      "brand_name": "微信餐厅",
                      "code_type": "CODE_TYPE_TEXT",
                      "title": "132元双人火锅套餐",
                      "color": "Color010",
                      "notice": "使用时向服务员出示此券",
                      "service_phone": "020-88888888",
                      "description": "不可与其他优惠同享\n如需团购券发票，请在消费时向商户提出\n店内均可使用，仅限堂食",
                      "date_info": {
                          "type": "DATE_TYPE_FIX_TIME_RANGE",
                          "begin_timestamp": 1397577600,
                          "end_timestamp": 1472724261
                      },
                      "sku": {
                          "quantity": 500000
                      },
                      "use_limit":100,
                      "get_limit": 3,
                      "use_custom_code": false,
                      "bind_openid": false,
                      "can_share": true,
                      "can_give_friend": true,
                      "location_id_list": [
                          123,
                          12321,
                          345345
                      ],
                      "center_title": "顶部居中按钮",
                      "center_sub_title": "按钮下方的wording",
                      "center_url": "www.qq.com",
                      "custom_url_name": "立即使用",
                      "custom_url": "http://www.qq.com",
                      "custom_url_sub_title": "6个汉字tips",
                      "promotion_url_name": "更多优惠",
                      "promotion_url": "http://www.qq.com",
                      "source": "大众点评"
                  },
                   "advanced_info": {
                       "use_condition": {
                           "accept_category": "鞋类",
                           "reject_category": "阿迪达斯",
                           "can_use_with_other_discount": true
                       },
                       "abstract": {
                           "abstract": "微信餐厅推出多种新季菜品，期待您的光临",
                           "icon_url_list": [
                               "http://mmbiz.qpic.cn/mmbiz/p98FjXy8LacgHxp3sJ3vn97bGLz0ib0Sfz1bjiaoOYA027iasqSG0sj  piby4vce3AtaPu6cIhBHkt6IjlkY9YnDsfw/0"
                           ]
                       },
                       "text_image_list": [
                           {
                               "image_url": "http://mmbiz.qpic.cn/mmbiz/p98FjXy8LacgHxp3sJ3vn97bGLz0ib0Sfz1bjiaoOYA027iasqSG0sjpiby4vce3AtaPu6cIhBHkt6IjlkY9YnDsfw/0",
                               "text": "此菜品精选食材，以独特的烹饪方法，最大程度地刺激食 客的味蕾"
                           },
                           {
                               "image_url": "http://mmbiz.qpic.cn/mmbiz/p98FjXy8LacgHxp3sJ3vn97bGLz0ib0Sfz1bjiaoOYA027iasqSG0sj piby4vce3AtaPu6cIhBHkt6IjlkY9YnDsfw/0",
                               "text": "此菜品迎合大众口味，老少皆宜，营养均衡"
                           }
                       ],
                       "time_limit": [
                           {
                               "type": "MONDAY",
                               "begin_hour":0,
                               "end_hour":10,
                               "begin_minute":10,
                               "end_minute":59
                           },
                           {
                               "type": "HOLIDAY"
                           }
                       ],
                       "business_service": [
                           "BIZ_SERVICE_FREE_WIFI",
                           "BIZ_SERVICE_WITH_PET",
                           "BIZ_SERVICE_FREE_PARK",
                           "BIZ_SERVICE_DELIVER"
                       ]
                   },
                  "deal_detail": "以下锅底2选1（有菌王锅、麻辣锅、大骨锅、番茄锅、清补 凉锅、酸菜鱼锅可选）：\n大锅1份 12元\n小锅2份 16元 "
              }
          }
        }';
        $status = http_post($url,$body);
        var_dump($status);

    }


}