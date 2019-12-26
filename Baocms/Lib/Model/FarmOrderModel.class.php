<?php


class FarmOrderModel extends CommonModel{
    
    protected $pk   = 'order_id';
    protected $tableName =  'farm_order';

    public function cancel($order_id){

        if(!$order_id = (int)$order_id){
            return false;
        }elseif(!$detail = $this->find($order_id)){
            return false;
        }else{
            if($detail['order_status'] == 1){
                $detail['is_fan'] = 1;
            }
            if(false !== $this->save(array('order_id'=>$order_id,'order_status'=>-1))){
                if($detail['is_fan'] == 1){
                    D('Users')->addMoney($detail['user_id'],(int)$detail['amount']*100,'农家乐订单取消,ID:'.$order_id.'，返还余额');
                }
                return true;
            }else{
                return false;
            }
            
        }  
    }
    
    
    public function complete($order_id){
        if(!$order_id = (int)$order_id){
            return false;
        }elseif(!$detail = $this->find($order_id)){
            return false;
        }else{
            $shop = D('Shop')->find($detail['shop_id']);
            if($detail['order_status'] == 1){
                $detail['is_fan'] = 1;
            }
            if(false !== $this->save(array('order_id'=>$order_id,'order_status'=>2))){
                if($detail['is_fan'] == 1){
					if ($detail['jiesuan_amount'] > 0) {
						$info = '农家乐订单ID:'.$order_id.'完成，结算金额'.$detail['jiesuan_amount'];
                        D('Shopmoney')->add(array(
							'shop_id' => $shop['shop_id'], 
							'city_id' => $shop['city_id'], 
							'area_id' => $shop['area_id'], 
							'money' => $detail['jiesuan_amount']*100, 
							'create_time' => NOW_TIME, 
							'create_ip' => get_client_ip(),
							'type' => 'farm', 
							'order_id' => $order_id, 
							'intro' => $info
						));
                        D('Users')->Money($shop['user_id'], $detail['jiesuan_amount']*100, $info);  //写入商户余额
                     }
                }
                return true;
            }else{
                return false;
            }
            
        }  
    }
     
}