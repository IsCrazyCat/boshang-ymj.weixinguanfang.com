<?php 
class tenpay
{
 
    /**
     * 生成支付代码
     * @param   array    $logs       订单信息
     * @param   array    $payment     支付方式信息
     */
    function getCode($logs, $payment)
    {
        $cmd_no = '1';

        /* 获得订单的流水号，补零到10位 */
        $sp_billno = $logs['logs_id'];

        /* 交易日期 */
        $today = date('Ymd');

        /* 将商户号+年月日+流水号 */
        $bill_no = str_pad($logs['logs_id'], 10, 0, STR_PAD_LEFT);
        $transaction_id = $payment['tenpay_account'].$today.$bill_no;

        /* 银行类型:支持纯网关和财付通 */
        $bank_type = '0';

        /* 订单描述，用订单号替代 */
        if (!empty($logs['logs_id']))
        {
            $desc = $logs['subject'];
            $attach = '';
        }else{
            $desc = '物品购买';
            $attach = '';
        }
        

       // $desc = iconv('utf-8', 'gbk', $desc);
    

        /* 返回的路径 */
        $return_url = __HOST__ . U( 'wap/payment/respond', array('code' => 'tenpay'));

        /* 总金额 */
        $total_fee = $logs['logs_amount']*100;

        /* 货币类型 */
        $fee_type = '1';

        /* 财付通风险防范参数 */
        $spbill_create_ip = $_SERVER['REMOTE_ADDR'];

        /* 数字签名 */
        $sign_text = "cmdno=" . $cmd_no . "&date=" . $today . "&bargainor_id=" . $payment['tenpay_account'] .
          "&transaction_id=" . $transaction_id . "&sp_billno=" . $sp_billno .
          "&total_fee=" . $total_fee . "&fee_type=" . $fee_type . "&return_url=" . $return_url .
          "&attach=" . $attach . "&spbill_create_ip=" . $spbill_create_ip . "&key=" . $payment['tenpay_key'];
        $sign = strtoupper(md5($sign_text));

        /* 交易参数 */
        $parameter = array(
            'cmdno'             => $cmd_no,                     // 业务代码, 财付通支付支付接口填  1
            'date'              => $today,                      // 商户日期：如20051212
            'bank_type'         => $bank_type,                  // 银行类型:支持纯网关和财付通
            'desc'              => $desc,                       // 交易的商品名称
            'purchaser_id'      => '',                          // 用户(买方)的财付通帐户,可以为空
            'bargainor_id'      => $payment['tenpay_account'],  // 商家的财付通商户号
            'transaction_id'    => $transaction_id,             // 交易号(订单号)，由商户网站产生(建议顺序累加)
            'sp_billno'         => $sp_billno,                  // 商户系统内部的定单号,最多10位
            'total_fee'         => $total_fee,                  // 订单金额
            'fee_type'          => $fee_type,                   // 现金支付币种
            'return_url'        => $return_url,                 // 接收财付通返回结果的URL
            'attach'            => $attach,                     // 用户自定义签名
            'sign'              => $sign,                       // MD5签名
            'spbill_create_ip'  => $spbill_create_ip,           //财付通风险防范参数
        );

        $button  = '<form action="https://www.tenpay.com/cgi-bin/v1.0/pay_gate.cgi" target="_blank" style="margin:0px;padding:0px" >';

        foreach ($parameter AS $key=>$val)
        {
            $button  .= "<input type='hidden' name='$key' value='$val' />";
        }
        $button  .= "<input type='hidden' name='cs' value='utf-8' />";

        $button  .= '<input type="submit"  class="button button-block bg-dot button-big"  value="立刻支付" /></form>';

        return $button;
    }

    /**
     * 响应操作
     */
    function respond()
    {
        /*取返回参数*/
        $cmd_no         = $_GET['cmdno'];
        $pay_result     = $_GET['pay_result'];
        $pay_info       = $_GET['pay_info'];
        $bill_date      = $_GET['date'];
        $bargainor_id   = $_GET['bargainor_id'];
        $transaction_id = $_GET['transaction_id'];
        $sp_billno      = $_GET['sp_billno'];
        $total_fee      = $_GET['total_fee'];
        $fee_type       = $_GET['fee_type'];
        $attach         = $_GET['attach'];
        $sign           = $_GET['sign'];

        $payment    =  D('Payment')->getPayment('tenpay');

    
        /* 如果pay_result大于0则表示支付失败 */
        if ($pay_result > 0)
        {
            return false;
        }



        /* 检查数字签名是否正确 */
        $sign_text  = "cmdno=" . $cmd_no . "&pay_result=" . $pay_result .
                          "&date=" . $bill_date . "&transaction_id=" . $transaction_id .
                            "&sp_billno=" . $sp_billno . "&total_fee=" . $total_fee .
                            "&fee_type=" . $fee_type . "&attach=" . $attach .
                            "&key=" . $payment['tenpay_key'];
        $sign_md5 = strtoupper(md5($sign_text));
        if ($sign_md5 != $sign)
        {
            return false;
        }
        else
        {
            D('Payment')->logsPaid($sp_billno);
            return true;
        }
    }
}
