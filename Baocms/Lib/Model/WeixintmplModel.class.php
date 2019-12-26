<?php
class WeixintmplModel extends CommonModel{
	protected $pk   = 'tmpl_id';
    protected $tableName =  'weixin_tmpl';
	protected $_validate = array(
		array('title','2,20','模板标题2至10个字符！',Model::MUST_VALIDATE, 'length', Model::MODEL_BOTH),
		array('serial','/^\w{3,}$/','请输入正确的模板库编号！',Model::MUST_VALIDATE, 'regex', Model::MODEL_BOTH),
		array('status','0,1','状态值不合法,必须0或1！',Model::MUST_VALIDATE, 'in', Model::MODEL_BOTH),
		array('sort','/^\d{1,4}$/','排序值不合法！',Model::MUST_VALIDATE, 'regex', Model::MODEL_BOTH),
	);
	
	//抢单配送批量发送微信模板消息
	public function delivery_tz_user($order_id,$type){
		$order_id = array($order_id);
		$type = (int) $type;//0是商城，1是外卖，2是快递
		if($type == 0){
			$obj = D('Order');
			$info = '有新的商城订单啦~';
		}elseif($type == 1){
			$obj = D('Eleorder');
			$info = '有新的外卖订单啦~';
		}else{
			$obj = D('Express');
			$info = '有新的快递订单啦~';
		}
		$detail = $obj->find($order_id);
		$time = date("Y-m-d H:i:s",$detail['create_time']); //订单时间
		$delivery  = D('Delivery')->where(array('is_weixin'=>1))->select();
		
		$config = D('Setting')->fetchAll();
		
        foreach ($delivery as $v=>$val)  { 
            include_once "Baocms/Lib/Net/Wxmesg.class.php";
            $_delivery_tz_user = array(//整体变更
                'url'       =>  $config['site']['host']."/delivery/lists/scraped.html",
                'topcolor'  =>  '#F55555',
                'first'     =>  $val['name'].'订单生成日期：'.$time .'',
                'remark'    =>  '更多信息,请登录'.$config['site']['sitename'].',将为您提供更多信息服务！',
                'nickname'  =>  $val['name'],
                'title'     =>  $info

         );
         $delivery_tz_user_data = Wxmesg::delivery_tz_user($_delivery_tz_user);
         $return = Wxmesg::net($val['user_id'], 'OPENTM207042342', $delivery_tz_user_data);//结束
       } 
        return true;
    }
	
	
	//套餐下单微信通知
    public function weixin_notice_tuan_user($order_id,$user_id,$type){
            $Tuanorder = D('Tuanorder')->find($order_id);
		    $Tuan = D('Tuan')->find($order['tuan_id']);
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
            include_once 'Baocms/Lib/Net/Wxmesg.class.php';
            $notice_data = array(
				'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/user/tuan/detail/order_id/' . $order_id . '.html', 
				'first' => '亲,您的订单创建成功!', 
				'remark' => '详情请登录-http://' . $_SERVER['HTTP_HOST'], 
				'order_id' => $order_id, 
				'title' => $Tuan['title'], 
				'num' => $Tuanorder['num'],
				'price' => round($Tuanorder['need_pay'] / 100, 2) . '元', 
				'pay_type' => $pay_type 
			);
			
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id, 'OPENTM202297555', $notice_data);
			return true;
    }
	
	//商城下单微信通知
    public function weixin_notice_goods_user($order_id,$user_id,$type){
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
			$Order = D('Order')->find($order_id);
			$num = D('Ordergoods')->where(array('order_id'=>$order_id))->sum('num');
			$goods_name = $this->get_mall_order_goods_name($order_id);//获取商城订单名称
            include_once 'Baocms/Lib/Net/Wxmesg.class.php';
            $notice_data = array(
				'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/user/goods/index/aready/' . $order_id . '.html', 
				'first' => '亲,您的订单创建成功!', 
				'remark' => '详情请登录-http://' . $_SERVER['HTTP_HOST'], 
				'order_id' => $order_id, 
				'title' => $goods_name, 
				'num' => $num,
				'price' => round($Order['need_pay'] / 100, 2) . '元', 
				'pay_type' => $pay_type 
			);
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id,'OPENTM202297555', $notice_data);
			return true;
    }
	
	//订座下单微信通知
    public function weixin_notice_booking_user($order_id,$user_id,$type){
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
			$Bookingorder = D('Bookingorder')->find($order_id);
			$Booking = D('Booking')->find($Bookingorder['shop_id']);
			
            include_once "Baocms/Lib/Net/Wxmesg.class.php";
            $notice_data = array(
                'url'       =>  "http://".$_SERVER['HTTP_HOST']."/user/booking/detail/order_id/".$order_id.".html",
                'first'   => '亲,您的订单创建成功!',
                'remark'  => '详情请登录-http://'.$_SERVER['HTTP_HOST'],
				'order_id' => $order_id, 
				'title' => $Booking['shop_name'], 
				'num' => '1',
				'price' => round($Bookingorder['amount'] / 100, 2) . '元', 
				'pay_type' => $pay_type 
            );
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id, 'OPENTM202297555', $notice_data);
			return true;
    }
	
	//酒店下单微信通知
    public function weixin_notice_hotel_user($order_id,$user_id,$type){
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
			$Hotelorder = D('Hotelorder')->find($order_id);
			$Hotel = D('Hotel')->find($Hotelorder['hotel_id']);
            include_once "Baocms/Lib/Net/Wxmesg.class.php";
            $notice_data = array(
                'url'       =>  "http://".$_SERVER['HTTP_HOST']."/user/hotel/detail/order_id/".$order_id.".html",
                'first'   => '亲,您的订单创建成功!',
                'remark'  => '详情请登录-http://'.$_SERVER['HTTP_HOST'],
				'order_id' => $order_id, 
				'title' => $Hotel['hotel_name'], 
				'num' => '1',
				'price' => round($Hotelorder['amount'] / 100, 2) . '元', 
				'pay_type' => $pay_type 
            );
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id, 'OPENTM202297555', $notice_data);
			return true;
    }
	
	//农家乐下单微信通知
    public function weixin_notice_farm_user($order_id,$user_id,$type){
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
			$Farmgorder = D('Farmorder')->find($order_id);
			$Farm = D('Farm')->find($Farmorder['farm_id']);
            include_once "Baocms/Lib/Net/Wxmesg.class.php";
            $notice_data = array(
                'url'       =>  "http://".$_SERVER['HTTP_HOST']."/user/hotel/detail/order_id/".$order_id.".html",
                'first'   => '亲,您的订单创建成功!',
                'remark'  => '详情请登录-http://'.$_SERVER['HTTP_HOST'],
				'order_id' => $order_id, 
				'title' => $Farm['farm_name'], 
				'num' => '1',
				'price' => round($Farmorder['amount'] / 100, 2) . '元', 
				'pay_type' => $pay_type 
            );
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id, 'OPENTM202297555', $notice_data);
			return true;
    }
	
	//外卖下单微信通知
    public function weixin_notice_ele_user($order_id,$user_id,$type){
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
			$order = D('Eleorder')->find($order_id);
            $product_name = $this->get_ele_order_product_name($order_id);
            include_once 'Baocms/Lib/Net/Wxmesg.class.php';
            $notice_data = array(
				'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/user/eleorder/detail/order_id/' . $order_id . '.html', 
				'first' => '亲,您的订单创建成功!', 
				'remark' => '详情请登录-http://' . $_SERVER['HTTP_HOST'], 
				'order_id' => $order_id, 
				'title' => $product_name, 
				'num' => $order['num'],
				'price' => round($order['need_pay'] / 100, 2) . '元', 
				'pay_type' => $pay_type 
			);
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id,'OPENTM202297555', $notice_data);
			return true;
    }
	
	//买家申请退款通知商家1外卖，2商城，3家政，4团购
    public function weixin_user_refund_shop($order_id,$type){
		    $config_site_url = 'http://' . $_SERVER['HTTP_HOST'] . '/distributors/';
			if($type == 1){
				$type_refund_name = '外卖退款' ;
				$url = $config_site_url.'ele/detail/order_id/'.$logs['order_id'].'/';
				$Eleorder = D('Eleorder')->find($order_id);
				$Shop = D('Shop')->find($Eleorder['shop_id']);
				$product_name = $this->get_ele_order_product_name($order_id);
				$refund_titie = $product_name ;
				$refund_num = $Eleorder['mum'];
				$refund_price = round($Eleorder['need_pay']/100,2).'元';
			}elseif($type == 2){
				$type_refund_name = '商城退款' ;
				$url = $config_site_url.'mart/detail/order_id/'.$logs['order_id'].'/';  
				$Order = D('Order')->find($order_id);
				$Shop = D('Shop')->find($Order['shop_id']);
				$num = D('Ordergoods')->where(array('order_id'=>$order_id))->sum('num');
				$goods_name = $this->get_mall_order_goods_name($order_id);//获取商城订单名称
				$refund_titie = $goods_name ;
				$refund_num = $num;
				$refund_price = round($Order['need_pay']/100,2).'元';
			}elseif($type == 3){
				$type_refund_name = '家政退款' ;
				$url = $config_site_url;
				$Appointorder = D('Appointorder')->find($order_id);
			    $Appoint= D('Appoint')->find($Appointorder['appoint_id']);
				$Shop = D('Shop')->find($Appointorder['shop_id']);
				$refund_titie = $Appoint['title'];
				$refund_num = '1';
				$refund_price = round($Appointorder['need_pay']/100,2).'元';
			}elseif($type == 4){
				$type_refund_name = '团购退款' ;
				$url = $config_site_url.'tuan/detail/order_id/'.$logs['order_id'].'/';
				$Tuancode = D('Tuancode')->find($code_id);
			    $Tuan= D('Tuan')->find($Tuancode['tuan_id']);
				$Shop = D('Shop')->find($Tuancode['shop_id']);
				$refund_titie = $Tuan['title'];
				$refund_num = '1';
				$refund_price = round($Tuancode['real_money']/100,2).'元';
			}
            include_once 'Baocms/Lib/Net/Wxmesg.class.php';
            $_order_refund_data = array(
				'url' => $url, 
				'first' => $type_refund_name.'通知提醒', 
				'remark' => '详情请登录：http://' . $_SERVER['HTTP_HOST'], 
				'order_id' => $order_id, //订单ID
				'refund_titie' => $refund_titie , //退款名称
				'refund_num' => $refund_num,//退款数量
				'refund_price' => $refund_price, //退款金额
			);
			//买家退款通知商家,模板OPENTM要修改
            $order_refund_data = Wxmesg::place_an_order($_order_refund_data);
            Wxmesg::net($Shop['user_id'],'OPENTM202297555', $order_refund_data);
			return true;
    }
	
	//商家确认退款微信通知买家1外卖，2商城，3家政，4团购
    public function weixin_shop_confirm_refund_user($order_id,$type){
		    $config_site_url = 'http://' . $_SERVER['HTTP_HOST'] . '/user/';
			if($type == 1){
				$type_confirm_refund_name = '外卖' ;
				$url = $config_site_url.'eleorder/detail/order_id/'.$logs['order_id'].'/'; 
				$Eleorder = D('Eleorder')->find($order_id);
				$Users = D('Users')->find($Eleorder['user_id']);
				$confirm_refund_price = round($Eleorder['need_pay']/100,2).'元';
			}elseif($type == 2){
				$type_confirm_refund_name = '商城' ;
				$url = $config_site_url.'goods/detail/order_id/'.$logs['order_id'].'/'; 
				$Order = D('Order')->find($order_id);
				$Users = D('Users')->find($Order['user_id']);
				$confirm_refund_price = round($Order['need_pay']/100,2).'元';
			}elseif($type == 3){
				$type_confirm_refund_name = '家政' ;
				$url = $config_site_url.'appoint/detail/order_id/'.$logs['order_id'].'/';
				$Appointorder = D('Appointorder')->find($order_id);
			    $Users = D('Users')->find($Appointorder['user_id']);
				$confirm_refund_price = round($Appointorder['need_pay']/100,2).'元';
			}elseif($type == 3){
				$type_confirm_refund_name = '团购' ;
				$url = $config_site_url.'tuan/';
				$Tuancode = D('Tuancode')->find($code_id);
			    $Users = D('Users')->find($Tuancode['user_id']);
				$confirm_refund_price = round($Tuancode['real_money']/100,2).'元';
			}
		   $config = D('Setting')->fetchAll();
           include_once "Baocms/Lib/Net/Wxmesg.class.php";
           $_confirm_refund_data_balance = array(
				'url' => $url, 
				'topcolor' => '#F55555', 
				'first' => '您的账户有退款信息，信息如下：', 
				'remark' => '如对上述余额变动有异议，请联系'.$config['site']['sitename'].'客服人员协助处理。' . $config['site']['tel'], 
				'accountType' => $config['site']['sitename'].'会员账户', 
				'operateType' => $type_confirm_refund_name.'费用变动', 
				'operateInfo' => $type_confirm_refund_name.'成功退款', 
				'price' => '+' . $confirm_refund_price . '元', 
				'balance' => round($Users['money']/100,2). '元'
			);
            $confirm_refund_data_balance = Wxmesg::pay($_confirm_refund_data_balance);
            $return = Wxmesg::net($logs['user_id'], 'OPENTM201495900', $confirm_refund_data_balance);
			return true;
    }
	
	//商家发货通知买家万能接口1外卖，2商城，3家政，4团购
    public function weixin_shop_delivery_user($order_id,$user_id,$type){
		    $config_site_url = 'http://' . $_SERVER['HTTP_HOST'] . '/user/';
			if($type == 1){
				$type_delivery_name = '外卖发货' ;
				$url = $config_site_url.'eleorder/detail/order_id/'.$logs['order_id'].'/'; 
				$order = D('Eleorder')->find($order_id);
				$product_name =$this->get_ele_order_product_name($order_id);
				$title = $product_name ;
				$num = $order['mum'];
				$price = round($order['need_pay']/100,2).'元';
				if($Order['is_daofu'] ==0){
					$pay_type = '在线付款';
				}else{
					$pay_type = '货到付款';
				}
			}elseif($type == 2){
				$type_delivery_name = '商城发货' ;
				$url = $config_site_url.'goods/detail/order_id/'.$logs['order_id'].'/'; 
				$Order = D('Order')->find($order_id);
				$num = D('Ordergoods')->where(array('order_id'=>$order_id))->sum('num');
				$goods_name = $this->get_mall_order_goods_name($order_id);//获取商城订单名称
				$title = $goods_name ;
				$num = $num;
				$price = round($Order['need_pay']/100,2).'元';
				if($Order['is_daofu'] ==0){
					$pay_type = '在线付款';
				}else{
					$pay_type = '货到付款';
				}
			}elseif($type == 3){
				$type_delivery_name = '家政发货' ;
				$url = $config_site_url.'appoint/detail/order_id/'.$logs['order_id'].'/';
				$Appointorder = D('Appointorder')->find($logs['order_id']);
			    $Appoint= D('Appoint')->find($Appointorder['appoint_id']);
				$title = $Appoint['title'] ;
				$num = '改家政已被预约：'.$Appointorder['buy_num'].'次';
				$price = round($Appointorder['need_pay']/100,2).'元';
				$pay_type = '在线支付';
			}
		
            include_once 'Baocms/Lib/Net/Wxmesg.class.php';
            $_order_delivery_data = array(
				'url' => $url, 
				'first' => $type_delivery_name.'发货通知提醒', 
				'remark' => '详情请登录：http://' . $_SERVER['HTTP_HOST'], 
				'order_id' => $order_id, 
				'title' => $title , 
				'num' => $num,
				'price' => $price, 
				'pay_type' => $pay_type 
			);
            $order_delivery_data = Wxmesg::place_an_order($_order_delivery_data);
            Wxmesg::net($user_id,'OPENTM202297555', $order_delivery_data);
			return true;
    }
	
	//根据订单ID获取外卖订单名称
	public function get_ele_order_product_name($order_id){
		    $order = D('Eleorder')->find($order_id);
            $product_ids = D('Eleorderproduct')->where('order_id=' . $order_id)->getField('product_id', true);
            $product_ids = implode(',', $product_ids);
            $map = array('product_id' => array('in', $product_ids));
            $product_name = D('Eleproduct')->where($map)->getField('product_name', true);
            $product_name = implode(',', $product_name);
			return $product_name;
		 
    }
	//根据订单ID获取商城订单名称
	public function get_mall_order_goods_name($order_id){
		    $Order = D('Order')->find($order_id);
			$goods_ids = D('Ordergoods')->where("order_id={$order_id}")->getField('goods_id', true);
			$goods_ids = implode(',', $goods_ids);
			$map = array('goods_id' => array('in', $goods_ids));
			$goods_name = D('Goods')->where($map)->getField('title', true);
			$goods_name = implode(',', $goods_name);
			return $goods_name;
		 
    }
	
	//支付成功余额变化通知
    public function weixin_pay_balance_user($log_id){
		   $logs = D('Paymentlogs')->find($log_id);
		   $config_site_url = 'http://' . $_SERVER['HTTP_HOST'] . '/user/';
		   if($logs['type'] == 'tuan'){
			  $type_name = '套餐'; 
			  $url = $config_site_url.'tuan/detail/order_id/'.$logs['order_id'].'/';
		   }elseif($logs['type'] == 'ele'){
			  $type_name = '订餐';  
			  $url = $config_site_url.'eleorder/detail/order_id/'.$logs['order_id'].'/'; 
		   }elseif($logs['type'] == 'booking'){
			  $type_name = '订座';   
			  $url = $config_site_url.'booking/detail/order_id/'.$logs['order_id'].'/'; 
		   }elseif($logs['type'] == 'goods'){
			  $type_name = '商城';  
			  $url = $config_site_url.'goods/detail/order_id/'.$logs['order_id'].'/';  
		   }elseif($logs['type'] == 'breaks'){
			  $type_name = '优惠买单';  
			  $url = $config_site_url.'breaks/index/';  
		   }elseif($logs['type'] == 'hotel'){
			  $type_name = '酒店';  
			  $url = $config_site_url.'hotel/detail/order_id/'.$logs['order_id'].'/';  
		   }elseif($logs['type'] == 'crowd'){
			  $type_name = '众筹';   
			  $url = $config_site_url.'crowd/detail/order_id/'.$logs['order_id'].'/'; 
		   }elseif($logs['type'] == 'farm'){
			  $type_name = '农家乐';  
			  $url = $config_site_url.'farm/detail/order_id/'.$logs['order_id'].'/';  
		   }elseif($logs['type'] == 'appoint'){
			  $type_name = '家政';  
			  $url = $config_site_url.'appoint/detail/order_id/'.$logs['order_id'].'/';  
		   }elseif($logs['type'] == 'money'){
			  $type_name = '充值'; 
			  $url = $config_site_url.'logs/moneylogs/';   
		   }elseif($logs['type'] == 'running'){
			  $type_name = '跑腿'; 
			  $url = $config_site_url.'running/detail/running_id/'.$logs['order_id'].'/';   
		   }elseif($logs['type'] == 'cloud'){
			  $type_name = '云购';  
			  $url = $config_site_url.'cloud/detail/log_id/'.$logs['order_id'].'/';  
		   }
		   $config = D('Setting')->fetchAll();
		   $users = D('Users')->find($logs['user_id']);
           $price = round($logs['need_pay'] / 100, 2);
           $balance = round($users['money'] / 100, 2);
           include_once "Baocms/Lib/Net/Wxmesg.class.php";
           $_data_balance = array(
				'url' => $url, 
				'topcolor' => '#F55555', 
				'first' => '您的账户余额发生变动，信息如下：', 
				'remark' => '如对上述余额变动有异议，请联系'.$config['site']['sitename'].'客服人员协助处理。' . $config['site']['tel'], 
				'accountType' => $config['site']['sitename'].'会员账户', 
				'operateType' => $type_name.'费用支出', 
				'operateInfo' => $type_name.'购物消费', 
				'price' => '-' . $price . '元', 
				'balance' => $balance . '元'
			);
            $balance_data = Wxmesg::pay($_data_balance);
            $return = Wxmesg::net($logs['user_id'], 'OPENTM201495900', $balance_data);
			return true;
    }
	
	//买家已付款通知商家，OPENTM401973756
    public function weixin_pay_balance_shop($log_id,$type){
			if($type == 0){
				$order_ways = '货到付款' ;
			}else{
				$order_ways = '在线支付' ;
			}
		   $logs = D('Paymentlogs')->find($log_id);
		   $config_site_url = 'http://' . $_SERVER['HTTP_HOST'] . '/distributors/';
		   if($logs['type'] == 'tuan'){
			  $Tuanorder = D('Tuanorder')->find($logs['order_id']);
			  $Tuan = D('Tuan')->find($Tuanorder['tuan_id']);
			  $Shop = D('Shop')->find($Tuanorder['shop_id']);
			  $Users = D('Users')->find($Tuanorder['user_id']);
			  $type_name = '套餐'; 
			  $url = $config_site_url.'tuan/detail/order_id/'.$logs['order_id'].'/';
			  $shop_name = $Shop['shop_name'];
			  $order_goods = $Tuan['title'];
			  $order_price = round($Tuanorder['need_pay']/100,2).'元';
			  $order_user_information = $Users['nickname'];
		   }elseif($logs['type'] == 'ele'){
			  $Eleorder = D('Eleorder')->find($logs['order_id']);
			  $Shop = D('Shop')->find($Eleorder['shop_id']);
              $product_name = $this->get_ele_order_product_name($order_id);//获取外卖订单名称
			  $Useraddr = D('Useraddr')->find($Eleorder['addr_id']);
			  $type_name = '订餐';  
			  $url = $config_site_url.'ele/detail/order_id/'.$logs['order_id'].'/';
			  $shop_name = $Shop['shop_name'];
			  $order_goods = $product_name;
			  $order_price = round($Eleorder['need_pay']/100,2).'元，其中配送费：'.round($Eleorder['logistics']/100,2).'元';
			  $order_user_information = $Useraddr['name'].'---'.$Useraddr['mobile'].'---'.$Useraddr['addr'];
		   }elseif($logs['type'] == 'booking'){
			  $Bookingorder = D('Bookingorder')->find($logs['order_id']);
			  $Booking = D('Booking')->find($Bookingorder['shop_id']); 
			  $Bookingroom = D('Bookingroom')->find($Bookingorder['room_id']); 
			  $Shop = D('Shop')->find($Bookingorder['shop_id']);  
			  $type_name = '订座';   
			  $url = $config_site_url; 
			  $shop_name = $Shop['shop_name'];
			  $order_goods = $Booking['shop_name'].'包厢名称：'.$Bookingroom['name'];
			  $order_price = '定金：'.round($order['amount']/100,2).'元';
			  $order_user_information = $Bookingorder['name'].'---'.$Bookingorder['mobile'].'预订时间：'.$Useraddr['ding_date'].'-'.$Useraddr['ding_time'];
		   }elseif($logs['type'] == 'goods'){
			  $Order = D('Order')->find($logs['order_id']);
			  $Shop = D('Shop')->find($Order['shop_id']); 
			  $Paddress = D('Paddress')->find($Order['address_id']); 
			  $goods_name = $this->get_mall_order_goods_name($logs['order_id']);//获取商城订单名称
			  $num = D('Ordergoods')->where(array('order_id'=>$order_id))->sum('num');
			  $type_name = '商城';  
			  $url = $config_site_url.'mart/detail/order_id/'.$logs['order_id'].'/';  
			  $shop_name = $Shop['shop_name'];
			  $order_goods = $goods_name;
			  $order_price = '实付：'.round($Order['need_pay']/100,2).'元';
			  $order_user_information = '购买人地址：'.$Paddress['xm'].'--'.$Paddress['tel'].'--'.$Paddress['area_str'].'--'.$Paddress['info'];
			  
		   }elseif($logs['type'] == 'breaks'){
			  $Breaksorder = D('Breaksorder')->find($logs['order_id']);
			  $Shop = D('Shop')->find($Breaksorder['shop_id']); 
			  $Users = D('Users')->find($Breaksorder['user_id']);
			  $type_name = '优惠买单';  
			  $url = $config_site_url;  
			  $shop_name = $Shop['shop_name'];
			  $order_goods = '优惠买单';
			  $order_price = '买单金额：'.$Breaksorder['need_pay'].'元';
			  $order_user_information = '买单人姓名：'.$Users['nickname'].'，买单人手机：'.$Users['mobile'];
		   }elseif($logs['type'] == 'hotel'){
			  $Hotelorder = D('Hotelorder')->find($logs['order_id']);
			  $Hotel = D('Hotel')->find($Hotelorder['hotel_id']);
			  $Shop = D('Shop')->find($Hotel['shop_id']); 
			  $Hotelroom = D('Hotelroom')->find($Hotelorder['room_id']);
			  $type_name = '酒店';  
			  $url = $config_site_url;   
			  $shop_name = $Shop['shop_name'];
			  $order_goods = '房型：'.$Hotelroom['title'];
			  $order_price = '付款金额：'.round($Hotelorder['amount']/100,2).'元';
			  $order_user_information = $Hotelorder['name'].'--'.$Hotelorder['mobile'].'入驻时间：'.$Hotelorder['stime'].'退房时间：'.$Hotelorder['ltime'];
		   }elseif($logs['type'] == 'crowd'){
			  $Crowdorder = D('Crowdorder')->find($logs['order_id']);
			  $Crowd = D('Crowd')->find($Crowdorder['goods_id']);
			  $Users = D('Users')->find($Crowd['uid']); 
			  $Paddress = D('Paddress')->find($Crowdorder['address_id']); 
			  $type_name = '众筹';   
			  $url = $config_site_url;  
			  $shop_name = $Users['nickname'];
			  $order_goods = $Crowd ['title'];
			  $order_price = '众筹金额：'.round($Crowdorder['need_pay']/100,2).'元';
			  $order_user_information = '参与人地址：'.$Paddress['xm'].'--'.$Paddress['tel'].'--'.$Paddress['area_str'].'--'.$Paddress['info'];
		   }elseif($logs['type'] == 'farm'){
			  $Farmgorder = D('Farmorder')->find($logs['order_id']);
			  $Farm = D('Farm')->find($Farmorder['farm_id']);
			  $Shop = D('Shop')->find($Farm['shop_id']); 
			  $Farmpackage = D('Farmpackage')->find($Farmgorder['pid']);
			  $type_name = '农家乐';  
			  $url = $config_site_url;  
			  $shop_name = $Farm['farm_name'];
			  $order_goods = '农家乐套餐：'.$Farmpackage ['title'];
			  $order_price = '付款金额：'.round($Farmgorder['amount']/100,2).'元';
			  $order_user_information = '购买人信息：'.$Farmgorder['name'].'--'.$Farmgorder['mobile'].'--'.$Farmgorder['note'];
		   }elseif($logs['type'] == 'appoint'){
			  $Appointorder = D('Appointorder')->find($logs['order_id']);
			  $Appoint= D('Appoint')->find($Appointorder['appoint_id']);
			  $Shop = D('Shop')->find($Appoint['shop_id']); 
			  $type_name = '家政';  
			  $url = $config_site_url;  
			  $shop_name = $Shop['shop_name'];
			  $order_goods = '家政名称：'.$Appoint['title'];
			  $order_price = '家政定金：'.round($Appointorder['need_pay']/100,2).'元';
			  $order_user_information = '预约信息：'.$Appointorder['name'].'-'.$Appointorder['mobile'].'-'.$Appointorder['addr'].'时间'.$Appointorder['svctime'];
		   }elseif($logs['type'] == 'cloud'){
			  $Cloudlogs = D('Cloudlogs')->where(array('log_id'=>$logs['order_id']))->find();
			  $Cloudgoods= D('Cloudgoods')->find($Cloudlogs['goods_id']);
			  $Shop = D('Shop')->find($Cloudgoods['shop_id']); 
			  $Users = D('Users')->find($Cloudgoods['user_id']); 
			  $type_name = '云购';  
			  $url = $config_site_url;  
			  $shop_name = $Shop['shop_name'];
			  $order_goods = '云购名称：'.$Cloudgoods['title'];
			  $order_price = '云购金额：'.round($Cloudlogs['money']/100,2).'元';
			  $order_user_information = '购买人信息：'.$Users['nickname'].'-'.$Users ['mobile'];
		   }
		   $config = D('Setting')->fetchAll();
           include_once "Baocms/Lib/Net/Wxmesg.class.php";
           $_data_order_notice = array(
				'url' => $url, 
				'topcolor' => '#F55555', 
				'first' => $type_name .'订单通知商家，信息如下：', 
				'remark' => '尊敬的【'.$shop_name.'】，您有一笔新订单！如有问题请联系'.$config['site']['sitename'].'客服人员协助处理。' . $config['site']['tel'], 
				'order_id' => $logs['order_id'], 
				'order_goods' => $order_goods, 
				'order_price' => $order_price, 
				'order_ways' => $order_ways, 
				'order_user_information' => $order_user_information
			);
            $order_notice = Wxmesg::order_notice_shop($_data_order_notice);
            $return = Wxmesg::net($Shop['user_id'], 'OPENTM401973756', $order_notice);
			return true;
    }
	
	//买家取消【删除】订单通知商家：1外卖，2商城，3家政，4团购
    public function weixin_delete_order_shop($order_id,$user_id,$type){
		    $config_site_url = 'http://' . $_SERVER['HTTP_HOST'] . '/distributors/';
			if($type == 1){
				$type_delete_order_name = '外卖' ;
				$url = $config_site_url.'eleorder/detail/order_id/'.$logs['order_id'].'/'; 
				$Eleorder = D('Eleorder')->find($order_id);
				$Useraddr = D('Useraddr')->find($Eleorder['addr_id']);
				$product_name = $this->get_ele_order_product_name($order_id);
				
				$delete_order_title = $product_name ;
				$delete_order_price = round($order['need_pay']/100,2).'元';
				if($Order['is_daofu'] ==0){
					$delete_order_pay_type = '在线付款';
				}else{
					$delete_order_pay_type = '货到付款';
				}
				$delete_order_user_information = $Useraddr['name'].'---'.$Useraddr['mobile'].'---'.$Useraddr['addr'];
			}elseif($type == 2){
				$type_delete_order_name = '商城' ;
				$url = $config_site_url.'goods/detail/order_id/'.$logs['order_id'].'/'; 
				$Order = D('Order')->find($order_id);
				$Paddress = D('Paddress')->find($Order['address_id']); 
				$goods_name = $this->get_mall_order_goods_name($order_id);//获取商城订单名称
				
				$delete_order_title = $goods_name ;
				$delete_order_price= $num;
				$price = round($Order['need_pay']/100,2).'元';
				if($Order['is_daofu'] ==0){
					$delete_order_pay_type= '在线付款';
				}else{
					$delete_order_pay_type = '货到付款';
				}
				$delete_order_user_information = '购买人地址：'.$Paddress['xm'].'--'.$Paddress['tel'].'--'.$Paddress['area_str'].'--'.$Paddress['info'];
			}elseif($type == 3){
				$type_delete_order_name = '家政' ;
				$url = $config_site_url;
				$Appointorder = D('Appointorder')->find($logs['order_id']);
			    $Appoint= D('Appoint')->find($Appointorder['appoint_id']);
				
				$delete_order_title = $Appoint['title'] ;
				$delete_order_price = round($Appointorder['need_pay']/100,2).'元';
				$delete_order_pay_type = '在线支付';
				$delete_order_user_information = $Appointorder['name'].'-'.$Appointorder['mobile'].'-'.$Appointorder['addr'].'时间'.$Appointorder['svctime'];
			}elseif($type == 3){
				$type_delete_order_name = '团购' ;
				$url = $config_site_url.'tuan/';
				$Tuancode = D('Tuancode')->find($code_id);
				$Tuan = D('Tuan')->find($tuan_id);
				$Users = D('Users')->find($Tuancode['user_id']);
				
				$delete_order_title = $Tuan['tuan_id'];
				$delete_order_price = round($Tuancode['real_money']/100,2).'元';
				$delete_order_pay_type = '在线支付';
				$delete_order_user_information = $Users['nickname'];;
			}
		    $config = D('Setting')->fetchAll();
            include_once "Baocms/Lib/Net/Wxmesg.class.php";
            $_data_delete_order_notice = array(
				'url' => $url, 
				'topcolor' => '#F55555', 
				'first' => $type_delete_order_name .'订单取消通知商家，信息如下：', 
				'remark' => '尊敬的【'.$shop_name.'】，您有一笔订单用户已取消！如有问题请联系'.$config['site']['sitename'].'客服人员协助处理。' . $config['site']['tel'], 
				'order_id' => $order_id, 
				'order_goods' => $delete_order_title, 
				'order_price' => $delete_order_price, 
				'order_ways' => $delete_order_pay_type, 
				'order_user_information' => $delete_order_user_information,
			 );
             $data_delete_order_notice = Wxmesg::order_notice_shop($_data_delete_order_notice);
             $return = Wxmesg::net($user_id, 'OPENTM401973756', $data_delete_order_notice);
			 return true;
    }
	
	//会员提现，审核，拒绝，通知会员自己
 	 public function weixin_cash_user($user_id,$tpye){
		if($tpye ==1){
			$tpye_name = '您已经成功申请提现'; 
		}elseif($tpye ==2){
			$tpye_name = '您的提现已通过审核'; 
		}elseif($tpye ==3){
			$tpye_name = '您的提现被拒绝，请关注您的账户'; 
		}
		$Users = D('Users')->find($user_id);
		$t = time(); 
        include_once "Baocms/Lib/Net/Wxmesg.class.php";
        $_cash_data = array(
             'url'       =>  "http://".$_SERVER['HTTP_HOST']."/user/",
             'first'   => $tpye_name,
             'remark'  => '详情请登录-http://'.$_SERVER['HTTP_HOST'],
             'balance'  => '您的余额：'.round($Users['money']/100,2).'元',
             'time'   => '操作时间：'.$t,
          );
         $cash_data = Wxmesg::cash($_cash_data);
	      Wxmesg::net($user_id, 'OPENTM206909003', $cash_data);
		
	}




}