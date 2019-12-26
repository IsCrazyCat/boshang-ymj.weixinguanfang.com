<?php
class DeliveryOrderModel extends RelationModel {
      protected $pk   = 'order_id';
      protected $tableName =  'delivery_order';

	  protected $_link = array(
        'Delivery' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'Delivery',
            'foreign_key' => 'delivery_id',
            'mapping_fields' =>'name,mobile',
            'as_fields'=>'name,mobile', 
        ),
     );
	 //抢单数据库操作
	 public function upload_deliveryOrder($delivery_id,$order_id){
		    $delivery_id = (int)$delivery_id;
			$order_id = (int)$order_id;
			$Delivery = D('Delivery')->where(array('user_id'=>$delivery_id))->find();
		    $DeliveryOrder = D('DeliveryOrder');
			$delivery_order = $DeliveryOrder -> where(array('order_id' =>$order_id)) -> find();//详情
			
			if(empty($delivery_order)){
				return false;
			}elseif(($delivery_order['is_appoint'] ==1) || (!empty($delivery_order['appoint_user_id']))){
				if($Delivery['id'] != $delivery_order['appoint_user_id'] ){//如果指定了配送员，非配送员抢单报错处理
					return false;
				}
			}elseif($delivery_order['closed'] ==1){
				return false;
			}else{
				if($delivery_order['type'] == 0){//商城
				   $obj = D('Order');
				   $obj -> where('order_id ='.$delivery_order['type_order_id']) -> setField('status',2);//改变商城发货状态
				   D('Ordergoods') -> where('order_id ='.$delivery_order['type_order_id']) -> setField('status',1);//改变
				}elseif($delivery_order['type'] == 1){//外卖
				   $obj = D('Eleorder');
				   $obj -> where('order_id ='.$delivery_order['type_order_id']) -> setField('status',2);//更新外卖
				}
			}
			return true;
	  } 
	  
	 //确认完成数据库操作
	 public function ok_deliveryOrder($delivery_id,$order_id){
		    $DeliveryOrder = D('DeliveryOrder');
			$delivery_order = $DeliveryOrder -> where('order_id ='. $order_id ) -> find();//详情
			if(empty($delivery_order) ||$delivery_order['closed'] ==1 ){
				return false;	
			}else{
				if($delivery_order['type'] == 0){//商城
				   $obj = D('Order');
				   $obj -> where('order_id ='.$delivery_order['type_order_id']) -> setField('status',2);//改变商城发货状态
				   D('Ordergoods') -> where('order_id ='.$delivery_order['type_order_id']) -> setField('status',1);//改变
				}elseif($delivery_order['type'] == 1){//外卖
				   $obj = D('EleOrder');
				   $obj -> where('order_id ='.$delivery_order['type_order_id']) -> setField('status',2);//更新外卖
				}
			}
			return true;
	  }  
 }
