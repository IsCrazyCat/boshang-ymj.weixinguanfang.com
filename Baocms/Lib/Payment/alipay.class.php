<?php



class alipay {

    public function getCode($logs, $setting) {

        $real_method = $setting['service'];
        switch ($real_method) {
            case '0':
                $service = 'trade_create_by_buyer';
                break;
            case '1':
                $service = 'create_partner_trade_by_buyer';
                break;
            case '2':
                $service = 'create_direct_pay_by_user';
                break;
            
        }
        $parameter = array(
            'service' => $service,
            'partner' => $setting['alipay_partner'],
            '_input_charset' => 'utf-8',
            'notify_url' => __HOST__ . U( 'Home/payment/respond', array('code' => 'alipay')),
            'return_url' => __HOST__ . U( 'Home/payment/respond', array('code' => 'alipay')),
            /* 业务参数 */
            'subject' => $logs['subject'],
            'out_trade_no' => $logs['subject'] . $logs['logs_id'],
            'price' => $logs['logs_amount'],
            'quantity' => 1,
            'payment_type' => 1,
            /* 物流参数 */
            'logistics_type' => 'EXPRESS',
            'logistics_fee' => 0,
            'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',
            /* 买卖双方信息 */
            'seller_email' => $setting['alipay_account']
        );

        ksort($parameter);
        reset($parameter);

        $param = '';
        $sign = '';
        foreach ($parameter as $key => $val) {
            $param .= "$key=" . urlencode($val) . "&";
            $sign .= "$key=$val&";
        }
        $param = substr($param, 0, -1);
        $sign = substr($sign, 0, -1) . $setting['alipay_key'];
        $button = '<div style="text-align:center"><input type="button" class="payment" onclick="window.open(\'https://www.alipay.com/cooperate/gateway.do?' . $param . '&sign=' . md5($sign) . '&sign_type=MD5\')" value=" 立刻支付 " /></div>';
        return $button;
    }

    public function respond() {
        
        if (!empty($_POST)) {
            foreach ($_POST as $key => $data) {
                $_GET[$key] = $data;
            }
        }
        $payment = D('Payment')->getPayment($_GET['code']);
        $seller_email = rawurldecode($_GET['seller_email']);
        $logs_id = str_replace($_GET['subject'], '', $_GET['out_trade_no']);
        $logs_id = trim($logs_id);

        /* 检查支付的金额是否相符 */
        if (!D('Payment')->checkMoney($logs_id, $_GET['total_fee']*100)) {
            return false;
        }
        unset($_GET['_URL_']);
        /* 检查数字签名是否正确 */
        ksort($_GET);
        reset($_GET);
        
        $sign = '';

        foreach ($_GET AS $key => $val) {
            if ($key != 'sign' && $key != 'sign_type' && $key != 'code' && $key != 'g' && $key != 'm' && $key != 'a') {
                $sign .= "$key=$val&";
            }
        }

        $sign = substr($sign, 0, -1) . $payment['alipay_key'];
      
        //$sign = substr($sign, 0, -1) . ALIPAY_AUTH;
        if (md5($sign) != $_GET['sign']) {
            return false;
        }

        if ($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS' || $_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
            /* 改变订单状态 */
            D('Payment')->logsPaid($logs_id);

            return true;
        } else {
            return false;
        }
    }

}