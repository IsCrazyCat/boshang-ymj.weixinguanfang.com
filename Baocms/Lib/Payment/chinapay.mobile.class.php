<?php 

class chinapay
{
    /**
     * 生成支付代码
     * @param   array   $logs  订单信息
     * @param   array   $payment    支付方式信息
     */

    function getCode($logs, $payment)
    {
     
        $charset = 'UTF-8';
     

        $front_pay_url         = 'https://unionpaysecure.com/api/Pay.action';
        $security_key          = $payment['chinapay_key'];
        $merId                 = $payment['chinapay_account'];
        $logsNumber            =  $logs['logs_id'] . '-' . $this->_formatSN($logs['logs_id']);	
        $frontEndUrl           = __HOST__ . U( 'wap/payment/respond', array('code' => 'chinapay'));
        $backEndUrl            = __HOST__ . U( 'wap/payment/respond', array('code' => 'chinapay'));
        $merAbbr               = $payment['chinapay_merabbr'];

        $params = array(
                "version"            =>  '1.0.0',                      //接口版本
                "signMethod"         =>  'md5',                        //加密方式
                "charset"            =>  $charset,                     //编码
                "transType"          =>  '01',                         //交易类型
                "origQid"            =>  '',
                "merId"              =>  $merId,                       //收款账号
                "merAbbr"            =>  $merAbbr,                     //商户名称
                "acqCode"            =>  '',
                "merCode"            =>  '',
                "commodityUrl"       =>  '',                           //商品url
                "commodityName"      =>  $logs['subject'] ,                           //商品名字
                "commodityUnitPrice" =>  '',                           //商品单价
                "commodityQuantity"  =>  '',                           //商品数量
                "commodityDiscount"  =>  '',
                "transferFee"        =>  '',
                "orderNumber"        =>  $logsNumber,                 //订单号，必须唯一
                "orderAmount"        =>  $logs['logs_amount'], //交易金额 转化为分
                "orderCurrency"      =>  '156',                        //交易币种，CURRENCY_CNY=>人民币
                "orderTime"          =>  date('YmdHis'),               //交易时间, YYYYmmhhddHHMMSS
                "customerIp"         =>  $_SERVER['REMOTE_ADDR'],      //用户IP
                "customerName"       =>  '',
                "defaultPayType"     =>  '',
                "defaultBankNumber"  =>  '',
                "transTimeout"       =>  '',
                "frontEndUrl"        =>  $frontEndUrl,                 // 前台回调URL
                "backEndUrl"         =>  $backEndUrl,                  // 后台回调URL
                "merReserved"        =>  ''             
        );
        
        $params['signature']    =$this->sign($params, $security_key,'md5');
        
        $button = "<input type='submit' class='payment' value='立刻支付' />";
        $html = $this->create_html($params,$front_pay_url,$button);

        return $html;
    }

    /**
     * 响应操作
     */
    function respond()
    {
            $payment        = D('Payment')->getPayment($_GET['chinapay']);

            $arr_args = array();
            $arr_reserved = array();

            if (is_array($_POST)) 
            {
                $arr_args       = $_POST;
                $cupReserved    = isset($arr_args['cupReserved']) ? $arr_args['cupReserved'] : '';
                parse_str(substr($cupReserved, 1, -1), $arr_reserved); //去掉前后的{}
            }
            else 
            {
                $cupReserved = '';
                $pattern = '/cupReserved=(\{.*?\})/';
                if (preg_match($pattern, $_POST, $match)) { //先提取cupReserved
                   $cupReserved = $match[1];
                }
                //将cupReserved的value清除(因为含有&, parse_str没法正常处理)
                $args_r         = preg_replace($pattern, 'cupReserved=', $_POST);
                parse_str($args_r, $arr_args);
                $arr_args['cupReserved'] = $cupReserved;
                parse_str(substr($cupReserved, 1, -1), $arr_reserved); //去掉前后的{}
            }
            //提取服务器端的签名
            if (!isset($arr_args['signature']))
            {
                 return false;
            }
     
            //验证签名
            $signature=$this->sign($arr_args, $payment['chinapay_key'],'md5');
            if ($signature != $arr_args['signature']) 
            {
                return false;
            }

            $arr_ret = array_merge($arr_args, $arr_reserved);
            unset($arr_ret['cupReserved']);

            if ($arr_ret['respCode'] != '00') 
            {
                return false;
            }
            if(!strpos($arr_ret['orderNumber'], '-')) 
            {
                return false;
            }
            $logs_sn_arr = explode('-', $arr_ret['orderNumber']);
            
            $logs_sn    = $logs_sn_arr['0'];
            $pay_id = intval($logs_sn_arr['1']);
          
            if ($payment['chinapay_account'] != $arr_ret['merId'])
            {
               return false;
            }
           
            // 如果未支付成功。
            if ($arr_ret['respCode'] != '00')
            {
               return false;
            }
            
            D('Payment')->logsPaid($logs_sn);
            //告诉用户交易完成
            return true;

    }
    /**
    * 格式订单号
    */
    function _formatSN($sn)
    {
        return str_repeat('0', 9 - strlen($sn)) . $sn;
    }
    function create_html($params,$front_pay_url,$button)
    {
        $html = <<<eot
    <form style="text-align:center;" id="pay_form" name="pay_form" action="{$front_pay_url}" method="post" target="_blank">
eot;
        foreach ($params as $key => $value) 
        {
            $html .= " <input type=\"hidden\" name=\"{$key}\" id=\"{$key}\" value=\"{$value}\" />\n";
        }
        $html .= $button . "</form>";
        return $html;
    }
    function sign($params,$security_key,$sign_method)
    {
        if (strtolower($sign_method) == "md5") 
        {
            ksort($params);
            $sign_str = "";
            $sign_ignore_params=array('bank','signMethod','signature');
            foreach ($params as $key => $val)
            {
                if (in_array($key,$sign_ignore_params)) 
                {
                    continue;
                }
                $sign_str .= sprintf("%s=%s&", $key, $val);
            }
            return md5($sign_str . md5($security_key));
        }
        else 
        {
            exit("Unknown sign_method set in quickpay_conf");
        }
    }

}