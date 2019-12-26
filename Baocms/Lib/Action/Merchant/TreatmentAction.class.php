<?php
class TreatmentAction extends CommonAction {
	
	//自动提醒
	public function order($shop_id = 0){
			$this->cronyes($order_id=0);//处理应该自动收货的订单
			session_start();
			if (empty($_SESSION['last_check'])){
				$_SESSION['last_check'] = time();
			}
			$mapd['create_time'] = array(array('egt', $_SESSION['last_check']));      
			$mapd['status'] = 1; //已付款  未处理的
			$mapd['shop_id'] = $shop_id;
			$OrderList = D('Eleorder')->where($mapd)->select();
			if($OrderList){
				$_SESSION['last_check'] = time();
				echo 1;
			}else{
				echo 0;
			}
		}
		
		
   //自动确认订单
    public function cronyes($order_id){
	    $this->_CONFIG = D('Setting')->fetchAll();
		$time = time();  //当前时间
		$out_time = $this->_CONFIG['site']['ele']*3600;  //自动确认收货时间 外卖
		$shopout_time = $this->_CONFIG['site']['goods']*24*3600;  //自动确认收货时间 商城
		$jtime = $time - $out_time; 
		$shoptime = $time - $shopout_time;  //商城订单时间戳
        $map['create_time'] = array(array('ELT', $jtime));
		$map['status'] = 2;
		$OrderList = D('Eleorder')->where($map)->select();
	    $map1['create_time'] = array(array('ELT', $shoptime));
		$map1['status'] = 2;
		$shopOrderList = D('Order')->where($map1)->select();
        //商城订单
	    if (is_array($shopOrderList)) {
          $obj = D('Order');
          foreach($shopOrderList as $soid){
		    $date = true;
            if (!$detial = $obj->find($soid['order_id'])) {
                $date = false;
            }
            if ($detial['status'] != 2) {
               $date = false;
            }
			$shop = D('Shop')->find($detial['shop_id']);
			if ($shop['is_pei'] != 1) {
			   $DeliveryOrder = D('DeliveryOrder') -> where(array('type_order_id' =>$soid['order_id'],'type' =>0)) -> find();
				if ($DeliveryOrder['status'] != 8) {
					$date = false;
				}
            }
			if($date){
				 $obj->save(array('order_id'=>$soid['order_id'],'status'=>3));
				 D('Order')->overOrder($soid['order_id']); //确认到账入口
			}
        }
    }

         //外卖订单
         if (is_array($OrderList)) {
   			foreach($OrderList as $oid){
				$dateele = true;
				if (!$detial = D('Eleorder')->find($oid['order_id'])) {
					$dateele = false;
				}
				$shop = D('Shop')->find($detial['shop_id']);
				if ($shop['is_pei'] == 0) {
				   $DeliveryOrder = D('DeliveryOrder') -> where(array('type_order_id' =>$oid['order_id'],'type' =>1)) -> find();
					if ($DeliveryOrder['status'] == 2) {
						$dateele = false;
					}
				}else{//不走配送
					if ($detial['status'] != 2) {
						$dateele = false;
					}	
				}	
				if($dateele){
					$obj = D('Eleorder');
					$obj->overOrder($oid['order_id']);
					$obj->save(array('order_id' => $oid['order_id'], 'status' => 8));
				}
            }
        } else {
            return false;
        }
    }
   
}