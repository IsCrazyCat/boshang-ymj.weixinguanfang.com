<?php
class RunningmoneyModel extends CommonModel{
    protected $pk   = 'money_id';
    protected $tableName =  'running_money';
	
	//写入外卖配送费
	public function add_delivery_logistics($order_id,$logistics,$type){
		if($type ==1){
			$map = (array('type_order_id'=>$order_id,'type'=>1));
		}else{
			$map = (array('type_order_id'=>$order_id,'type'=>0));
		}
		$DeliveryOrder = D('DeliveryOrder')->where($map)->find();	
		$Shop = D('Shop')->find($DeliveryOrder['shop_id']);
		$info = '外卖订单ID'.$order_id.'结算给配送员运费'.round($DeliveryOrder['logistics_price']/100,2).'元';
		if(!empty($DeliveryOrder) && !empty($Shop)){
			if ($DeliveryOrder['logistics_price'] > 0) {
                    $this->add(array(
						   'running_id' => $order_id, 
						   'delivery_id' => $DeliveryOrder['delivery_id'], 
						   'user_id' => $DeliveryOrder['user_id'], 
						   'money' => $DeliveryOrder['logistics_price'], 
						   'type' => ele, 
						   'create_time' => NOW_TIME, 
						   'create_ip' => get_client_ip(), 
						   'intro' => $info
					));
                    D('Users')->addMoney($DeliveryOrder['delivery_id'], $DeliveryOrder['logistics_price'],$info);  //写入配送员余额
               }
             return true;
		}else{
			return true;
		}
        return true;
    }
	
	
	//写入商城配送费
	public function add_express_price($order_id,$express_price,$type){
		$Ordergoods = D('Ordergoods')->where(array('order_id'=>$order_id))->find();	
		$Shop = D('Shop')->where(array('shop_id'=>$Ordergoods['shop_id']))->find();	
		if(!empty($express_price)){
			$money = $Ordergoods['express_price'];
		}else{
			$money = $Shop['express_price'];
		}

		$info = '外卖订单ID'.$order_id.'结算给配送员运费'.round($Ordergoods['express_price']/100,2).'元';
		if(!empty($Ordergoods) && !empty($Shop)){
			if ($money > 0) {
                    $this->add(array(
						   'running_id' => $order_id, 
						   'delivery_id' => $Ordergoods['delivery_id'], 
						   'user_id' => $Ordergoods['user_id'], 
						   'money' => $money, 
						   'type' => ele, 
						   'create_time' => NOW_TIME, 
						   'create_ip' => get_client_ip(), 
						   'intro' => $info
					));
                    D('Users')->addMoney($Ordergoods['delivery_id'], $Ordergoods['express_price'],$info);  //写入配送员余额
               }
             return true;
		}else{
			return true;
		}
        return true;
    }

}