<?php
class WeixinJSSDK {
    private $appId;
    private $appSecret;

    public function __construct($appId, $appSecret) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);

        $signPackage = array(
          "appId"     => $this->appId,
          "nonceStr"  => $nonceStr,
          "timestamp" => $timestamp,
          "url"       => $url,
          "signature" => $signature,
          "rawString" => $string
        );
        return $signPackage; 
    }

  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  

  private function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例    ;
	
    if (($data = json_decode(file_get_contents("jsapi_ticket.json"),true)) && ($data['expire_time'] < time())) {
      $accessToken = $this->getAccessToken();
      // 如果是企业号用以下 URL 获取 ticket
      // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      $res = json_decode($this->httpGet($url), true);
      $ticket = $res['ticket'];
      if ($ticket) {
        $data['jsapi_ticket'] = $ticket;
		$data['expire_time'] = time() + 7000;
		$fp = fopen("jsapi_ticket.json", "w");
        fwrite($fp, json_encode($data));
        fclose($fp);
      }
    } else {
      $ticket = $data['jsapi_ticket'];
    }

    return $ticket;
  }

  private function getAccessToken() {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
	return getWxAccessToken();
//      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
//      $res = json_decode($this->httpGet($url));
//      $access_token = $res->access_token;
//
//    return $access_token;
  }

  private function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
  }

    public function getCardApiTicket()
    {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例    ;
		
		if (($data = json_decode(file_get_contents("card_ticket.json"),true)) && ($data['expire_time'] < time())) {
            $accessToken = $this->getAccessToken();
			
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=wx_card&access_token=$accessToken";
            $res = json_decode($this->httpGet($url), true);
			
            $ticket = $res['ticket'];
            if ($ticket) {
                $data['wx_card_ticket'] = $ticket;
                $data['expire_time'] = time() + 7000;
				$fp = fopen("card_ticket.json", "w");
				fwrite($fp, json_encode($data));
				fclose($fp);
            }
        } else {
            $ticket = $data['wx_card_ticket'];
        }
        return $ticket;         
    }

    public function getCardSignPackage($card_id)
    {
        $apiTicket = $this->getCardApiTicket();
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $params = array($apiTicket, $timestamp, $nonceStr, $card_id);
        sort($params, SORT_STRING);
        $string = implode('', $params); 
        $signature = sha1($string);
        $signPackage = array(
          "nonce_str"  => $nonceStr,
          "timestamp" => $timestamp,
          "apiTicket"   => $apiTicket,
          "signature" => $signature,
          "rawString" => $string,
		  "ext" => '{"timestamp":"'.$timestamp.'","nonce_str":"'.$nonceStr.'","signature":"'.$signature.'"}'
        ); 
        return $signPackage;
    }
}
