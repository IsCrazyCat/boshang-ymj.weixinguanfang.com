<?php
class IndexAction extends CommonAction{
    public function index(){
        $this->assign('homepage', '商户中心首页');
        $this->assign('shop_branch', D('Shopbranch')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count());
        $this->assign('shop_branch_audit', D('Shopbranch')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'audit' => 0))->count());
        $counts = array();
        $bg_time = strtotime(TODAY);
        $counts['tuan'] = (int) D('Tuan')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['tuan_audit'] = (int) D('Tuan')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => array('EGT', 0), 'audit' => 0))->count();
        $counts['tuan_order'] = (int) D('Tuanorder')->where(array('shop_id' => $this->shop_id))->count();
        $counts['totay_tuan_order'] = (int) D('Tuanorder')->where(array('shop_id' => $this->shop_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['tuan_order_code_is_used'] = (int) D('Tuancode')->where(array('shop_id' => $this->shop_id, 'is_used' => 0))->count();
        $counts['tuan_order_code_is_used_ture'] = (int) D('Tuancode')->where(array('shop_id' => $this->shop_id, 'is_used' => 1))->count();
        $counts['tuan_order_code_status'] = (int) D('Tuancode')->where(array('shop_id' => $this->shop_id, 'status' => 1))->count();
        $counts['goods'] = (int) D('Goods')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['goods_audit'] = (int) D('Goods')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'audit' => 0))->count();
        $counts['goods_order'] = (int) D('Order')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['totay_goods_order'] = (int) D('Order')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['goods_order_one'] = (int) D('Order')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => 1))->count();
        $counts['goods_order_two'] = (int) D('Order')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => 2))->count();
        $counts['goods_order_three'] = (int) D('Order')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => 8))->count();
        $counts['coupon'] = (int) D('Coupon')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['coupon_audit'] = (int) D('Coupon')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'audit' => 0))->count();
        $counts['coupon_download'] = (int) D('Coupondownload')->where(array('shop_id' => $this->shop_id))->count();
        $counts['coupon_download_is_used'] = (int) D('Coupondownload')->where(array('shop_id' => $this->shop_id, 'is_used' => 0))->count();
        $counts['ele'] = (int) D('Ele')->where(array('shop_id' => $this->shop_id))->count();
        $counts['ele_audit'] = (int) D('Ele')->where(array('shop_id' => $this->shop_id, 'audit' => 0))->count();
        $counts['ele_order'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['totay_ele_order'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['ele_order_one'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => 1))->count();
        $counts['ele_order_two'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => 2))->count();
        $counts['ele_order_eight'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => 8))->count();
        $counts['ding_room'] = (int) D('Shopdingroom')->where(array('shop_id' => $this->shop_id))->count();
        $counts['ding_order'] = (int) D('Shopdingorder')->where(array('shop_id' => $this->shop_id))->count();
        $counts['totay_ding_order'] = (int) D('Shopdingorder')->where(array('shop_id' => $this->shop_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['ding_order_one'] = (int) D('Shopdingyuyue')->where(array('shop_id' => $this->shop_id, 'is_pay' => 1))->count();
        $counts['ding_order_zero'] = (int) D('Shopdingyuyue')->where(array('shop_id' => $this->shop_id, 'is_pay' => 0))->count();
        $counts['biz'] = (int) D('Biz')->where(array('shop_id' => $this->shop_id))->count();
        $counts['biz_audit'] = (int) D('Biz')->where(array('shop_id' => $this->shop_id, 'status' => -1))->count();
        $counts['favorites'] = (int) D('Shopfavorites')->where(array('shop_id' => $this->shop_id))->count();
        $counts['totay_favorites'] = (int) D('Shopfavorites')->where(array('shop_id' => $this->shop_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['shop_dianping'] = (int) D('Shopdianping')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['totay_shop_dianping'] = (int) D('Shopdianping')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['goods_dianping'] = (int) D('Goodsdianping')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['totay_goods_dianping'] = (int) D('Goodsdianping')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['ele_dianping'] = (int) D('Eledianping')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['totay_ele_dianping'] = (int) D('Eledianping')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['ding_dianping'] = (int) D('Shopdingdianping')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['totay_ding_dianping'] = (int) D('Shopdingdianping')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['tuan_dianping'] = (int) D('Tuandianping')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['totay_tuan_dianping'] = (int) D('Tuandianping')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['life'] = (int) D('Life')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['life_audit'] = (int) D('Life')->where(array('shop_id' => $this->shop_id, 'audit' => 0, 'closed' => 0))->count();
        $counts['work'] = (int) D('Work')->where(array('shop_id' => $this->shop_id))->count();
        $counts['work_audit'] = (int) D('Work')->where(array('shop_id' => $this->shop_id, 'audit' => 0))->count();
        $counts['shopyuyue'] = (int) D('Shopyuyue')->where(array('shop_id' => $this->shop_id))->count();
        $counts['shopyuyue_one'] = (int) D('Shopyuyue')->where(array('shop_id' => $this->shop_id, 'used' => 1))->count();
        $counts['shopyuyue_eight'] = (int) D('Shopyuyue')->where(array('shop_id' => $this->shop_id, 'used' => 0))->count();
        $counts['news'] = (int) D('Article')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['news_autit'] = (int) D('Article')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'audit' => 0))->count();
        $counts['shopworker'] = (int) D('Shopworker')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
		
		
		$str = '-1 day';
        $bg_time_yesterday = strtotime(date('Y-m-d', strtotime($str)));
        $counts['money_day'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'shop_id' => $this->shop_id))->sum('money');
        //昨日总收入
        $counts['money_day_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)), 'shop_id' => $this->shop_id))->sum('money');

        //这个后期封装
        $appid = $this->_CONFIG['weixin']["appid"];
        $appsecret = $this->_CONFIG['weixin']["appsecret"];
        import("@/Net.Jssdk");
        $jssdk = new JSSDK("{$appid}", "{$appsecret}");
        $sign = $jssdk->GetSignPackage();
        $this->assign("sign", $sign);
		
        $this->assign('counts', $counts);
        $this->display();
    }
    public function dingwei() {
        $lat = $this->_get('lat', 'htmlspecialchars');
        $lng = $this->_get('lng', 'htmlspecialchars');
        cookie('lat', $lat);
        cookie('lng', $lng);
        die(NOW_TIME);
    }
}