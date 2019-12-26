<?php
class OrderModel extends CommonModel{
	
    protected $pk = 'order_id';
    protected $tableName = 'order';
    protected $types = array(
		0 => '等待付款', 
		1 => '已支付',
		2 => '仓库已捡货', 
		3 => '客户已收货', 
		4 => '申请退款中', //待开发
		5 => '已退款', //待开发
		6 => '申请售后中', //待开发
		7 => '已完成售后', //待开发
		8 => '已完成配送',
        9 =>'分成中'
	);
	
	
	
    public function getType(){
        return $this->types;
    }
	public function getError() {
        return $this->error;
    }
	
	public function order_delivery($order_id, $type=''){
		$order_id = (int)$order_id;
        $type = (int)$type;
		if($type ==0){
			$obj = D('Order');
		}else{
			$obj = D('Eleorder');	
		}
		$order_shop = $obj->where('order_id =' . $order_id)->find();
		$shop = D('Shop')->find($order_shop['shop_id']);
	
		if($shop['is_pei'] == 0) {//如果走配送
			$DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => $type))->find();
			if (!empty($DeliveryOrder)) {
				if ($DeliveryOrder['closed'] ==0 ) {//如果订单状态是关闭
					D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => $type))->setField('closed', 0); //重新开启订单
				}else{
					if($DeliveryOrder['status'] == 2 || $DeliveryOrder['status'] == 8) {
						return false;
					}else{
						D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 0))->setField('closed', 1);//更改配送状态
					}	
			   }
			}else{
				return false;
			}
		  return true;
		}	
	   return true;
		
	}
	
		
   //更新购物表的状态
   public function del_order_goods_closed($order_id) {
       $order_id = (int) $order_id;
       $order_goods = D('Ordergoods')->where(array('order_id' => $order_id))->select();
			foreach ($ordergoods as $k => $v){
				D('Ordergoods')->save(array('order_id' => $v['order_id'], 'closed' => 1)); 
        }
      return TRUE;
    }
	
  //更新退款库存
   public function del_goods_num($order_id) {
       $order_id = (int) $order_id;
       $ordergoods = D('Ordergoods')->where('order_id =' . $order_id)->select();
       foreach ($ordergoods as $k => $v) {
       	 D('Goods')->updateCount($v['goods_id'], 'num', $v['num']);
       }
      return TRUE;
    }
	
	//更新商城销售接口
    public function mallSold($order_ids) {
        if (is_array($order_ids)) {
            $order_ids = join(',', $order_ids);//这里还是有一点点区别
            $ordergoods = D('Ordergoods')->where("order_id IN ({$order_ids})")->select();
            foreach ($ordergoods as $k => $v) {
                D('Goods')->updateCount($v['goods_id'], 'sold_num', $v['num']);
                //这里操作多规格的库存
                 refresh_spec_stock($v['goods_id'],$v['key'],-$v['num']);     
			D('Goods') -> updateCount($v['goods_id'], 'num', -$v['num']);//减去库存
            }
        } else {
            $order_ids = (int) $order_ids;
            $ordergoods = D('Ordergoods')->where('order_id =' . $order_ids)->select();
            foreach ($ordergoods as $k => $v) {
                D('Goods')->updateCount($v['goods_id'], 'sold_num', $v['num']);//更新销量
                 //这里操作多规格的库存
                 refresh_spec_stock($v['goods_id'],$v['key'],-$v['num']);     
		     D('Goods') -> updateCount($v['goods_id'], 'num', -$v['num']);//减去库存		     
            }
        }
        return TRUE;
    }



    //商城购物配送接口
    public function mallPeisong($order_ids,$wait = 0) {
        if($wait == 0){
            $status = 1;
        }else{
            $status = 0;
        }
        foreach ($order_ids as $order_id) {
            $order = D('Order')->where('order_id =' . $order_id)->find();
            $shops = D('Shop')->find($order['shop_id']);
			$Paddress = D('Paddress')->find($order['address_id']);
			D('Sms')->sms_delivery_user($order_id,$type=0);//短信通知配送员
			D('Weixintmpl')->delivery_tz_user($order_id,$type=0);//微信消息全局通知
            if (!empty($shops['tel'])) {
                $mobile = $shops['tel'];
            } else {
                $mobile = $shops['mobile'];
            }
            if ($shops['is_pei'] == 0) {
                $mall_deliveryorder_data = array(
                    'type' => 0,
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
					'name' => $Paddress['xm'],
					'mobile' => $Paddress['tel'],
					'addr' => $Paddress['area_str'].$Paddress['info'],
                    'addr_id' => $order['addr_id'],
                    'address_id' => $order['address_id'],
                    'logistics_price' => $order['express_price'],
                    'create_time' => NOW_TIME,
                    'update_time' => 0,
                    'status' => $status
                );
                D('DeliveryOrder')->add($mall_deliveryorder_data);
            }
        }
        return true;
    }

	//PC端输入物流单号发货
	public function pc_express_deliver($order_id){
		D('Order')->save(array('status' => 2), array("where" => array('order_id' => $order_id)));
        D('Ordergoods')->save(array('status' => 1), array("where" => array('order_id' => $order_id)));
        return true;
    }
	
     //可以使用积分 根据订单使用积分的情况 返回支付记录需要实际支付的金额！
    public function useIntegral($uid,$order_ids){
        $orders = $this->where(array('order_id'=>array('IN',$order_ids)))->select();
        $users = D('Users');
        $member = $users->find($uid); 
        $useint = $fan = $total = 0;
        foreach($orders as $k=>$order){
            if($order['use_integral']>$order['can_use_integral']){ //需要返回积分给客户
                $member['integral'] += $order['use_integral']-$order['can_use_integral'];
               
                $this->save($order); //保存ORDER
                $users->addIntegral($uid,$order['use_integral']-$order['can_use_integral'],'商城购物使用积分退还');//积分退还
                $orders[$k]['use_integral'] = $order['use_integral'] = $order['can_use_integral'];
            }else{ //否则就是 使用积分
                if($member['integral'] > $order['can_use_integral']){//账户余额大于可使用积分时
                    $member['integral'] -=$order['can_use_integral'];
                    $orders[$k]['use_integral'] = $order['use_integral'] = $order['can_use_integral'];
                    $this->save($order); //保存ORDER
                    $users->addIntegral($uid,-$order['can_use_integral'],'商城购物使用积分');
                }elseif($member['integral']>0){//账户余额小于积分时
                     $orders[$k]['use_integral'] = $order['use_integral'] = $member['integral'];
                     $this->save($order); //保存ORDER
                     $users->addIntegral($uid,-$member['integral'],'商城购物使用积分'); //小于等于0 就不执行了
                     $member['integral'] = 0;
                }
            }
            $useint+= $order['use_integral'];
            $fan += $order['mobile_fan'];
            $total+= $order['total_price'];
			$express_price+= $order['express_price'];
			$coupon_price += $order['coupon_price'];
			
			//后期写这里才才正确
			$config = D('Setting')->fetchAll();//积分比例控制
			if($config['integral']['buy'] == 0){
				$useint_price = $useint;
			}else{
				$useint_price = $useint * $config['integral']['buy'];	
			}
			$total_fan = $total - $fan;//判断总价-手机下单返现>=积分兑换，默认积分还是扣除吧，暂时不去返回积分
			if($useint_price >= $total_fan ){
				$useint_price  = 0;
				D('Users')->addIntegral($uid,$useint_price,'商城购物扣除积分失败返回积分');//扣除积分失败积分退还
			}
			$total_fan_useint_price = $total - $fan - $useint_price;//判断总价-手机下单返现-积分兑换>优惠价的价格，这里后期加上返回优惠劵逻辑
			if($total_fan_useint_price <= $coupon_price ){
				$coupon_price  = 0;
				D('Order')->delete_order_download_id($order['order_id']); //使用优惠劵失败，退回优惠劵
			}
			
        }
		return $total - $fan - $useint_price - $coupon_price + $express_price;
    }
	//如果使用优惠劵抵扣失败删除表中优惠劵ID
	public function delete_order_download_id($order_id){	
		D('Order') -> save(array('download_id'=>0,'coupon_price'=>0,), array("where" => array('order_id' => $order_id)));	
	}
	
	public function goods_print($order_id,$address_id) {	
			$order_id = (int) $order_id;
			$addr_id = (int) $address_id;	
			$order = D('Order')->find($order_id);
			if($order['is_daofu'] == 1){
				$fukuan = '货到付款';
			}else{
				$fukuan = '已支付';
			}
			if (empty($order)){//没有找到订单返回假
            return false;
			}
            $member = D('Users')->find($order['user_id']);//会员信息
			if(!empty($address_id)){
				$address_id = $address_id;	
			}else{
				$address_id = $order['address_id'];
			}
			$user_addr = D('Paddress ')->where(array('id'=>$address_id))->find();
			$shop_print = D('Shop')->where(array('shop_id'=> $order['shop_id']))->find();//商家信息
			
			$msg .= '<MN>2</MN>\r';
			$msg .= '********************************\r';
			$msg .= '用户昵称：:' . $member['nickname'] . '\r';
            $msg .= '订单编号：:' . $order['order_id'] . '\r';
			$msg .= '下单时间：' . date('Y-m-d H:i:s', $order['create_time']) . '\r';
			$msg .= '********************************\r';
            $msg .= '<center>订单详情</center>\r';
			$products = D('Ordergoods')->where(array('order_id' => $order['order_id']))->select();
			foreach ($products as $key => $value) {
                $product = D('Goods')->where(array('goods_id' => $value['goods_id']))->find();
                $msg .= ($key+1).'.'.$product['title'].'.'.$product['key_name'].' * '.$value['num'].$product['guige'].'\r';
            }
			$msg .= '********************************\r';
            $msg .= '抵扣：积分' . round($order['use_integral']/ 100,2) . '元,手机下单立减' . round($order['mobile_fan']/ 100,2) . '元\r';
            $msg .= '订单总价：' . round($order['total_price']/ 100,2) .'元\r';
			$msg .= '配送费用：' . round($order['express_price']/ 100,2) .'元\r';
			$msg .= '@@2实际付款：' . round($order['need_pay']/ 100,2) .'元\r';
            $msg .= '@@2付款状态：' . $fukuan . '\r';
			$msg .= '********************************\r';
            $msg .= '配送地址：' .  $user_addr['area_str'] . '、' . $user_addr['info'] . '\r';
			$msg .= '联系信息：' . $user_addr['xm'] .' - ' . $user_addr['tel'] . '\r';
			$msg .= '********************************\r';
			$msg .= '商家名称：' . $shop_print['shop_name'] .'\r';
            $msg .= '配货电话：' . $shop_print['tel'] . '\r';
			$msg .= '配货地址：' . $shop_print['addr'] . '\r';
			$msg .= '备注：\r';
			$msg .= '\r';
            
			return $msg;//返回数组
   }
	public function orderdel_print($order_id) {	
			$order_id = (int) $order_id;
			$order = D('Order')->find($order_id);
			if (empty($order)){//没有找到订单返回假
            return false;
			}
			$msg .= '********************************\r';
            $msg .= '<center>订单取消通知</center>\r';
			$msg .= '订单编号：:' . $order['order_id'] . '订单已取消\r';
			return $msg;//返回数组
   }
   //下面的暂时懒得做
	public function tuihuo_print($order_id) {	
			$order_id = (int) $order_id;
			$order = D('Order')->find($order_id);
			if (empty($order)){
            return false;
			}
			$msg .= '********************************\r';
            $msg .= '<center>订单退货通知</center>\r';
			$msg .= '订单编号：:' . $order['order_id'] . '订单已申请退货\r';
			return $msg;
   }
	public function qxtk_print($order_id) {	
			$order_id = (int) $order_id;
			$order = D('Order')->find($order_id);
			if (empty($order)){
            return false;
			}
			$msg .= '********************************\r';
            $msg .= '<center>订单取消退货通知</center>\r';
			$msg .= '订单编号：:' . $order['order_id'] . '订单已取消退货，请立即安排配送！\r';
			return $msg;
   }
   
   //商城万能打印接口
    public function combination_goods_print($order_ids) {
        if (is_array($order_ids)) {
            $order_ids = join(',', $order_ids);
            $Order = D('Order')->where("order_id IN ({$order_ids})")->select();
            foreach ($Order as $k => $v) {
                $this->goods_order_print($v['order_id']);
            }
        } else {
			//单商家
            $order_ids = (int) $order_ids;
            $Order = D('Order')->where('order_id =' . $order_ids)->select();
            foreach ($Order as $k => $v) {
               $this->goods_order_print($v['order_id']);
            }
        }
        return TRUE;
    }
	//正式打印
	public function goods_order_print($order_id) {
	        $goods_order = D('Order')->find($order_id);
			$goods_order_shop = D('Shop')->find($goods_order['shop_id']);
			if ($goods_order_shop['is_goods_print'] == 1) {
				$msg = $this-> goods_print($goods_order['order_id'], $goods_order['address_id']);
				$result = D('Print') -> printOrder($msg, $goods_order_shop['shop_id']);
				$result = json_decode($result);
				$backstate = $result -> state;
				if ($backstate == 1) {
					if($goods_order_shop['is_pei'] ==1){//1代表没开通配送确认发货步骤
						D('Order') -> save(array('status' => 2,'is_print'=>1), array("where" => array('order_id' => $goods_order['order_id'])));
						D('Ordergoods') -> save(array('status' => 1), array("where" => array('order_id' => $goods_order['order_id'])));
					}else{//如果是配送配送只改变打印状态
						D('Order') -> save(array('is_print'=>1), array("where" => array('order_id' => $goods_order['order_id'])));	
					}
				}	
		   }
		return TRUE;		
	}
	
	
    public function overOrder($order_id){
        //后台管理员可以直接确认2的
        $order = $this->find($order_id);
        if (empty($order)) {
            return false;
        }
        if ($order['status'] != 2 && $order['status'] != 3) {
            return false;
        }
        if ($this->save(array('status' => 8, 'order_id' => $order_id))) {
            $userobj = D('Users');
            $goods = D('Ordergoods')->where(array('order_id' => $order_id))->select();
            $shop = D('Shop')->find($order['shop_id']);
            if (!empty($goods)) {
                D('Ordergoods')->save(array('status' => 8), array('where' => array('order_id' => $order_id)));
                if ($order['is_daofu'] == 0) {
                    foreach ($goods as $val) {
                        $js_price = $val['total_price']  - $val['mobile_fan'];//结算价格减去模板立减
						$Goods = D('Goods')->find($val['goods_id']);
						$Goodscate = D('Goodscate')->find($Goods['cate_id']);
						if(!empty($Goodscate['rate'])){
							$settlement_price = $js_price - intval(($js_price * $Goodscate['rate'])/1000);//结算价格，运费不算扣点
						}else{
							return false;//未设置结算价格不让结算
						}
						if($shop['is_pei'] ==0){
							$money = $settlement_price + $val['express_price'];//如果是开通配送员结算运费
							D('Runningmoney')->add_express_price($val['order_id'],$val['express_price'],2);//运费结算给配送员
						}else{
					        $money = $settlement_price + $val['express_price'];//加上运费
						}
						$intro  = '商城购物结算：订单ID'.$val['order_id'];
                        if ($money > 0) {
                            D('Shopmoney')->add(array(
								'shop_id' => $order['shop_id'], 
								'city_id' => $shop['city_id'], 
								'area_id' => $shop['area_id'], 
								'order_id' => $val['order_id'], 
								'type' => 'goods', 
								'money' => $money, 
								'create_time' => NOW_TIME, 
								'create_ip' => get_client_ip(), 
								'intro' => $intro
							));
                            D('Users')->Money($shop['user_id'], $money,$intro);  //写入商户余额
                        }
                    }
                    // 购物积分奖励给买的人，这个开关在后台
                    D('Users')->gouwu($order['user_id'], $order['total_price'], '购物积分奖励');
					$config = D('Setting')->fetchAll();
					if(!empty($order['use_integral'])){
						if(!empty($config['integral']['mall_return_integral'])){
							D('Users')->return_integral($shop['user_id'], $order['use_integral'] , '商城用户积分兑换返还给商家');
						}
						$prestige = intval(($order['need_pay'] - $order['express_price'])/100);//返还声望
						if(!empty($config['prestige']['is_goods'])){
							D('Users')->reward_prestige($order['user_id'], $prestige,'商城购物返'.$config['prestige']['name']);
						}
						
					}
					
                }
				return true;
            }
            return true;
        }
        return false;
    }
	//后台退款跟商家退款逻辑封装
	public function implemented_refund($order_id){
		$order_id = (int) $order_id;
		$order = D('Order');
        $detail = $order->find($order_id);
		if ($detail['status'] != 4) {
             return false;
        }
		if (!empty($order_id)) {
			//返还余额
			$order->save(array('order_id' => $detail['order_id'], 'status' => 5)); //更改已退款状态
			$obj = D('Users');
			if ($detail['need_pay'] > 0) {
				$obj->addMoney($detail['user_id'], $detail['need_pay'], '商城退款，订单号：' . $detail['order_id']);
			}
			if ($detail['use_integral'] > 0) {
			   $obj->addIntegral($detail['user_id'], $detail['use_integral'], '商城退款积分返还，订单号：' . $detail['order_id']);
			}
			$this->order_goods_status($order_id);//更高订单表状态
			$this->goods_num($order_id); //增加库存
			D('Sms') -> goods_refund_user($order_id);//退款成功短信通知用户
			D('Weixintmpl')->weixin_shop_confirm_refund_user($order_id,2);//家政商家确认退款，传订单ID跟类型
        }else{
			return false;	
	   }
	   return TRUE;
	}
	
	
		
   //后台退款跟商家退款更新购物表的状态
   public function order_goods_status($order_id) {
       $order_id = (int) $order_id;
       $order_goods = D('Ordergoods')->where(array('order_id' => $order_id))->select();
			foreach ($order_goods as $k => $v){
				D('Ordergoods')->where('order_id =' . $v['order_id'])->setField('status', 3);
        }
      return TRUE;
    }
	
  //后台退款跟商家退款更新退款库存
   public function goods_num($order_id) {
       $order_id = (int) $order_id;
       $ordergoods = D('Ordergoods')->where('order_id =' . $order_id)->select();
       foreach ($ordergoods as $k => $v) {
       	 D('Goods')->updateCount($v['goods_id'], 'num', $v['num']);
       	 refresh_spec_stock($v['goods_id'],$v['key'],$v['num']);    
       }
      return TRUE;
    }
	
	

	
	
    public function money($bg_time, $end_time, $shop_id){
        $bg_time = (int) $bg_time;
        $end_time = (int) $end_time;
        $shop_id = (int) $shop_id;
        if (!empty($shop_id)) {
            $data = $this->query(" SELECT sum(total_price)/100 as price,FROM_UNIXTIME(create_time,'%m%d') as d from  " . $this->getTableName() . "   where status=8 AND create_time >= '{$bg_time}' AND create_time <= '{$end_time}' AND shop_id = '{$shop_id}'  group by  FROM_UNIXTIME(create_time,'%m%d')");
        } else {
            $data = $this->query(" SELECT sum(total_price)/100 as price,FROM_UNIXTIME(create_time,'%m%d') as d from  " . $this->getTableName() . "   where status=8 AND create_time >= '{$bg_time}' AND create_time <= '{$end_time}'  group by  FROM_UNIXTIME(create_time,'%m%d')");
        }
        $showdata = array();
        $days = array();
        for ($i = $bg_time; $i <= $end_time; $i += 86400) {
            $days[date('md', $i)] = '\'' . date('m月d日', $i) . '\'';
        }
        $price = array();
        foreach ($days as $k => $v) {
            $price[$k] = 0;
            foreach ($data as $val) {
                if ($val['d'] == $k) {
                    $price[$k] = $val['price'];
                }
            }
        }
        $showdata['d'] = join(',', $days);
        $showdata['price'] = join(',', $price);
        return $showdata;
    }
	
}