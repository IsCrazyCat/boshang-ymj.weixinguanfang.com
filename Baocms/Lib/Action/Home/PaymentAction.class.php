<?php

import('WxPay', APP_PATH . 'Lib/Payment/weixin', '.Api.php');
//支付貌似不走这里
class PaymentAction extends CommonAction {

    protected function ele_success($message, $detail) {
        $order_id = $detail['order_id'];
        $eleorder = D('Eleorder')->find($order_id);
        $detail['single_time'] = $eleorder['create_time'];
        $detail['settlement_price'] = $eleorder['settlement_price'];
        $detail['new_money'] = $eleorder['new_money'];
        $detail['fan_money'] = $eleorder['fan_money'];
        $addr_id = $eleorder['addr_id'];
        $product_ids = array();
        $ele_goods = D('Eleorderproduct')->where(array('order_id' => $order_id))->select();
        foreach ($ele_goods as $k => $val) {
            if (!empty($val['product_id'])) {
                $product_ids[$val['product_id']] = $val['product_id'];
            }
        }
        $addr = D('Useraddr')->find($addr_id);
        $this->assign('addr', $addr);
        $this->assign('ele_goods', $ele_goods);
        $this->assign('products', D('Eleproduct')->itemsByIds($product_ids));
        $this->assign('message', $message);
        $this->assign('detail', $detail);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('ele');
    }
	
	 protected function hotel_success($message, $detail) {
        $order_id = (int)$detail['order_id'];
        $order = D('Hotelorder')->find($order_id);
        $detail['single_time'] = $order['create_time'];
        $room = D('Hotelroom')->find($order['room_id']);
        $hotel = D('Hotel')->find($room['hotel_id']);
        $this->assign('hotel',$hotel);
        $this->assign('room',$room);
        $this->assign('message', $message);
        $this->assign('detail', $detail);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('hotel');
    }
    

    protected function goods_success($message, $detail) {

        $order_ids = array();
        if (!empty($detail['order_id'])) {
            $order_ids[] = $detail['order_id'];
        } else {
            $order_ids = explode(',', $detail['order_ids']);
        }
        $goods = $good_ids = $addrs = array();
        foreach ($order_ids as $k => $val) {
            if (!empty($val)) {
                $order = D('Order')->find($val);
                $addr = D('Useraddr')->find($order['addr_id']);
                $ordergoods = D('Ordergoods')->where(array('order_id' => $val))->select();
                foreach ($ordergoods as $a => $v) {
                    $good_ids[$v['goods_id']] = $v['goods_id'];
                }
            }
            $goods[$k] = $ordergoods;
            $addrs[$k] = $addr;
        }
		
        $this->assign('addr', $addrs[0]);
        $this->assign('goods', $goods);
        $this->assign('good', D('Goods')->itemsByIds($good_ids));
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('goods');
    }

    public function booking($order_id) {
        $Bookingorder = D('Bookingorder');
        $Bookingyuyue = D('Bookingyuyue');
        $Bookingmenu = D('Bookingmenu');
        if (!$order = $Bookingorder->where('order_id = ' . $order_id)->find()) {
            $this->baoError('该订单不存在');
        } else if (!$yuyue = $dingyuyue->where('ding_id = ' . $order['ding_id'])->find()) {
            $this->baoError('该订单不存在');
        } else if ($yuyue['user_id'] != $this->uid) {
            $this->error('非法操作');
        } else {
            $arr = $Bookingorder->get_detail($this->shop_id, $order, $yuyue);
            $menu = $Bookingmenu->shop_menu($this->shop_id);
            $this->assign('yuyue', $yuyue);
            $this->assign('order', $order);
            $this->assign('order_id', $order_id);
            $this->assign('arr', $arr);
            $this->assign('menu', $menu);
            $this->display();
        }
    }

    protected function booking_success($message, $detail) {
        $Bookingorder = D('Bookingorder');
        $Bookingyuyue = D('Bookingyuyue');
        $Bookingmenu = D('Bookingmenu');

        if (!$order = $Bookingdingorder->where('order_id = ' . $detail['order_id'])->find()) {
            $this->error('该订单不存在');
        } else if (!$yuyue = $Bookingyuyue->where('ding_id = ' . $order['ding_id'])->find()) {
            $this->error('该订单不存在');
        } else if ($yuyue['user_id'] != $this->shop_id) {
            $this->error('非法操作');
        } else {
            $arr = $Bookingorder->get_detail($yuyue['shop_id'], $order, $yuyue);
            $menu = $Bookingmenu->shop_menu($yuyue['shop_id']);
            $this->assign('yuyue', $yuyue);
            $this->assign('order', $order);
            $this->assign('order_id', $detail['order_id']);
            $this->assign('arr', $arr);
            $this->assign('menu', $menu);
            $this->assign('message', $message);
            $this->assign('paytype', D('Payment')->getPayments());
            $this->display('booking');
        }
    }

    protected function other_success($message, $detail) {

        $tuanorder = D('Tuanorder')->find($detail['order_id']);
        if (!empty($tuanorder['branch_id'])) {
            $branch = D('Shopbranch')->find($tuanorder['branch_id']);
            $addr = $branch['addr'];
        } else {
            $shop = D('Shop')->find($tuanorder['shop_id']);
            $addr = $shop['addr'];
        }

        $this->assign('addr', $addr);
        $tuans = D('Tuan')->find($tuanorder['tuan_id']);
        $this->assign('tuans', $tuans);
        $this->assign('tuanorder', $tuanorder);
        $this->assign('message', $message);
        $this->assign('detail', $detail);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('other');
    }
	
	//家政支付成功
	 protected function appoint_success($message, $detail){
        $order_id = (int) $detail['order_id'];
        $order = D('Appointorder')->find($order_id);
        $Appoint = D('Appoint')->find($order['appoint_id']);//获取众筹商品
        $this->assign('order', $order);
        $this->assign('appoint', $Appoint);
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('appoint');
    }
	
	//云购支付成功
	 protected function cloud_success($message, $detail){
        $log_id = (int) $detail['order_id'];
        $cloudlogs = D('Cloudlogs')->find($log_id);
        $cloudgoods = D('Cloudgoods')->find($cloudlogs['goods_id']);//获取商品
        $this->assign('cloudlogs', $cloudlogs);
        $this->assign('cloudgoods', $cloudgoods);
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('cloud');
    }

    public function respond() {
        $code = $this->_get('code');
        if (empty($code)) {
            $this->error('没有该支付方式！');
            die;
        }
        $ret = D('Payment')->respond($code);
        if ($ret == false) {
            $this->error('支付验证失败！1');
            die;
        }
        if ($this->isPost()) {
            echo 'SUCESS';
            die;
        }
        $type = D('Payment')->getType();
        $log_id = D('Payment')->getLogId();
        $detail = D('Paymentlogs')->find($log_id);
        if(!empty($detail)){
            if ($detail['type'] == 'ele') {
                $this->ele_success('恭喜您支付成功啦！', $detail);
            } elseif ($detail['type'] == 'booking') {
                $this->booking_success('恭喜您预订支付成功啦！', $detail);
            } elseif ($detail['type'] == 'goods') {
                if(empty($detail['order_id'])){
                    $this->success('合并付款成功', U('members/order/index'));
                }else{
                    $this->goods_success('恭喜您支付成功啦！', $detail);
                }
            } elseif ($detail['type'] == 'appoint') {
                $this->appoint_success('恭喜您家政支付成功啦！', $detail);
            } elseif ($type == 'hotel') {
                $this->hotel_success('恭喜您酒店成功啦！', $detail);
            }  elseif ($type == 'cloud') {
                $this->cloud_success('恭喜您云购支付成功啦！', $detail);
            }  elseif ($detail['type'] == 'gold' || $detail['type'] == 'money'|| $detail['type'] == 'fzmoney') {
                $this->success('恭喜您充值成功', U('members/index/index'));

            } else {
                $this->other_success('恭喜您支付成功啦！', $detail);
            }
        }else{
             $this->success('支付成功', U('members/index/index'));
        }
        
    }

    public function payment($log_id) {
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $log_id = (int) $log_id;
        $logs = D('Paymentlogs')->find($log_id);
        if (empty($logs) || $logs['user_id'] != $this->uid || $logs['is_paid'] == 1) {
            $this->error('没有有效的支付记录1！');
            die;
        }
        $url = "";
        if ($logs['type'] == "tuan") {
            $url = U('tuan/pay', array('order_id' => $logs['order_id']));
        } elseif ($logs['type'] == "ele") {
            $url = U('ele/pay', array('order_id' => $logs['order_id']));
        } elseif ($logs['type'] == "goods") {
            $url = U('mall/pay', array('order_id' => $logs['order_id']));
        } elseif ($logs['type'] == "booking") {//预订
            $url = U('booking/pay2', array('order_id' => $logs['order_id']));
        } elseif ($logs['type'] == "farm") {//农家可
            $url = U('farm/pay', array('order_id' => $logs['order_id']));
        } elseif ($logs['type'] == "appoint") {//家政
            $url = U('appoint/pay', array('order_id' => $logs['order_id']));
        }elseif ($logs['type'] == "hotel") {
            $url = U('hotels/pay', array('order_id' => $logs['order_id']));
        }elseif ($logs['type'] == "crowd") {//增加众筹
            $url = U('crowd/pay', array('order_id' => $logs['order_id']));
        }elseif ($logs['type'] == "cloud") {//云购
            $url = U('cloud/pay', array('log_id' => $logs['order_id']));
        }
        $this->assign('url', $url);
        $this->assign('button', D('Payment')->getCode($logs));
        $this->assign('types', D('Payment')->getTypes());
        $this->assign('logs', $logs);
        $this->assign('paytype', D('Payment')->getPayments());
		$this->assign('host', __HOST__);
		$check_pay_password = D('Users')->check_pay_password($this->uid);
		$this->assign('user_pay_password',$check_pay_password);
        $this->display();
    }
	//检测扫码支付支付状态
	 public function check() {
		$log_id = $this->_get('log_id');
        $paymentlogs = D('Paymentlogs')->find($log_id);
        if (empty($paymentlogs)) {
           $this->ajaxReturn(array('status' => 'error', 'msg' => '非法操作'));
        }elseif($paymentlogs['is_paid'] ==1){
			$this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您支付成功'));
		}else{
			$this->ajaxReturn(array('status' => 'error', 'msg' => '支付失败，正在重新检测，请勿关闭页面'));
		}
    }
	
    //检测支付密码
	 public function check_pay_password() {
		$user_id = I('user_id', 0, 'trim,intval');
		$pay_password = I('pay_password', 0, 'trim');
        $Users = D('Users')->find($user_id);
		if (empty($pay_password)){
           $this->ajaxReturn(array('status' => 'error', 'msg' => '支付密码不能为空'));
        }
		if ($Users['pay_password'] != md5(md5($pay_password)) ) {
           $this->ajaxReturn(array('status' => 'error', 'msg' => '支付密码错误'));
        }else{
		   $this->ajaxReturn(array('status' => 'success', 'msg' => '密码正确，点击支付按钮支付'));
		}
    }
	
	//设置支付密码
	 public function set_pay_password() {
		$user_id = I('user_id', 0, 'trim,intval');
		$pay_password = I('pay_password', 0, 'trim');
        $Users = D('Users')->find($user_id);
		if (empty($pay_password)){
           $this->ajaxReturn(array('status' => 'error', 'msg' => '支付密码不能为空'));
        }
		if (strlen($pay_password) < 6 || strlen($pay_password) >= 20){
           $this->ajaxReturn(array('status' => 'error', 'msg' => '支付密码大于等于6位，或者大于20位数，请重新设置'));
        }

		if (D('Passport')->set_pay_password($Users['account'], $pay_password)) {
			 $this->ajaxReturn(array('status' => 'success', 'msg' => '成功'));
		}else{
			 $this->ajaxReturn(array('status' => 'error', 'msg' => '操作失败，请重试'));
		}
    }

}
