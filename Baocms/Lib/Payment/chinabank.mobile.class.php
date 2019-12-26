<?php


class chinabank
{
    

    /**
     * 生成支付代码
     * @param   array   $logs      订单信息
     * @param   array   $setting    支付方式信息
     */
    function getCode($logs, $setting)
    {
        $bao_vid           = trim($setting['chinabank_account']);
        $bao_orderid       = $logs['logs_id'];
        $bao_vamount       = $logs['logs_amount'];
        $bao_vmoneytype    = 'CNY';
        $bao_vpaykey       = trim($setting['chinabank_key']);
        $bao_vreturnurl    = __HOST__ . U( 'Wap/payment/respond', array('code' => 'chinabank'));

        $MD5KEY =$bao_vamount.$bao_vmoneytype.$bao_orderid.$bao_vid.$bao_vreturnurl.$bao_vpaykey;
        $MD5KEY = strtoupper(md5($MD5KEY));

        $button  = '<form  method=post action="https://pay3.chinabank.com.cn/PayGate">';
        $button .= "<input type=hidden name='v_mid' value='".$bao_vid."'>";
        $button .= "<input type=hidden name='v_oid' value='".$bao_orderid."'>";
        $button .= "<input type=hidden name='v_amount' value='".$bao_vamount."'>";
        $button .= "<input type=hidden name='v_moneytype'  value='".$bao_vmoneytype."'>";
        $button .= "<input type=hidden name='v_url'  value='".$bao_vreturnurl."'>";
        $button .= "<input type=hidden name='v_md5info' value='".$MD5KEY."'>";
        $button .= "<input type=hidden name='remark1' value=''>";
        $button .= "<input type=submit  class='payment' value='立刻支付'>";
        $button .= "</form>";

        return $button;
    }

    /**
     * 响应操作
     */
    function respond()
    {
        $setting        =  D('Payment')->getPayment('chinabank');

        $v_oid          = trim($_POST['v_oid']);
        $v_pmode        = trim($_POST['v_pmode']);
        $v_pstatus      = trim($_POST['v_pstatus']);
        $v_pstring      = trim($_POST['v_pstring']);
        $v_amount       = trim($_POST['v_amount']);
        $v_moneytype    = trim($_POST['v_moneytype']);
        $remark1        = trim($_POST['remark1' ]);
        $remark2        = trim($_POST['remark2' ]);
        $v_md5str       = trim($_POST['v_md5str' ]);

        /**
         * 重新计算md5的值
         */
        $key            = $setting['chinabank_key'];

        $md5string=strtoupper(md5($v_oid.$v_pstatus.$v_amount.$v_moneytype.$key));

        /* 检查秘钥是否正确 */
        if ($v_md5str==$md5string)
        {
            if ($v_pstatus == '20')
            {
                D('Payment')->logsPaid($v_oid);
                return true;
            }
        }
       
        return false;
       
    }
}