<?php

class PaddressModel extends CommonModel {

    protected $pk = 'id';
    protected $tableName = 'paddress';
	
	//单个付款获取用户地址
	public function order_address_id($uid,$order_id){
			$obj = D('Paddress');
			if (empty($order_id)) {
				return false;
			} else {
				$Order = D('Order') -> where(array('order_id' =>$order_id))-> find();
				$defaultAddress = $obj -> where(array('id' =>$Order['address_id'],'closed' => 0))-> find();
				if (!empty($defaultAddress)) {
					return $defaultAddress; 
				} else {
					return false;
				}
			}
		return false;
	}
	
	//PC合并付款获取第一个地址
	public function pc_paycode_address($log_id,$uid){
		$log_id = (int)$log_id;
		$Paymentlogs = D('Paymentlogs')->where(array('log_id'=>$log_id))->find();
		
	    if (!empty($Paymentlogs['order_ids'])) {
		   $order_ids = explode(',', $Paymentlogs['order_ids']);
		   foreach($order_ids as $v){
			  $order = D('Order')->where(array('order_id'=>$v))->find();
		    }
           if (!empty($order['address_id'])) {
              $addrs = D('Paddress')->where(array('user_id' => $uid))->order(array('default' => 'desc', 'id' => 'desc'))->limit(0, 6)->select();
              return $addrs; 
           }
        } else{
			
			return false;   
		}
		return $addrs; 
	}
	
	//PC合并付款修改默认地址
	public function paycode_replace_default_address($uid,$id){
		$paddress = D('Paddress')-> where(array('user_id'=>$uid,'default' => 1))->select();
		if(!empty($paddress)){
			foreach($paddress as $k => $val){
				D('Paddress') -> where(array('id'=>$val['id'])) -> setField('default', 0);
			}
			D('Paddress') -> where(array('id'=>$id)) -> setField('default',1);	
			return ture; 
		}else{
			return false;   	
		}
	   return ture; 
	}
	
	
	//PC单独付款获取收货地址
	public function pc_pay_address($address_id,$uid){
        if (!empty($address_id)) {
            $thisaddr[] = D('Paddress')->find($address_id);
		    $addrs = D('Paddress')->where(array('user_id' => $uid, 'id' => array('NEQ', $address_id)))->order('id DESC')->limit(0, 6)->select();
            if (empty($addrs)) {             
				$addrss = array_merge($thisaddr,$addrs);
				return $addrss; 
            } else {
                $addrss = array_merge($thisaddr,$addrs);
				return $addrss; 
            }
        } else {
            $addrs = D('Paddress')->where(array('user_id' => $uid))->order(array('default' => 'desc', 'id' => 'desc'))->limit(0, 6)->select();
			return $addrs; 
        }
	}
	
	public function defaultAddress($uid,$type){
			$obj = D('Paddress');
			$addressCount = $obj -> where(array('user_id' => $uid,'closed' => 0)) -> count();
			if ($addressCount == 0) {
				$this->fengmiMsg(U('wap/address/addrcat', array('type' => goods, 'order_id' => $order_id)));//应该有问题
			} else {
				$defaultCount = $obj -> where(array('user_id' => $uid, 'default' => 1,'closed' => 0)) -> count();
				if ($defaultCount == 0) {
					$defaultAddress = $obj -> where(array('user_id' => $uid,'closed' => 0)) -> order("id desc") -> find();
					return $defaultAddress; 
				} else {
					$defaultAddress = $obj -> where(array('user_id' => $uid, 'default' => 1,'closed' => 0)) -> find();
					return $defaultAddress; 
				}
			}
		 return $defaultAddress; 
	}
	public function check_cat_address($uid,$type){
		$obj = D('Paddress');
		if(!empty($uid)){
			$user_address_default = $obj -> where(array('user_id' =>$uid,'default'=>1,'closed' => 0))-> find();
			if(!empty($user_address_default)){
				$address_id = $user_address_default['id'];
				return $address_id;
			}else{
				$user_address = $obj -> where(array('user_id' =>$uid,'closed' => 0))->order(array('id desc')) -> find();
				$address_id = $user_address['id'];
				return $address_id;
			}
		}else{
			return false;
		}
	}	
}