<?php
class AppointorderModel  extends  CommonModel{
    protected $pk   = 'order_id';
    protected $tableName =  'appoint_order';
    
	protected $types = array(
		0 => '等待付款', 
		1 => '已付款', 
		2 => '已接单', 
		3 => '退款中', 
		4 => '已退款', 
		8 => '已完成'
	);
	
    public function getType(){
        return $this->types;
    }
	//检测阿姨端的订单状态
	public function Appoint_order_Distribution($order_id, $type=''){
		$order_id = (int)$order_id;
        $type = (int)$type;
		$obj = D('Appointorder');	
		$Appointorders = D('Appointorders');	
		$appoint_order = $obj->where('order_id =' . $order_id)->find();
		$appoint = D('Appoint')->find($appoint_order['appoin_id']);
	
		if($appoint['is_orders'] == 1) {//如果走阿姨端
			$appoint_orders = D('Appointorders')->where(array('appoint_order_id' => $order_id, 'type' => $type))->find();
			if (!empty($appoint_orders)) {
				if ($appoint_orders['closed'] ==0 ) {//如果订单状态是关闭
					$Appointorders->where(array('appoint_order_id' => $order_id, 'type' => $type))->setField('closed', 0); //重新开启订单
				}else{
					if($appoint_orders['status'] == 2 || $appoint_orders['status'] == 8) {
						return false;
					}else{
						$Appointorders->where(array('appoint_order_id' => $order_id, 'type' => 0))->setField('closed', 1);//更改阿姨抢单状态
					}	
			   }
			}else{
				return false;
			}
		  return true;
		}	
	   return true;
		
	}
	//返回详情
	public function detail($id){
        $id = (int)$id;
        $data = $this->find($id);
        if(empty($data)){
            $data = array('id'=>$id);
            $this->add($data);
        }
        return $data;
    }
   //更新预约数量
    public function updateCount_yuyue_num($order_id){
        $order_id = (int)$order_id;
		$Appoint = D('Appoint')->where(array('order_id'=>$order_id))->find();;//查找日志
        D('Appoint')->updateCount($Appoint['appoint_id'], 'yuyue_num');	
        return true;
    }
	
	//家政退款给用户封装
    public function refund_user($order_id){
		$order_id = (int)$order_id;
		if(empty($order_id)){
			 return false;
		}
		$detail = $this->find($order_id);
		if(!empty($detail)){
			$Appoint = D('Appoint')->find($detail['appoint_id']);
            if(false !== $this->save(array('order_id'=>$order_id,'status'=>4))){
				D('Sms')->sms_appoint_refund_user($order_id);//家政退款通知用户手机
				D('Weixintmpl')->weixin_shop_confirm_refund_user($order_id,3);//家政商家确认退款，传订单ID跟类型
				$info = '家政申请退款，订单号：'.$order_id;
                D('Users')->addMoney($detail['user_id'], $detail['need_pay'], $info);//给用户增加金额
                return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
     }  
	 
	//家政结算封装
    public function appoint_settlement($order_id){
		$order_id = (int)$order_id;
		if(empty($order_id)){
			 return false;
		}
		$detail = $this->find($order_id);
		if(!empty($detail)){
			$Appoint = D('Appoint')->find($detail['appoint_id']);
			$shop = D('Shop')->find($Appoint['shop_id']);
            if(false !== $this->save(array('order_id'=>$order_id,'status'=>8))){
			   $info = '家政结算，订单号：'.$order_id;
               D('Users')->Money($shop['user_id'], $detail['need_pay'], $info);//写入到酒店
			   D('Shopmoney')->add(array(
					'shop_id' => $shop['shop_id'], 
					'city_id' => $shop['city_id'], 
					'area_id' => $shop['area_id'], 
					'money' => $detail['need_pay'], 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(),
					'type' => 'appoint', 
					'order_id' => $order_id, 
					'intro' => $info
			  ));
			  return true;
           }else{
			 return false;  
		  }
		}else{
			return false;
		}
    }  
	//家政打印
	public function appoint_order_print($order_id) {	
  		    $Appointorder = D('Appointorder') -> where('order_id =' . $order_id) -> find();
			$Shop = D('Shop')-> find($Appointorder['shop_id']);
			if($Shop['is_appoint_print'] ==1){
			  $msg = $this->appoint_print($Appointorder['order_id']);
			  $result = D('Print')->printOrder($msg, $Shop['shop_id']);
			 /* $result = json_decode($result);
			  $backstate = $result -> state;
			  
			  if ($backstate == 1) {
				D('Appointorder') -> save(array('status' => 2,'is_print'=>1), array("where" => array('order_id' => $Appointorder['order_id'])));
			  }	*/
		    }
		  return true;
    }
	//家政订单打印
	public function appoint_print($order_id) {	
			$Appointorder = D('Appointorder')->find($order_id);
			$Shop = D('Shop')->where(array('shop_id'=> $Appointorder['shop_id']))->find();//商家信息
			
            $msg .= '@@家政订单__________NO:' . $Appointorder['order_id'] . '\r';
            $msg .= '预约姓名：' . $Appointorder['name'] . '\r';
            $msg .= '预约电话：' . $Appointorder['tel'] . '\r';
            $msg .= '预约地址：' . $Appointorder['addr'] . '\r';
            $msg .= '预约时间：' . $$Appointorder['svctime'] . '\r';
			
			if(!empty($Appointorder['worker_id'])){
				$msg .= '----------------------\r';
				$msg .= '@@预约技师信息\r';
				$appointworker = D('Appointworker')->where(array('worker_id' => $Appointorder['worker_id']))->find();
				$msg .= '技师姓名：'.$appointworker['name'].'技师职务：'.$appointworker['office'].'技师手机：'.$appointworker['mobile'].'\r';
			}
			
            $msg .= '----------------------\r';
			$msg .= '商家名称：' . $Shop['shop_name'] . '\r';
            $msg .= '已付定金：' . $Appointorder['need_pay'] / 100 . '元\r';
			return $msg;//返回数组
   }
}