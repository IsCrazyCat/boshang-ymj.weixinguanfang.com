<?php



class OperateAction extends CommonAction {


    public function index() {
        $counts = array();
        $bg_time = strtotime(TODAY);
		//套餐
		$counts['tuan'] = (int) D('Tuan')->where(array('shop_id' => $this->shop_id,'closed' => 0))->count();//总套餐数量
		$counts['tuan_audit'] = (int) D('Tuan')->where(array('shop_id' => $this->shop_id,'closed' => 0,'status' => array('EGT', 0),'audit' => 0))->count();//待审核套餐
		$counts['tuan_order'] = (int) D('Tuanorder')->where(array('shop_id' => $this->shop_id))->count();//总套餐清单
		$counts['totay_tuan_order'] = (int) D('Tuanorder')->where(array('shop_id' => $this->shop_id,'create_time' => array(array('ELT', NOW_TIME),array('EGT', $bg_time),), 'status' => array('EGT', 0),))->count();//今日套餐订单
		$counts['tuan_order_code_is_used'] = (int) D('Tuancode')->where(array('shop_id' => $this->shop_id,'is_used' => 0))->count();//未验证
		$counts['tuan_order_code_status'] = (int) D('Tuancode')->where(array('shop_id' => $this->shop_id,'status' => 1))->count();//套餐退款中
		
		//商城
		$counts['goods'] = (int) D('Goods')->where(array('shop_id' => $this->shop_id,'closed' => 0,))->count();//总商品
		$counts['goods_audit'] = (int) D('Goods')->where(array('shop_id' => $this->shop_id,'closed' => 0,'audit' => 0))->count();//待审核商品
		$counts['goods_order'] = (int) D('Order')->where(array('shop_id' => $this->shop_id,'closed' => 0))->count();//总商品清单
		$counts['totay_goods_order'] = (int) D('Order')->where(array('shop_id' => $this->shop_id,'closed' => 0,'create_time' => array(array('ELT', NOW_TIME),array('EGT', $bg_time),), 'status' => array('EGT', 0),))->count();//今日商品订单
		$counts['goods_order_one'] = (int) D('Order')->where(array('shop_id' => $this->shop_id,'closed' => 0,'status' => 1))->count();//1代表已经付
		$counts['goods_order_two'] = (int) D('Order')->where(array('shop_id' => $this->shop_id,'closed' => 0,'status' => 2))->count();//2代表正在配送
		$counts['goods_order_three'] = (int) D('Order')->where(array('shop_id' => $this->shop_id,'closed' => 0,'status' => 8))->count();//8代表已经完成 
		
		//优惠劵
		$counts['coupon'] = (int) D('Coupon')->where(array('shop_id' => $this->shop_id,'closed' => 0,))->count();//总优惠劵
		$counts['coupon_audit'] = (int) D('Coupon')->where(array('shop_id' => $this->shop_id,'closed' => 0,'audit' => 0))->count();//待审核优惠劵
		$counts['coupon_download'] = (int) D('Coupondownload')->where(array('shop_id' => $this->shop_id))->count();//总下载优惠劵
		$counts['totay_coupon_download'] = (int) D('Coupondownload')->where(array('shop_id' => $this->shop_id,'create_time' => array(array('ELT', NOW_TIME),array('EGT', $bg_time),), 'status' => array('EGT', 0),))->count();//今日下载优惠劵
		$counts['coupon_is_used'] = (int) D('Coupondownload')->where(array('shop_id' => $this->shop_id,'is_used' => 0))->count();//未验证优惠劵
		
		//外卖
		$counts['ele'] = (int) D('Ele')->where(array('shop_id' => $this->shop_id))->count();//总外卖个数
		$counts['ele_audit'] = (int) D('Ele')->where(array('shop_id' => $this->shop_id,'audit' => 0))->count();//待审核外卖
		$counts['ele_order'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id,'closed' => 0))->count();//总外卖订单
		$counts['totay_ele_order'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id,'closed' => 0,'create_time' => array(array('ELT', NOW_TIME),array('EGT', $bg_time),), 'status' => array('EGT', 0),))->count();//今日外卖订单
		$counts['ele_order_one'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id,'closed' => 0,'status' => 1))->count();//1等待处理
		$counts['ele_order_two'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id,'closed' => 0,'status' => 2))->count();// 2代表已经确认
		$counts['ele_order_eight'] = (int) D('Eleorder')->where(array('shop_id' => $this->shop_id,'closed' => 0,'status' => 8))->count();// 8代表配送完成


		//订座
		$counts['ding_room'] = (int) D('Shopdingroom')->where(array('shop_id' => $this->shop_id))->count();//订座包厢数量
		$counts['ding_order'] = (int) D('Shopdingorder')->where(array('shop_id' => $this->shop_id))->count();//总订座订单
		$counts['totay_ding_order'] = (int) D('Shopdingorder')->where(array('shop_id' => $this->shop_id,'create_time' => array(array('ELT', NOW_TIME),array('EGT', $bg_time),), 'status' => array('EGT', 0),))->count();//今日订座订单
		$counts['ding_order_one'] = (int) D('Shopdingyuyue')->where(array('shop_id' => $this->shop_id,'is_pay' => 1))->count();// 订座1代表已经付款购买 
		$counts['ding_order_zero'] = (int) D('Shopdingyuyue')->where(array('shop_id' => $this->shop_id,'is_pay' => 0))->count();// 订座0未付款
		
		//黄页
		$counts['biz'] = (int) D('Biz')->where(array('shop_id' => $this->shop_id))->count();//总黄页数量
		$counts['biz_audit'] = (int) D('Biz')->where(array('shop_id' => $this->shop_id,'status' => -1))->count();// 等待审核黄页
		
		//粉丝
		$counts['favorites'] = (int) D('Shopfavorites')->where(array('shop_id' => $this->shop_id))->count();//总粉丝数量
		$counts['totay_favorites'] = (int) D('Shopfavorites')->where(array('shop_id' => $this->shop_id,'create_time' => array(array('ELT', NOW_TIME),array('EGT', $bg_time),), 'status' => array('EGT', 0),))->count();//今日新增粉丝数量
		
		//评价
		$counts['shop_dianping'] = (int) D('Shopdianping')->where(array('shop_id' => $this->shop_id,'closed' => 0))->count();//总商家点评
		$counts['totay_shop_dianping'] = (int) D('Shopdianping')->where(array('shop_id' => $this->shop_id,'closed' => 0,'create_time' => array(array('ELT', NOW_TIME),array('EGT', $bg_time),), 'status' => array('EGT', 0),))->count();//今日商家点评
		$counts['goods_dianping'] = (int) D('Goodsdianping')->where(array('shop_id' => $this->shop_id,'closed' => 0))->count();//总商城点评
		$counts['totay_goods_dianping'] = (int) D('Goodsdianping')->where(array('shop_id' => $this->shop_id,'closed' => 0,'create_time' => array(array('ELT', NOW_TIME),array('EGT', $bg_time),), 'status' => array('EGT', 0),))->count();//今日商城点评
		$counts['ele_dianping'] = (int) D('Eledianping')->where(array('shop_id' => $this->shop_id,'closed' => 0))->count();//总外卖点评
		$counts['totay_ele_dianping'] = (int) D('Eledianping')->where(array('shop_id' => $this->shop_id,'closed' => 0,'create_time' => array(array('ELT', NOW_TIME),array('EGT', $bg_time),), 'status' => array('EGT', 0),))->count();//今日外卖点评
		$counts['ding_dianping'] = (int) D('Shopdingdianping')->where(array('shop_id' => $this->shop_id,'closed' => 0))->count();//总订座点评
		$counts['totay_ding_dianping'] = (int) D('Shopdingdianping')->where(array('shop_id' => $this->shop_id,'closed' => 0,'create_time' => array(array('ELT', NOW_TIME),array('EGT', $bg_time),), 'status' => array('EGT', 0),))->count();//今日订座点评
		$counts['tuan_dianping'] = (int) D('Tuandianping')->where(array('shop_id' => $this->shop_id,'closed' => 0))->count();//总套餐点评
		$counts['totay_tuan_dianping'] = (int) D('Tuandianping')->where(array('shop_id' => $this->shop_id,'closed' => 0,'create_time' => array(array('ELT', NOW_TIME),array('EGT', $bg_time),), 'status' => array('EGT', 0),))->count();//今日套餐点评
		
		//分类信息
		$counts['life'] = (int) D('Life')->where(array('shop_id' => $this->shop_id,'closed' => 0))->count();//总分类信息
		$counts['life_audit'] = (int) D('Life')->where(array('shop_id' => $this->shop_id,'audit' => 0,'closed' => 0))->count();//待审核分类信息
		
		//商家招聘
		$counts['work'] = (int) D('Work')->where(array('shop_id' => $this->shop_id))->count();//总招聘
		$counts['work_audit'] = (int) D('Work')->where(array('shop_id' => $this->shop_id,'audit' => 0))->count();//待审核招聘
		
		//商家预约
		$counts['shopyuyue'] = (int) D('Shopyuyue')->where(array('shop_id' => $this->shop_id,))->count();//总商家预约数量
		$counts['shopyuyue_one'] = (int) D('Shopyuyue')->where(array('shop_id' => $this->shop_id,'used' => 1))->count();//已确认预约
		$counts['shopyuyue_eight'] = (int) D('Shopyuyue')->where(array('shop_id' => $this->shop_id,'used' => 0))->count();//未确认预约

		
		
		

        $this->assign('counts', $counts);
        /* 统计套餐 */
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
