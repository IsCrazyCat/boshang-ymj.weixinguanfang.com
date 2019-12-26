<?php
class IndexAction extends CommonAction{
    public function index(){
        $this->display();
    }
    public function main(){
        $counts = array();
        $bg_time = strtotime(TODAY);
        //套餐
        $counts['tuan'] = (int) D('Tuan')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['tuan_audit'] = (int) D('Tuan')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => array('EGT', 0), 'audit' => 0))->count();
        $counts['tuan_order'] = (int) D('Tuanorder')->where(array('shop_id' => $this->shop_id))->count();
        $counts['totay_tuan_order'] = (int) D('Tuanorder')->where(array('shop_id' => $this->shop_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['tuan_order_code_is_used'] = (int) D('Tuancode')->where(array('shop_id' => $this->shop_id, 'is_used' => 0))->count();
        $counts['tuan_order_code_status'] = (int) D('Tuancode')->where(array('shop_id' => $this->shop_id, 'status' => 1))->count();
        //商城
        $counts['goods'] = (int) D('Goods')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['goods_audit'] = (int) D('Goods')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'audit' => 0))->count();
        $counts['goods_order'] = (int) D('Order')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['totay_goods_order'] = (int) D('Order')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['goods_order_one'] = (int) D('Order')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => 1))->count();
        $counts['goods_order_two'] = (int) D('Order')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => 2))->count();
        $counts['goods_order_three'] = (int) D('Order')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => 8))->count();
        //优惠劵
        $counts['coupon'] = (int) D('Coupon')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['coupon_audit'] = (int) D('Coupon')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'audit' => 0))->count();
        $counts['coupon_download'] = (int) D('Coupondownload')->where(array('shop_id' => $this->shop_id))->count();
        $counts['totay_coupon_download'] = (int) D('Coupondownload')->where(array('shop_id' => $this->shop_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['coupon_is_used'] = (int) D('Coupondownload')->where(array('shop_id' => $this->shop_id, 'is_used' => 0))->count();
        //外卖
        $counts['ele'] = (int) D('Ele')->where(array('shop_id' => $this->shop_id))->count();
        $counts['ele_audit'] = (int) D('Ele')->where(array('shop_id' => $this->shop_id, 'audit' => 0))->count();
        $counts['ele_order'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();
        $counts['totay_ele_order'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['ele_order_one'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => 1))->count();
        $counts['ele_order_two'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => 2))->count();
        $counts['ele_order_eight'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'status' => 8))->count();
		//订座
        $counts['booking_menu'] = (int) D('Bookingmenu')->where(array('shop_id' => $this->shop_id))->count();
        $counts['booking_order'] = (int) D('Bookingorder')->where(array('shop_id' => $this->shop_id))->count();
        $counts['totay_booking_order'] = (int) D('Bookingorder')->where(array('shop_id' => $this->shop_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
        $counts['booking_order_one'] = (int) D('Bookingyuyue')->where(array('shop_id' => $this->shop_id, 'is_pay' => 1))->count();
        $counts['booking_order_zero'] = (int) D('Bookingyuyue')->where(array('shop_id' => $this->shop_id, 'is_pay' => 0))->count();
		
		//酒店
        $counts['hotel_room'] = (int) D('Hotelroom')->where(array('shop_id' => $this->shop_id))->count();
        $counts['hotel_order'] = (int) D('Hotelorder')->where(array('shop_id' => $this->shop_id))->count();
		
		$hotel = D('hotel')->where(array('shop_id' => $this->shop_id))->find();
        $counts['totay_hotel_order'] = (int) D('Hotelorder')->where(array('hotel_id' =>$hotel['hotel_id'], 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();

		
		//黄页
        $counts['biz'] = (int) D('Biz')->where(array('shop_id' => $this->shop_id))->count();
        $counts['biz_audit'] = (int) D('Biz')->where(array('shop_id' => $this->shop_id, 'status' => -1))->count();
        $counts['favorites'] = (int) D('Shopfavorites')->where(array('shop_id' => $this->shop_id))->count();
        $counts['totay_favorites'] = (int) D('Shopfavorites')->where(array('shop_id' => $this->shop_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();
		//店铺
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
		
		//分类信息
        $counts['life'] = (int) D('Life')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->count();//总分类信息
        $counts['life_audit'] = (int) D('Life')->where(array('shop_id' => $this->shop_id, 'audit' => 0, 'closed' => 0))->count();//待审核分类信息

        $counts['work'] = (int) D('Work')->where(array('shop_id' => $this->shop_id))->count();//总招聘
        $counts['work_audit'] = (int) D('Work')->where(array('shop_id' => $this->shop_id, 'audit' => 0))->count();//待审核招聘
        $counts['shopyuyue'] = (int) D('Shopyuyue')->where(array('shop_id' => $this->shop_id))->count();
        $counts['shopyuyue_one'] = (int) D('Shopyuyue')->where(array('shop_id' => $this->shop_id, 'used' => 1))->count();//总商家预约数量
        $counts['shopyuyue_eight'] = (int) D('Shopyuyue')->where(array('shop_id' => $this->shop_id, 'used' => 0))->count();//已确认预约
        
        $this->assign('counts', $counts);//未确认预约

        $bg_date = date('Y-m-d', NOW_TIME - 86400 * 6);
        $end_date = TODAY;
        $bg_time = strtotime($bg_date);
        $end_time = strtotime($end_date);
        $this->assign('bg_date', $bg_date);
        $this->assign('end_date', $end_date);
        $this->assign('money', D('Tuanorder')->money($bg_time, $end_time, $this->shop_id));
        $this->assign('ordermoney', D('Order')->money($bg_time, $end_time, $this->shop_id));
        $this->assign('shopmoney', D('Shopmoney')->money($bg_time, $end_time, $this->shop_id));
        $this->display();
    }
}