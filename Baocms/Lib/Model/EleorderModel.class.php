<?php

class EleorderModel extends CommonModel {
    protected $pk = 'order_id';
    protected $tableName = 'ele_order';
    protected $cfg = array(
        0 => '等待付款',
        1 => '等待配送',
        2 => '正在配送',
		3 => '等待退款',
		4 => '退款完成',		
        8 => '已完成',
    );

    public function checkIsNew($uid, $shop_id) {
        $uid = (int) $uid;
        $shop_id = (int) $shop_id;
        return $this->where(array('user_id' => $uid, 'shop_id' => $shop_id, 'closed' => 0))->count();
    }

    public function getCfg() {
        return $this->cfg;
    }


    public function overOrder($order_id) {
        $detail = D('Eleorder')->find($order_id);
        if (empty($detail))
            return false;
        if ($detail['status'] != 2)
            return false;
        $ele = D('Ele')->find($detail['shop_id']);
        $shop = D('Shop')->find($detail['shop_id']);
        if (D('Eleorder')->save(array('order_id' => $order_id, 'status' => 8))) { //防止并发请求
            if ($detail['is_pay'] == 1) {
                $settlement_price = $detail['settlement_price'];
                if ($ele['is_fan']) { //如果商家开通了返现金额
                    $fan_money = $ele['fan_money'] > $settlement_price ? $settlement_price : $ele['fan_money'];
                    $fan = rand(0, $fan_money);
                    if ($fan > 0) {//返现金额大于0 那么更新订单 
                        $settlement_price = $settlement_price - $fan;
                        D('Eleorder')->save(array(
                            'order_id' => $order_id,
                            'settlement_price' => $settlement_price,
                            'fan_money' => $fan,
                        ));
                        D('Users')->addMoney($detail['user_id'], $fan, $ele['shop_name'] . '订餐返现');
                    }
                }
				
               
			//写入商户资金判断，如果商户开通了第三方配送，则结算时候减去配送费。
			if($shop['is_pei'] == 0){
				if(!empty($ele['given_distribution'])){
					$money = $detail['settlement_price'] + $ele['logistics'];
				}else{
					D('Runningmoney')->add_delivery_logistics($order_id,$ele['logistics'],1);//运费结算给配送员
					$money = $detail['settlement_price'];
				}
			}else{
				$money = $detail['settlement_price'] + $ele['logistics'];
			}
			
            if ($money > 0) {
                    D('Shopmoney')->add(array(
                        'shop_id' => $detail['shop_id'],
						'city_id' => $shop['city_id'],
						'area_id' => $shop['area_id'],
                        'type' => 'ele',
                        'money' => $money ,
                        'create_ip' => get_client_ip(),
                        'create_time' => NOW_TIME,
                        'order_id' => $order_id,
                        'intro' => '餐饮订单:' . $order_id
                    ));

 					D('Users')->Money($shop['user_id'], $money, '商户餐饮订单资金结算:' . '外卖订单号：'.$order_id);//写入金块
                }
                D('Users')->gouwu($detail['user_id'],$detail['total_price'],'外卖积分奖励');
            }

            //更新卖出数
            D('Eleorderproduct')->updateByOrderId($order_id);
            D('Ele')->updateCount($detail['shop_id'], 'sold_num'); //这里是订单数
            D('Ele')->updateMonth($detail['shop_id']);
        }
        return true;

    }
	
	
	public function ele_print($order_id,$addr_id) {	
			$order_id = (int) $order_id;
			$addr_id = (int) $addr_id;	
			$order = D('Eleorder')->find($order_id);
			if (empty($order))//没有找到订单返回假
            return false;
			if($order['is_daofu'] == 1){
				$fukuan = '货到付款';
			}else{
				$fukuan = '已支付';
			}
            $member = D('Users')->find($order['user_id']);//会员信息
			if(!empty($addr_id)){
				$addr_id = $addr_id;	
			}else{
				$addr_id = $order['addr_id'];
			}
			$user_addr = D('Useraddr')->where(array('addr_id'=>$addr_id))->find();
			$shop_print = D('Shop')->where(array('shop_id'=> $order['shop_id']))->find();//商家信息
            $msg .= '@@2点菜清单__________NO:' . $order['order_id'] . '\r';
            $msg .= '店名：' . $shop_print['shop_name'] . '\r';
            $msg .= '联系人：' . $user_addr['name'] . '\r';
            $msg .= '电话：' . $user_addr['mobile'] . '\r';
            $msg .= '客户地址：' . $user_addr['addr'] . '\r';
            $msg .= '用餐时间：' . date('Y-m-d H:i:s', $order['create_time']) . '左右\r';
            $msg .= '用餐地址：' . $shop_print['addr'] . '\r';
            $msg .= '商家电话：' . $shop_print['tel'] . '\r';
            $msg .= '----------------------\r';
            $msg .= '@@2菜品明细\r';
            $products = D('Eleorderproduct')->where(array('order_id' => $order['order_id']))->select();
            foreach ($products as $key => $value) {
                $product = D('Eleproduct')->where(array('product_id' => $value['product_id']))->find();
                $msg		  .= ($key+1).'.'.$product['product_name'].'—'.($product['price']/100).'元'.'*'.$value['num'].'份\r';
            }
            $msg .= '----------------------\r';
            $msg .= '@@2付款状态：' . $fukuan . '\r';
            $msg .= '外送费用：' . $order['logistics'] / 100 . '元\r';
            $msg .= '菜品金额：' . $order['total_price'] / 100 . '元\r';
            $msg .= '应付金额：' . $order['need_pay'] / 100 . '元\r';
			$msg .= '留言：'.$order['message'].'\r';
			return $msg;//返回数组
   }
   
      public function combination_ele_print($order_id,$addr_id) {	
  		    $order = D('EleOrder') -> where('order_id =' . $order_id) -> find();
			$shops = D('Shop') -> find($order['shop_id']);
			//外卖打印开始
			if($shops['is_ele_print'] ==1){
			  $msg = $this->ele_print($order['order_id'],$order['addr_id']);
			 // p($msg);die;
			  $result = D('Print')->printOrder($msg, $shops['shop_id']);
			  $result = json_decode($result);
			  $backstate = $result -> state;
			  $ele = D('Ele') -> find($order['shop_id']);
			  if($ele['is_print_deliver'] ==1){//如果开启自动打印
				  if ($backstate == 1) {
						if($shops['is_pei'] ==1){//1代表没开通配送确认发货步骤
							 D('EleOrder') -> save(array('status' => 2,'is_print'=>1), array("where" => array('order_id' => $order['order_id'])));
						}else{//如果是配送配送只改变打印状态
							 D('EleOrder') -> save(array('is_print'=>1), array("where" => array('order_id' => $order['order_id'])));
						}
					}	
			 }	
				
		    }
		  return true;
	  }
						
						
   public function ele_delivery_order($order_id,$wait = 0) {	
   			$order_id = (int) $order_id;
			if($wait == 0){
				$status = 1;
			}else{
				$status = 0;
			}
  			$order = D('Eleorder')->find($order_id);
			if (empty($order)){
				 return false;//没有找到订单返回假
			}
			D('Sms')->sms_delivery_user($order_id,$type=1);//短信通知配送员
			D('Weixintmpl')->delivery_tz_user($order_id,$type=1);//微信消息全局通知
			$DeliveryOrder = D('DeliveryOrder');
            $shops = D('Shop')->find($order['shop_id']);
			
			if (!$Useraddr = D('Useraddr')->find($order['addr_id'])) {
				return false;//没有找到用户地址返回假
			}
			
			if ($ele = D('Ele')->find($order['shop_id'])) {
				if(!empty($ele['given_distribution'])){
					$is_appoint = 1;
				}else{
					$is_appoint  = 0;
				}
			}else{
				return false;//没有找到外卖商家返回假
			}
			
			if ($shops['is_pei'] == 0) {
				$deliveryOrder_data = array(
						'type' => 1, 
						'type_order_id' => $order['order_id'], 
						'delivery_id' => 0, 
						'shop_id' => $order['shop_id'],
						'city_id' => $shops['city_id'],
						'area_id' => $shops['area_id'], 
						'business_id' => $shops['business_id'],  
						'lat' => $shops['lat'], 
						'lng' => $shops['lng'],  
						'user_id' => $order['user_id'], 
						'shop_name' => $shops['shop_name'],
						'name' => $Useraddr['name'],
						'mobile' => $Useraddr['mobile'],
						'addr' => $Useraddr['addr'],
						'addr_id' => $order['addr_id'], 
						'address_id' => $order['address_id'], 
						'logistics_price' => $order['logistics'], //订单配送费
						'is_appoint' => $is_appoint, //状态1位指定配送员
						'appoint_user_id' => $ele['given_distribution'], //指定配送员ID
						'create_time' => time(), 
						'update_time' => 0, 
						'status' => $status,
						'closed'=>0
					);
				$DeliveryOrder -> add($deliveryOrder_data);
			}
	}
	
	public function ele_month_num($order_id) {	
   	   $order_id = (int) $order_id;
       $Eleorderproduct = D('Eleorderproduct')->where('order_id =' . $order_id)->select();
       foreach ($Eleorderproduct as $k => $v) {
       	 D('Eleproduct')->updateCount($v['product_id'], 'month_num', $v['num']);
       }
      return TRUE;
	}
}

