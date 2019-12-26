<?php


class HotelorderModel extends CommonModel{
    protected $pk   = 'order_id';
    protected $tableName =  'hotel_order';
    
    public function cancel($order_id){
        if(!$order_id = (int)$order_id){
            return false;
        }elseif(!$detail = $this->find($order_id)){
            return false;
        }else{
            if($detail['online_pay'] == 1&&$detail['order_status'] == 1){
                $detail['is_fan'] = 1;
            }
            $room = D('Hotelroom')->find($detail['room_id']);
            if(!$room['is_cancel']){
                return false;
            }
            if(false !== $this->save(array('order_id'=>$order_id,'order_status'=>-1))){
                if($detail['is_fan'] == 1){
                    D('Users')->addMoney($detail['user_id'],(int)$detail['amount']*100,'酒店订单取消,ID:'.$order_id.'，返还余额');
                }
                D('Hotelroom')->updateCount($detail['room_id'],'sku',$detail['num']);
                return true;
            }else{
                return false;
            }
            
        }  
    }
     
    public function plqx($hotel_id){
        if($hotel_id = (int)$hotel_id){
            $ntime = date('Y-m-d',NOW_TIME);
            $map['stime'] = array('LT',$ntime);
            $map['hotel_id'] = $hotel_id;
            $order = $this->where($map)->select();
            foreach ($order as $k=>$val){
                $this->cancel($val['order_id']);
            }
            return true;
        }else{
            return false;
        }
    }
    //酒店结算
    public function complete($order_id){
		$order_id = (int)$order_id;
		if(empty($order_id)){
			 return false;
		}
		$detail = $this->find($order_id);
		if(!empty($detail)){
			
			$Hotel = D('Hotel')->find($detail['hotel_id']);
			$shop = D('Shop')->find($Hotel['shop_id']);
			
            if($detail['online_pay'] == 1&&$detail['order_status'] == 1){
                $detail['is_fan'] = 1;
            }
            $room = D('Hotelroom')->find($detail['room_id']);
            if(!$room['is_cancel']){
                return false;
            }
            if(false !== $this->save(array('order_id'=>$order_id,'order_status'=>2))){
                if($detail['is_fan'] == 1){
					$info = '酒店结算，订单号：'.$order_id;
                    D('Users')->Money($shop['user_id'], $detail['jiesuan_amount']*100, '酒店订单完成,ID:'.$order_id.'，结算金额');//写入到酒店
					D('Shopmoney')->add(array(
						'shop_id' => $shop['shop_id'], 
						'city_id' => $shop['city_id'], 
						'area_id' => $shop['area_id'], 
						'money' => $detail['jiesuan_amount']*100, 
						'create_time' => NOW_TIME, 
						'create_ip' => get_client_ip(),
						'type' => 'hotel', 
						'order_id' => $order_id, 
						'intro' => $info
					 ));
                }
                return true;
            }else{
                return false;
            }
		}else{
			return false;
		}
      }  

}