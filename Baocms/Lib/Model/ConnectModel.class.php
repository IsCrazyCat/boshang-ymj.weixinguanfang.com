<?php
class  ConnectModel extends Model{
    protected $pk   = 'connect_id';
    protected $tableName =  'connect';
    
    public function getConnectByOpenid($type,$open_id){
        
        return $this->find(array("where"=>array(
            'type' => $type,
            'open_id' => $open_id
        )));     
    }
	
	 public function getConnectByUid($uid){
        
        return $this->find(array("where"=>array(
            'uid' => $uid
        )));     
    }
	
	
     /**
     * qq获取用户信息
     * @param $client_id
     * @param $access_token
     * @param $openid
     * @return array 用户的信息数组
     * */
    public function user_info($client_id,$openid,$access_token){
        $url = 'https://graph.qq.com/user/get_user_info?oauth_consumer_key='.$client_id.'&access_token='.$access_token.'&openid='.$openid.'&format=json';
        $str = $this->visit_url($url);
        $arr = json_decode($str,true);
      return $arr;

		
		
    }
	
	  /**
     * 微信获取用户信息
     * @param $client_id
     * @param $access_token
     * @param $openid
     * @return array 用户的信息数组
     * */
    public function wx_user_info($openid,$access_token){
	//$url = 'https://api.weixin.qq.com/cgi-bin/user/info??access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
       $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid;
	 //  $url = 'https://api.weixin.qq.com/sns/auth?access_token='.$access_token;
	  
        $str = $this->visit_url($url);
        $arr = json_decode($str,true);
     return $arr;
	

		
		
    }
	
	/**
     * 请求URL地址，得到返回字符串
     * @param $url qq提供的api接口地址
     * */
    public function visit_url($url){
        static $cache = 0;
        //判断是否之前已经做过验证
        if($cache === 1){
            $str = $this->curl($url);
        }elseif($cache === 2){
            $str = $this->openssl($url);
        }else{
            //是否可以使用cURL
            if(function_exists('curl_init')){
                $str = $this->curl($url);
                $cache = 1;
                //是否可以使用openssl
            }elseif(function_exists('openssl_open') && ini_get("allow_fopen_url")=="1"){
                $str = $this->openssl($url);
                $cache = 2;
            }else{
                die('请开启php配置中的php_curl或php_openssl');
            }
        }
        return $str;
    }
	
	/**
     * 通过curl取得页面返回值
     * 需要打开配置中的php_curl
     * */
    private function curl($url){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);//允许请求的内容以文件流的形式返回
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);//禁用https
        curl_setopt($ch,CURLOPT_URL,$url);//设置请求的url地址
        $str = curl_exec($ch);//执行发送
        curl_close($ch);
        return $str;
    }
    /**
     * 通过file_get_contents取得页面返回值
     * 需要打开配置中的allow_fopen_url和php_openssl
     * */
    private function openssl($url){
        $str = file_get_contents($url);//取得页面内容
        return $str;
    }
    
    
}