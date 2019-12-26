<?php
class AliSms {
	private $gatewayUrl = "http://gw.api.taobao.com/router/rest";
	private $method="alibaba.aliqin.fc.sms.num.send";
	private $format="json";
	private $v="2.0";
	private $sign_method="md5";
	private $appKey;
	private $secretKey;
	private $sms_type = "normal";
	private $sms_free_sign_name = '';
	private $sms_param = array();//短信接收号码
	private $rec_num = '';//短信模版ID
	private $sms_template_code = '';
	
	private function _send(){
		$param = array(
			'method'				=>	$this->method,
			'format'				=>	$this->format,
			'app_key'				=>	$this->appKey,
			'timestamp'				=>	date("Y-m-d H:i:s"),
			'v'						=>	$this->v,
			'sign_method'			=>	$this->sign_method,
			'sms_type'				=>	$this->sms_type,
			'sms_free_sign_name'	=>	$this->sms_free_sign_name,
			'sms_param'				=>	json_encode($this->sms_param),
			'rec_num'				=>	$this->rec_num,
			'sms_template_code'		=>	$this->sms_template_code,
		);
		if(!$this->sms_param){
			unset($param['sms_param']);
		}
		$param['sign'] = $this->_sign(array_merge($param));
		$result = $this->_sendSms($param);
		return $result;
	}
	
	private function _sign($param){
		ksort($param);

		$sign = $this->secretKey;
		foreach ($param as $k => $v){
			$sign .= "$k$v";
		}
		$sign .= $this->secretKey;
        
		return strtoupper(md5($sign));
	}
	
	private function _sendSms($param){
		$url = $this->gatewayUrl . "?" . http_build_query($param);
		$ch = curl_init();
		$timeout = 5;
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$file_contents = curl_exec($ch);
		curl_close($ch);
		return $file_contents;
	}
	
	public function send($phone=''){
		if($phone!==''){
			$this->phone($phone);
		}
		$sms_id = $this->rec_sms_id;
		$result = $this->_send();
		$json = json_decode($result,true);
		if($json!==null){
			foreach($json as $v){
				$json = $v;
			}
		}else{
			return array('status'=>0,'info'=>'返回内容解析错误','data'=>$result);
		}
		if($json['code']){
			$json['status'] = 0;
			$json['info'] = $json['sub_msg'];
			D('Dayusms')->where(array('sms_id' => $sms_id))->save(array('status' => 0,'info'=>$json['info']));
			return $json;
		}else{
			$json['status'] = 1;
			$json['info'] = $json['msg'] ? $json['msg'] : "发送成功";
			D('Dayusms')->where(array('sms_id' => $sms_id))->save(array('status' => 1,'info'=>$json['info']));
			return $json;
		}
	}
	
	public function __construct($param1 = "",$param2 = ""){
		if($param1!=="" && $param2!=="" && is_string($param1) && is_string($param2)){
			$this->appkey($param1);
			$this->secret($param2);
		}
	}
	
	public function appkey($appKey=""){
		if($appKey) $this->appKey = $appKey;
		return $this;
	}
	
	public function secret($secretKey=""){
		if($secretKey) $this->secretKey = $secretKey;
		return $this;
	}
	
	public function sign($sign_name = ''){
		$this->sms_free_sign_name = $sign_name;
		return $this;
	}
	
	public function data($data = array()){
		$this->sms_param = $data;
		return $this;
	}
	
	public function phone($phone=''){
		$this->rec_num = $phone;
		return $this;
	}
	public function sms_id($sms_id=''){
		$this->rec_sms_id = $sms_id;
		return $this;
	}
	
	public function code($code=''){
		$this->sms_template_code = $code;
		return $this;
	}
}
