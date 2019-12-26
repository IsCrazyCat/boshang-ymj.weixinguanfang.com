<?php 

define('API_ENDPOINT', 'https://api-3t.paypal.com/nvp');
define('USE_PROXY',FALSE);
define('PROXY_HOST', '127.0.0.1');
define('PROXY_PORT', '808');
define('PAYPAL_URL', 'https://www.paypal.com/cgi-bin/webscr&cmd=_express-checkout&token=');

$API_Endpoint =API_ENDPOINT;
class paypal
{




    /**
     * 生成支付代码
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
    function getCode($order, $payment)
    {

        $token = '';
        $serverName = $_SERVER['SERVER_NAME'];
        $serverPort = $_SERVER['SERVER_PORT'];
        $url=dirname('http://'.$serverName.':'.$serverPort.$_SERVER['REQUEST_URI']);
        $paymentAmount=$order['logs_amount'];
        $currencyCodeType=$payment['paypal_ec_currency'];
        $paymentType='Sale';
        $data_order_id      = $order['logs_id'];


        $returnURL =urlencode($url.'&code=paypal_ec&currencyCodeType='.$currencyCodeType.'&paymentType='.$paymentType.'&paymentAmount='.$paymentAmount.'&invoice='.$data_order_id);
        $cancelURL =urlencode($url."&paymentType=".$paymentType );

        $nvpstr="&Amt=".$paymentAmount."&PAYMENTACTION=".$paymentType."&ReturnUrl=".$returnURL."&CANCELURL=".$cancelURL ."&CURRENCYCODE=".$currencyCodeType ."&ButtonSource=ECSHOP_cart_EC_C2";

        $resArray=$this->hash_call("SetExpressCheckout",$nvpstr);

        $resArray;
        if(isset($resArray["ACK"]))
        {
            $ack = strtoupper($resArray["ACK"]);
        }
        
        if (isset($resArray["TOKEN"]))
        {
            $token = urldecode($resArray["TOKEN"]);
        }            
            $payPalURL = PAYPAL_URL.$token;
            $button = '<input type="button" class="payment" onclick="window.open(\''.$payPalURL. '\')" value="立刻支付"/>';

        return $button;
    }

    /**
     * 响应操作
     */
    function respond()
    {
        $order_sn = $_REQUEST['invoice'];
        $token =urlencode( $_REQUEST['token']);
        $nvpstr="&TOKEN=".$token;
        $resArray=$this->hash_call("GetExpressCheckoutDetails",$nvpstr);
        $ack = strtoupper($resArray["ACK"]);
        if($ack=="SUCCESS")
        {


   
            $token =urlencode( $_REQUEST['token']);

            $paymentAmount =urlencode ($_REQUEST['paymentAmount']);
            $paymentType = urlencode($_REQUEST['paymentType']);
            $currCodeType = urlencode($_REQUEST['currCodeType']);
            $payerID = urlencode($_REQUEST['PayerID']);
            $serverName = urlencode($_SERVER['SERVER_NAME']);

            $nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$paymentType.'&AMT='.$paymentAmount.'&CURRENCYCODE='.$currCodeType.'&IPADDRESS='.$serverName ;

            $resArray=$this->hash_call("DoExpressCheckoutPayment",$nvpstr);
            
            $ack = strtoupper($resArray["ACK"]);
            if($ack=="SUCCESS")
            {
                /* 改变订单状态 */
                order_paid($order_sn, 2);
                return true;
            }
            else
             {
                return false;
             }
        }
        else
         {
            return false;
         }
    }

    function hash_call($methodName,$nvpStr)
    {
        global $API_Endpoint;
        $payment = D('Payment')->getPayment('paypal');
        $version='53.0';
        $API_UserName=$payment['paypal_ec_username'];
        $API_Password=$payment['paypal_ec_password'];
        $API_Signature=$payment['paypal_ec_signature'];
        $nvp_Header;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$API_Endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);

        if(USE_PROXY)
        {
            curl_setopt ($ch, CURLOPT_PROXY, PROXY_HOST.":".PROXY_PORT);
        }

        $nvpreq="METHOD=".urlencode($methodName)."&VERSION=".urlencode($version)."&PWD=".urlencode($API_Password)."&USER=".urlencode($API_UserName)."&SIGNATURE=".urlencode($API_Signature).$nvpStr;

        curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);

        $response = curl_exec($ch);

        $nvpResArray=$this->deformatNVP($response);
        
        $nvpReqArray=$this->deformatNVP($nvpreq);
      
        curl_close($ch);
        return $nvpResArray;
    }


    function deformatNVP($nvpstr)
    {

        $intial=0;
        $nvpArray = array();

        while(strlen($nvpstr))
        {
            $keypos= strpos($nvpstr,'=');
            $valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
            $keyval=substr($nvpstr,$intial,$keypos);
            $valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
            $nvpArray[urldecode($keyval)] =urldecode( $valval);
            $nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
        }

        return $nvpArray;
    }

}