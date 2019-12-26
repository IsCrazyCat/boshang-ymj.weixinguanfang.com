<?php

    //微信刷卡支付
    require_once "weixin/WxPay.MicroPay.php";

//    -刷卡支付，刷卡支付有单独的支付接口，不调用统一下单接口
    class micro
    {
//    流程详细说明：
//    （1）收银员在商户收银台生成支付订单，向用户展示支付金额；
//    （2）用户打开微信客户端，点击“我的钱包”，选择“刷卡”，进入条码界面；
//    （3）使用扫码设备读取用户手机屏幕上的条码；
//    （4）扫码设备将读取的信息上传给门店收银台；
//    （5）门店收银台得到支付信息后，向商户收银后台发起支付请求。
//    （6）商户后台对门店收银台的支付请求进行处理，生成签名后调用【提交刷卡支付API】向微信支付系统发起支付请求。
//    （7）微信支付系统得到商户侧的支付请求之后会对请求进行验证，验证通过之后会对请求数据进行处理，最后将处理后的支付结果返回给商户收银后台。
//如果支付成功，微信支付系统会将支付结果返回给商户，同时把支付结果通知给用户（以短信、微信消息的形式通知）。
//    （8）商户收银后台对得到的支付结果进行签名验证和处理，再将支付结果返回给门店收银台。
//    （9）收银员看到门店收银台的支付结果后给用户发货。
        public function getCode( $logs , $payment )
        {
            $input = new WxPayMicroPay();
            $auth_code = trim($payment["auth_code"]);
            $input->SetAuth_code($auth_code);
            $input->SetBody($logs['subject']);
            $total_fee = $logs['logs_amount'] * 100;
            $input->SetTotal_fee("{$total_fee}");
            $input->SetOut_trade_no($logs['logs_id']);

            $microPay = new MicroPay();
            printf_info($microPay->pay($input));

            /**
             * 注意：
             * 1、提交被扫之后，返回系统繁忙、用户输入密码等错误信息时需要循环查单以确定是否支付成功
             * 2、多次（一半10次）确认都未明确成功时需要调用撤单接口撤单，防止用户重复支付
             */
        }
        public function respond(){

        }
    }
