<?php
class AppointordersModel extends RelationModel {
      protected $pk   = 'orders_id';
      protected $tableName =  'Appoint_orders';


	 //抢单数据库操作写法案例
	 public function upload_deliveryOrder($delivery_id,$order_id){
		    $DeliveryOrder = D('DeliveryOrder');
			$delivery_order = $DeliveryOrder -> where('order_id ='. $order_id ) -> find();//详情
			if(empty($delivery_order) ||$delivery_order['closed'] ){
				return false;	
			}else{
				if($delivery_order['type'] == 0){//商城
				   $obj = D('Order');
				   $obj -> where('order_id ='.$delivery_order['type_order_id']) -> setField('status',2);//改变商城发货状态
				   D('Ordergoods') -> where('order_id ='.$delivery_order['type_order_id']) -> setField('status',1);//改变
				}elseif($f['type'] == 1){//外卖
				   $obj = D('EleOrder');
				   $obj -> where('order_id ='.$delivery_order['type_order_id']) -> setField('status',2);//更新外卖
				}
			}
			return true;
	  } 
	  
	
 }
