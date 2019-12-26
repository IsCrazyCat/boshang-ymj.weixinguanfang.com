<?php
class weixin {
    public function init($payment) {
        define('WEIXIN_APPID', $payment['appid']);
        define('WEIXIN_MCHID', $payment['mchid']);
        define('WEIXIN_APPSECRET', $payment['appsecret']);
        define('WEIXIN_KEY',$payment['appkey']);
        //=======【证书路径设置】=====================================
        define('WEIXIN_SSLCERT_PATH', '../cert/apiclient_cert.pem');
        define('WEIXIN_SSLKEY_PATH', '../cert/apiclient_key.pem');
        //=======【curl代理设置】===================================
        define('WEIXIN_CURL_PROXY_HOST', "0.0.0.0"); //"10.152.18.220";

        define('WEIXIN_CURL_PROXY_PORT', 0); //8080;

        //=======【上报信息配置】===================================
        define('WEIXIN_REPORT_LEVENL', 1);
        require_once "weixin/WxPay.Api.php";
        require_once "weixin/WxPay.JsApiPay.php";
    }
    public function getCode($logs, $payment) {
        $this->init($payment);
        $tools = new JsApiPay();
        $openId = $tools->GetOpenid($logs);
        $input = new WxPayUnifiedOrder();
        $input->SetBody($logs['subject']);
        $input->SetAttach($logs['subject']);
        $input->SetOut_trade_no($logs['logs_id']);
        $logs['logs_amount'] = $logs['logs_amount'] *100;
        $input->SetTotal_fee("{$logs['logs_amount']}");
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($logs['subject']);
        $input->SetNotify_url(__HOST__ . U( 'wap/payment/respond', array('code' => 'weixin')));
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        $str = '<script>function jsApiCall()
	{
		WeixinJSBridge.invoke(
			\'getBrandWCPayRequest\',
			'.$jsApiParameters.',
			function(res){
                            if(res.err_msg ==\'get_brand_wcpay_request:ok\'){ 
                                location.href="'.U('wap/payment/yes',array('log_id'=>$logs['logs_id'])).'";
                            }
			}
		);
	}
	function callpay(){
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener(\'WeixinJSBridgeReady\', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent(\'WeixinJSBridgeReady\', jsApiCall); 
		        document.attachEvent(\'onWeixinJSBridgeReady\', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}</script>
<button   class="button button-block bg-dot button-big" type="button" onclick="callpay()" >立即支付</button>
        ';
        return $str;
    }
    public function getCode1($logs, $payment) {
        $this->init($payment);
        $tools = new JsApiPay();
//        $openId = 'oz6Qc6As2xPVK6lHxedoY5saIzuw';
        $openId = 'oz6Qc6MLDsiKlcwPMSyyUDMFFEW0';

        $input = new WxPayUnifiedOrder();
        $input->SetBody($logs['subject']);
        $input->SetAttach($logs['subject']);
        $input->SetOut_trade_no($logs['logs_id']);
        $logs['logs_amount'] = $logs['logs_amount'] *100;
        $input->SetTotal_fee("{$logs['logs_amount']}");
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($logs['subject']);
        $input->SetNotify_url(__HOST__ . U( 'wap/payment/respond', array('code' => 'weixin')));
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        $str = '<script>function jsApiCall()
	{
		WeixinJSBridge.invoke(
			\'getBrandWCPayRequest\',
			'.$jsApiParameters.',
			function(res){
                            if(res.err_msg ==\'get_brand_wcpay_request:ok\'){ 
                                location.href="'.U('wap/payment/yes',array('log_id'=>$logs['logs_id'])).'";
                            }
			}
		);
	}
	function callpay(){
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener(\'WeixinJSBridgeReady\', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent(\'WeixinJSBridgeReady\', jsApiCall); 
		        document.attachEvent(\'onWeixinJSBridgeReady\', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}</script>
<button   class="button button-block bg-dot button-big" type="button" onclick="callpay()" >立即支付</button>
        ';
        return $str;
    }



    public function respond() {
        $xml = file_get_contents("php://input");
        if (empty($xml))
            return false;
        $xml = new SimpleXMLElement($xml);
        if (!$xml)
            return false;
        $data = array();
        foreach ($xml as $key => $value) {
            $data[$key] = strval($value);
        }
        if (empty($data['return_code']) || $data['return_code'] != 'SUCCESS') {
            return false;
        }
        if (empty($data['result_code']) || $data['result_code'] != 'SUCCESS') {
            return false;
        }
        if (empty($data['out_trade_no'])){
            return false;
        }
        ksort($data);
        reset($data);
        $payment = D('Payment')->getPayment('weixin');
        /* 检查支付的金额是否相符 */
        if (!D('Payment')->checkMoney($data['out_trade_no'], $data['total_fee'])) {
            return false;
        }
        $sign = array();
        foreach ($data as $key => $val) {
            if ($key != 'sign') {
                $sign[] = $key . '=' . $val;
            }
        }
        $sign[] = 'key=' . $payment['appkey'];
        $signstr = strtoupper(md5(join('&', $sign)));
        if ($signstr != $data['sign']){
            return false;
        }    
        D('Payment')->logsPaid($data['out_trade_no']);
        return true;
    }
}