<?php



class alipay {

    private $alipay_gateway_new = 'http://wappaygw.alipay.com/service/rest.htm?';

    //新版手机支付比老早的多了一个令牌获取其他差不多
    public function getCode($logs, $setting) {
        $req_id = date('YmdHis') . rand(0, 1000);
        $para_token = array(
            'service' => 'alipay.wap.trade.create.direct',
            'partner' => $setting['alipay_partner'],
            '_input_charset' => 'utf-8',
            'sec_id' => 'MD5',
            'format' => 'xml',
            'v' => '2.0',
            'req_id' => $req_id,
            'req_data' =>
            '<direct_trade_create_req><notify_url>'
            . __HOST__ . U('Wap/payment/respond', array('code' => 'alipay')) .
            '</notify_url><call_back_url>' .
            __HOST__ . U('Wap/payment/respond', array('code' => 'alipay')) .
            '</call_back_url><seller_account_name>' .
            $setting['alipay_account'] .
            '</seller_account_name><out_trade_no>' . $logs['logs_id'] . '</out_trade_no><subject>'
            . $logs['subject'] .
            '</subject><total_fee>'
            . $logs['logs_amount'] .
            '</total_fee><merchant_url>' .
            __HOST__ . U('index/index') .
            '</merchant_url></direct_trade_create_req>'
        );

        ksort($para_token);
        reset($para_token);

        $arg = "";
        foreach ($para_token as $key => $val) {
            $arg.=$key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        $mysign = md5($arg . $setting['alipay_key']);

        $para_token['sign'] = $mysign;

        $html = $this->getHttpResponsePOST($this->alipay_gateway_new, $para_token);
        $html = urldecode($html);
        $para_html_text = $this->parseResponse($html);

        //获取request_token
        $request_token = $para_html_text['request_token'];

        $req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
        $parameter = array(
            "service" => "alipay.wap.auth.authAndExecute",
            "partner" => $setting['alipay_partner'],
            "sec_id" => 'MD5',
            "format" => 'xml',
            "v" => '2.0',
            "req_id" => $req_id,
            "req_data" => $req_data,
            "_input_charset" => 'utf-8',
        );
        ksort($parameter);
        reset($parameter);
        $arg = "";
        foreach ($parameter as $key => $val) {
            $arg.=$key . "=" . $val . "&";
        }


        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);


        $mysign = md5($arg . $setting['alipay_key']);
        $parameter['sign'] = $mysign;

        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->alipay_gateway_new . "_input_charset=utf-8' method='get'>";
        foreach ($parameter as $key => $val) {
            $sHtml.= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        $sHtml = $sHtml . "<input type='submit' class=\"button button-block button-big bg-yellow\" value='立刻支付'></form>";
        return $sHtml;
    }

    public function respond() { //暂时未经过测试
        $payment = D('Payment')->getPayment($_GET['code']);
		
        if (empty($_POST)) { //同步返回的逻辑代码
            $logs_id = trim($_GET['out_trade_no']);
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
            $sign = substr($sign, 0, count($sign) - 2) . $payment['alipay_key'];

            if (md5($sign) != $_GET['sign']) {
                return false;
            }
            if (strtolower($_GET['result']) == 'success' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                /* 改变订单状态 */
                D('Payment')->logsPaid($logs_id);
                return true;
            } else {
                return false;
            }
        } else {
            $para_sort = array();
            $para_sort['service'] = $_POST['service'];
            $para_sort['v'] = $_POST['v'];
            $para_sort['sec_id'] = $_POST['sec_id'];
            $para_sort['notify_data'] = $_POST['notify_data'];
            $sign = '';
            foreach ($para_sort AS $key => $val) {
                if ($key != 'sign') {
                    $sign .= "$key=$val&";
                }
            }
            $sign = substr($sign, 0, count($sign) - 2) . $payment['alipay_key'];

            if (md5($sign) != $_POST['sign']) {
                return false;
            }
            $doc = new DOMDocument();
            $doc->loadXML($_POST['notify_data']);
            $out_trade_no = $doc->getElementsByTagName("out_trade_no")->item(0)->nodeValue;
            $trade_status = $doc->getElementsByTagName("trade_status")->item(0)->nodeValue;

            if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                D('Payment')->logsPaid($out_trade_no);
                return true;
            }
            return false;
        }
    }

    /**
     * 远程获取数据，POST模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * @param $para 请求的数据
     * @param $input_charset 编码格式。默认值：空值
     * return 远程输出的数据
     */
    private function getHttpResponsePOST($url, $para) {


        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); //SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); //严格认证
        curl_setopt($curl, CURLOPT_CAINFO, APP_PATH . 'Lib/Payment/cacert.pem'); //证书地址
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $para); // post传输数据
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }

    /**
     * 解析远程模拟提交后返回的信息
     * @param $str_text 要解析的字符串
     * @return 解析结果
     */
    private function parseResponse($str_text) {
        //以“&”字符切割字符串
        $para_split = explode('&', $str_text);
        //把切割后的字符串数组变成变量与数值组合的数组
        foreach ($para_split as $item) {
            //获得第一个=字符的位置
            $nPos = strpos($item, '=');
            //获得字符串长度
            $nLen = strlen($item);
            //获得变量名
            $key = substr($item, 0, $nPos);
            //获得数值
            $value = substr($item, $nPos + 1, $nLen - $nPos - 1);
            //放入数组中
            $para_text[$key] = $value;
        }

        if (!empty($para_text['res_data'])) {
            //token从res_data中解析出来（也就是说res_data中已经包含token的内容）
            $doc = new DOMDocument();
            $doc->loadXML($para_text['res_data']);
            $para_text['request_token'] = $doc->getElementsByTagName("request_token")->item(0)->nodeValue;
        }

        return $para_text;
    }

}