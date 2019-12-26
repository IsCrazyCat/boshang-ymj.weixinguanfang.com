<?php


class WeixinModel {

    /**
     * 微信推送过来的数据或响应数据
     * @var array
     */
    private $data = array();
    private $token = 'weixintoken'; 
    private $access_token = '';
    private $config = array();
    private $curl = null;

    /**
     * 构造方法，用于实例化微信SDK
     * @param string $token 微信开放平台设置的TOKEN
     */
    public function __construct() {
        import("@/Net.Curl");
        $this->curl = new Curl();
    }
    
    public function mass($data,$shop_id = 0){
        $token = $this->getToken($shop_id);
        $url = 'http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token='.$token.'&type=thumb';
        $result = $this->curl->post($url,array('media'=>'@'.BASE_PATH.'/attachs/'.$data['photo']));
        $result = json_decode($result,true);
    
        if($result['errcode']){
             return  $result['errcode'].$result['errmsg'];
        }
        
        $msg['articles']= array(
            array(
                'thumb_media_id'     => $result['media_id'],
                'author'            => $_SERVER['HTTP_HOST'],
                'title'             => $data['title'],  
                'content_source_url'=> $data['url'],
                'content'           => $data['contents'],
                'show_cover_pic'    => 1,
            ),
        );
        $msg = json_encode($msg);
        $this->curl->post('https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token='.$token,$msg);
        if($result['errcode']){
             return  $result['errcode'].$result['errmsg'];
        }
        return true;
    }
    
  

    public function tmplmesg($data)
    {
        $site_token = $this->getSiteToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$site_token}";
        $result = $this->curl->post($url, json_encode($data) );
        $result = (array)json_decode($result);
        if($result['errcode']){
            return false;
        }
        return true;
    }
    /*
     * 账号后台模板ID 
     * @param  string $short_id 模板库模板ID
     * @return string 账号后台模板ID
     */
    public function getTmplId($short_id)
    {
        $site_token = $this->getSiteToken();
        $url = "https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token={$site_token}";
        $result = $this->curl->post($url, json_encode(array('template_id_short'=>$shop_id)));
        $result = (array)json_decode($result);
        if($result['errcode']){
            return false;
        }
        return $result['template_id'];
    }

    public function getToken($shop_id=0) {
        return  $this->getSiteToken();
        
//        if(!$shop_id) return  $this->getSiteToken();
//
//        return $this->getShopToken($shop_id);
    }
    
    private function getShopToken($shop_id){ //获取商家的TOKEN
        if(!$data = D('Shopweixinaccess')->getToken($shop_id)){
         
            $details = D('Shopdetails')->find($shop_id);
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' .
                    $details['app_id'] . '&secret=' .
                    $details['app_key'];
            $result = $this->curl->get($url);
            $result = json_decode($result, true);
            if (!empty($result['errcode'])) return false;
           // exit($result['errmsg']);
            $data = $result['access_token'];
            D('Shopweixinaccess')->setToken($shop_id, $data);
        }
        return $data;
    }

	

	public function admin_wechat_client($shop_id=0)
    {
        static $clients = array();
		if($weixin_admin = D('Shopdetails')->find($shop_id)){
			include_once "Baocms/Lib/Action/Weixin/wechat.class.php";
			$client = new WechatClient($weixin_admin['app_id'], $weixin_admin['app_key']);
		}
        return $client;
    }
    public function wechat_client()
    {
        include_once BASE_PATH."/Baocms/Lib/Action/Weixin/wechat.class.php";
        $client = new WechatClient('wxea78884ef0a0a7a3', '5f3e872e294bd51d6f0f0722952d8ce8');
        return $client;
    }
    
    private function getSiteToken(){ //获取主站的TOKEN
//        $this->config = D('Setting')->fetchAll();
        return getWxAccessToken();
//        $cache = cache(array('type' => 'File', 'expire' => 7000));
//        if (!$data = $cache->get($this->token)) {
//            $this->config = D('Setting')->fetchAll();
//            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' .
//                    $this->config['weixin']['appid'] . '&secret=' .
//                    $this->config['weixin']['appsecret'];
//            //var_dump($this->config['weixin']);
//            $result = $this->curl->get($url);
//            $result = json_decode($result, true);
//            if (!empty($result['errcode'])) return;
//            $data = $result['access_token'];
//            $cache->set($this->token, $data);
//        }
//        return $data;
    }

    public function getCode($soure_id,$type){ //生成二维码
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $this->getSiteToken();
        $str = "";
        $detail = D('Weixinqrcode')->where(array('soure_id'=>$soure_id,'type'=>$type))->find();
        if(!empty($detail)){
            $str = $detail['id'];
        }else{
            $id = D('Weixinqrcode')->add(array('soure_id'=>$soure_id,'type'=>$type));
            $str = $id;
        }
        
        $data = array(
            'action_name' => 'QR_LIMIT_SCENE',
            'action_info' =>array(
                'scene' => array(
                    'scene_id' => $str,
                ),
            ),
        );
        $datastr = json_encode($data);
        $result = $this->curl->post($url, $datastr);
        $result = json_decode($result, true);
        
        if ($result['errcode']) {
            return false;
        }
        $ticket = urlencode($result['ticket']);
        $imgurl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=". $ticket;
        return $imgurl;
    }
    public function getCode2($soure_id,$type){ //生成二维码
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $this->getSiteToken();
        $str = "";
        $detail = D('Weixinqrcode')->where(array('soure_id'=>$soure_id,'type'=>$type))->find();
        if(!empty($detail)){
            $str = $detail['id'];
        }else{
            $id = D('Weixinqrcode')->add(array('soure_id'=>$soure_id,'type'=>$type));
            $str = $id;
        }

        $data = array(
            'action_name' => 'QR_LIMIT_SCENE',
            'action_info' =>array(
                'scene' => array(
                    'scene_id' => $str,
                ),
            ),
        );
        $datastr = json_encode($data);
        $result = $this->curl->post($url, $datastr);
        $result = json_decode($result, true);

        if ($result['errcode']) {
            return false;
        }
        $ticket = urlencode($result['ticket']);
        $imgurl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=". $ticket;
//        if(checkFile($imgurl))
        return $imgurl;
    }
    
    
    public function weixinmenu($data,$shop_id = 0 ) {

        $datas = array();
        foreach ($data['button'] as $key => $val) {
            if (!empty($val)) {
                $local = array(
                    'name' => urlencode($val),
                );
                foreach ($data['child'][$key] as $k => $v) {
                    if (!empty($v['name'])) {
                        $local['sub_button'][] = array(
                            'type' => 'view',
                            'name' => urlencode($v['name']),
                            'url' => $v['url'],
                        );
                    }
                }
                $datas[] = $local;
            }
        }

        $datastr = urldecode(json_encode(array('button'=>$datas)));
        //file_put_contents('/www/web/mantuo/public_html/Baocms/Lib/Model/bb.txt',$datastr);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->getToken($shop_id);
        $result = $this->curl->post($url, $datastr);
        $result = json_decode($result, true);
		//file_put_contents('/www/web/mantuo/public_html/Baocms/Lib/Model/bb.txt',$result);
        if ($result['errcode'] != 0) {
            return false;
        }
        return true;
    }

    //此TOKEN 是由网站分配
    public function init($token) {

        if (!empty($_GET['echostr'])) {
          //  $this->auth($token) || exit;
            exit($_GET['echostr']);
        } else {
            $xml = file_get_contents("php://input");
            if (!empty($xml)) {
                $xml = new SimpleXMLElement($xml);

                $xml || exit;

                foreach ($xml as $key => $value) {
                    $this->data[$key] = strval($value);
                }
            }
        }
    }

    /**
     * 获取微信推送的数据
     * @return array 转换为数组后的数据
     */
    public function request() {
        return $this->data;
    }

    /**
     * * 响应微信发送的信息（自动回复）
     * @param  string $to      接收用户名
     * @param  string $from    发送者用户名
     * @param  array  $content 回复信息，文本信息为string类型
     * @param  string $type    消息类型
     * @param  string $flag    是否新标刚接受到的信息
     * @return string          XML字符串
     */
    public function response($content, $type = 'text', $flag = 0) {
        /* 基础数据 */
        $this->data = array(
            'ToUserName' => $this->data['FromUserName'],
            'FromUserName' => $this->data['ToUserName'],
            'CreateTime' => NOW_TIME,
            'MsgType' => $type,
        );

        /* 添加类型数据 */
        $this->$type($content);

        /* 添加状态 */
        $this->data['FuncFlag'] = $flag;
        /* 转换数据为XML */
        $xml = new SimpleXMLElement('<xml></xml>');
        $this->data2xml($xml, $this->data);
        exit($xml->asXML());
    }

    /**
     * 回复文本信息
     * @param  string $content 要回复的信息
     */
    private function text($content) {
        $this->data['Content'] = $content;
    }

    /**
     * 回复音乐信息
     * @param  string $content 要回复的音乐
     */
    private function music($music) {
        list(
                $music['Title'],
                $music['Description'],
                $music['MusicUrl'],
                $music['HQMusicUrl']
                ) = $music;
        $this->data['Music'] = $music;
    }

    /**
     * 回复图文信息
     * @param  string $news 要回复的图文内容
     */
    private function news($news) {
        $articles = array();

        foreach ($news as $key => $value) {
            list(
                    $articles[$key]['Title'],
                    $articles[$key]['Description'],
                    $articles[$key]['PicUrl'],
                    $articles[$key]['Url']
                    ) = $value;
            if ($key >= 9) {
                break;
            } //最多只允许10调新闻
        }
        $this->data['ArticleCount'] = count($articles);
        $this->data['Articles'] = $articles;
    }

    /**
     * 数据XML编码
     * @param  object $xml  XML对象
     * @param  mixed  $data 数据
     * @param  string $item 数字索引时的节点名称
     * @return string
     */
    private function data2xml($xml, $data, $item = 'item') {
        foreach ($data as $key => $value) {
            /* 指定默认的数字key */
            is_numeric($key) && $key = $item;

            /* 添加子元素 */
            if (is_array($value) || is_object($value)) {
                $child = $xml->addChild($key);
                $this->data2xml($child, $value, $item);
            } else {
                if (is_numeric($value)) {
                    $child = $xml->addChild($key, $value);
                } else {
                    $child = $xml->addChild($key);
                    $node = dom_import_simplexml($child);
                    $node->appendChild($node->ownerDocument->createCDATASection($value));
                }
            }
        }
    }

    /**
     * 对数据进行签名认证，确保是微信发送的数据
     * @param  string $token 微信开放平台设置的TOKEN
     * @return boolean       true-签名正确，false-签名错误
     */
    private function auth($token) {
        /* 获取数据 */
        $data = array($_GET['timestamp'], $_GET['nonce'], $token);
        $sign = $_GET['signature'];

        /* 对数据进行字典排序 */
        sort($data);

        /* 生成签名 */
        $signature = sha1(implode($data));
       // file_put_contents('/www/web/bao_baocms_cn/public_html/Baocms/Lib/Action/Weixin/bb.txt',$signature);
        return $signature === $sign;
    }


}