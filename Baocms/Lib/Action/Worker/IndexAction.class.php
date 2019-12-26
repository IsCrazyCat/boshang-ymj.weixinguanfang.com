<?php
class IndexAction extends CommonAction{
    public function index(){
        $this->assign('shop_branch', D('Shopbranch')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count());
        $this->assign('shop_branch_audit', D('Shopbranch')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'audit' => 0))->count());
        $counts = array();
        $bg_time = strtotime(TODAY);
        //套餐
        $counts['tuan'] = (int) D('Tuan')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count(); 
        $counts['tuan_audit'] = (int) D('Tuan')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => array('EGT', 0), 'audit' => 0))->count(); 
		//待审核套餐
        $counts['tuan_order_code_is_used'] = (int) D('Tuancode')->where(array('shop_id' => $this->shop_id, 'is_used' => 0))->count(); 
      
        //优惠劵
        $counts['coupon_is_used'] = (int) D('Coupondownload')->where(array('shop_id' => $this->shop_id, 'is_used' => 0))->count();
       
   
        //家政信息
        $counts['appoint'] = (int) D('Appointorder')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['appoint_audit'] = (int) D('Appointorder')->where(array('shop_id' => $this->shop_id, 'status' => 1, 'closed' => 0))->count();
        //商家招聘
        $counts['work'] = (int) D('Work')->where(array('shop_id' => $this->shop_id))->count();
        $counts['work_audit'] = (int) D('Work')->where(array('shop_id' => $this->shop_id, 'audit' => 0))->count();
        //文章
        $counts['news'] = (int) D('Article')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['news_autit'] = (int) D('Article')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'audit' => 0))->count();
        //商家预约
        $counts['shopyuyue'] = (int) D('Shopyuyue')->where(array('shop_id' => $this->shop_id))->count();
        $counts['shopyuyue_one'] = (int) D('Shopyuyue')->where(array('shop_id' => $this->shop_id, 'used' => 1))->count();
        $counts['shopyuyue_eight'] = (int) D('Shopyuyue')->where(array('shop_id' => $this->shop_id, 'used' => 0))->count();
        $this->assign('counts', $counts);
		//这个后期封装
		$appid = $this->_CONFIG['weixin']["appid"];
        $appsecret = $this->_CONFIG['weixin']["appsecret"];
        import("@/Net.Jssdk");
        $jssdk = new JSSDK("{$appid}", "{$appsecret}");
        $sign = $jssdk->GetSignPackage();
        $this->assign("sign", $sign);
		

        $this->display();
    }
    public function dingwei(){
        $lat = $this->_get('lat', 'htmlspecialchars');
        $lng = $this->_get('lng', 'htmlspecialchars');
        cookie('lat', $lat);
        cookie('lng', $lng);
        die(NOW_TIME);
    }
    /**
     * 扫描核销码，展示使用用户信息等，
     * 就是增加一步信息展示，无业务逻辑
     */
    public function scanaudit(){



        //消费用户

        $json = $this->_param('snstr');

        $jsonarr = explode('/',$json);
        if(!empty($json)){
            for($i=0;$i<count($jsonarr);$i++){
                $val = $jsonarr[$i];
                if($val=='code_id'){
                    $code_id = $jsonarr[$i+1];
                }
                if($val=='use_user_id'){
                    $use_user_id = $this->_param('use_user_id');
                }
            }
        }else{
            $code_id = (int) $this->_param('code_id');
            $use_user_id = $this->_param('use_user_id');
        }
        $use_user = D('Users')->find($use_user_id);
        $tuancode = D('Tuancode')->find($code_id);
        if(empty($tuancode)){
            $this->error('没有找到对应的团购券信息！', U('worker/index/index'));
        }
        $tuan = D('tuan')->find($tuancode['tuan_id']);
        $order = D('Tuanorder')->find($tuancode['order_id']);

//        $scanurl = str_replace('index/scanaudit','weixin/tuan',$_SERVER['REQUEST_URI']);
        $scanurl = __HOST__.'/worker/weixin/tuan'.'/code_id/'.$code_id.'/use_user_id/'.$use_user_id;
        $this->assign('scanurl',$scanurl);
        $this->assign('use_user',$use_user);
        $this->assign('tuan',$tuan);
        $this->assign('order',$order);

        $this->display();
    }
}