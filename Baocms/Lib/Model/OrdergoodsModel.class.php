<?php

class OrdergoodsModel extends CommonModel {
    protected $pk = 'id';
    protected $tableName = 'order_goods';
    protected $types = array(
        0 => '已支付',
        1 => '已经捡货',
        2 => '待返还',
        3 => '返还中',
        8 => '已完成',
    );

    public function getType() {
        return $this->types;
    }
   //第一次更新商品运费
   public function calculation_express_price($uid,$kuaidi_id,$num,$goods_id,$pc_order) {
		$obj = D('Paddress');
		$addressCount = $obj -> where(array('user_id' => $uid)) -> count();//统计客户的收货地址
		if ($addressCount == 0) {
			if($pc_order ==1){
				$this->baoJump(U('members/address/index',  array('type' => goods, 'order_id' => $order_id)));
			}else{
				$this->fengmiMsg('您还没地址',U('address/addrcat', array('type' => goods, 'order_id' => $order_id)));
			}
			
		} else {
			$defaultCount = $obj -> where(array('user_id' => $uid, 'default' =>1))->count();//统计默认地址
			
			if ($defaultCount == 0) {
				$defaultAddress = $obj -> where(array('user_id' => $uid)) -> order("id desc") -> find();//没有默认地址
			} else {
			    $defaultAddress = $obj -> where(array('user_id' => $uid, 'default' => 1)) -> find();//找到默认地址
				
			}
		}
	   $detail = D('Goods') -> where(array('goods_id' => $goods_id)) ->find();//找到商品信息
	   
	   if($detail['is_reight'] != 0){
		   $express = D('Pkuaidi') -> where(array('id' => $detail['kuaidi_id'])) -> getField('name');
		   $order_yunfei = D('Pyunfei') -> where(array('kuaidi_id' => $kuaidi_id, 'province_id' => $defaultAddress['province_id'])) -> find();
		   if(!empty($order_yunfei)){
			   if($num == 1 ){
					$weight = ($detail['weight'] -1 );  
			   }else{
					$weight = ($num *($detail['weight']))-1;  
			   }
			   $express_price = $order_yunfei['shouzhong'] + ($weight * $order_yunfei['xuzhong']);
			   
		    }else{
				return $order_express_price = 0;   
		    }
		   return $order_express_price = $express_price;   
       }else{
		return $order_express_price = 0;   
	   }
      return $order_express_price = 0;  
    }
	
	 //商城万能打印接口
    public function combination_goods_print($order_ids) {
        if (is_array($order_ids)) {
            $order_ids = join(',', $order_ids);
            $Order = D('Order')->where("order_id IN ({$order_ids})")->select();
            foreach ($Order as $k => $v) {
                $this->goods_order_print($v['order_id']);
            }
        } 
	
	}
	//三维数组拆分重组转二维
	private function three_to_tow($arr){
		foreach($arr as $k=>$val){
			foreach($val as $k => $v){
				$arr_tow[] = $v;
			}
		}
		return $arr_tow;
	}
	
	
   //商城合并付款新运费，跟单个商品付款不重复
   public function merge_update_express_price($uid,$type,$log_id,$address_id) {
	    $log_id = (int)$log_id;
		$Paymentlogs = D('Paymentlogs')->where(array('log_id'=>$log_id))->find();
		
	    if (!empty($Paymentlogs['order_ids'])) {
           $order_ids = explode(',', $Paymentlogs['order_ids']);
		   $Ordergoods = D('Ordergoods');
		   foreach($order_ids as $v){
			    $ordergoods_list_all[]= $Ordergoods->where(array('order_id'=>$v))->select();
		    }
			
		   $ordergoods_list = $this -> three_to_tow($ordergoods_list_all);
			
		   if (empty($ordergoods_list)) {
				return false;   
		   }else{
			   //更新订单物流ID
			   
			   foreach($order_ids as $v){
				 D('Order')->save(array('address_id' => $address_id), array('where' => array('order_id' => $v))); 	   
			   }
			   
			   //更新订单表ID
			   foreach ($ordergoods_list as $k => $val) {
				 $Ordergoods->save(array('kuaidi_id' => $address_id), array('where' => array('id' => $val['id']))); 
			   }
				
			  //计算运费 
			   foreach ($ordergoods_list as $k => $v) {
				 $v['express_price'] ; //以前的运费
				 $express_price = $this->replace_add_express_price($uid,$address_id,$v['num'],$v['goods_id']);//现在的运费
				 $total_price = $v[total_price] -  $v['express_price']  + $express_price ;
				 $conbine_total_price += $total_price;//所有的 $total_price总和
				 $Ordergoods->save(array('total_price'=>$total_price,'express_price' =>  $express_price), array('where' => array('id' => $v['id']))); 
				 D('Order')->save(array('express_price' =>$express_price,'address_id' => $address_id), array('where' => array('order_id' =>$v['order_id']))); 
				 
			   }
			   //这里是更新日志总运费
			   D('Paymentlogs')->save(array('need_pay'=>$conbine_total_price),array('where'=>array('log_id'=>$log_id)));
			
			
			   return true; 	      			   
			}
        } else{
			 return false;   
		}
		return true; 
    }

	
   //用户更换收货地址更新配送费，并写入日志后再支付
   public function update_express_price($uid,$type,$order_id,$address_id) {
	   $order_id = (int)$order_id;
	   $Ordergoods = D('Ordergoods');
	   $ordergoods_list = $Ordergoods->where('order_id =' . $order_id)->select();
	   if (empty($ordergoods_list) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            return false;   
       }else{
		   //更新订单物流ID
		   D('Order')->save(array('address_id' => $address_id), array('where' => array('order_id' => $order_id))); 
		   foreach ($ordergoods_list as $k => $val) {
			 $Ordergoods->save(array('kuaidi_id' => $address_id), array('where' => array('id' => $val['id']))); 
		   }
		   //更新运费
		   foreach ($ordergoods_list as $k => $v) {
			 $replace_order_express_price = $this->replace_add_express_price($uid,$address_id,$v['num'],$v['goods_id']);
			 $Ordergoods->save(array('express_price' => $replace_order_express_price), array('where' => array('id' => $v['id']))); 
		   }
		   
		   $total_express_price = $Ordergoods->where('order_id =' . $order_id)->sum('express_price');//统计单个商品总运费
		   //更新总表运费
		   D('Order')->save(array('express_price' => $total_express_price,'address_id' => $address_id), array('where' => array('order_id' => $order_id))); 
		}
		return true; 
    }
	
	//更换地址更新商品运费
   public function replace_add_express_price($uid,$kuaidi_id,$num,$goods_id) {
	   $obj = D('Paddress');   
	   
	   $defaultAddress = $obj -> where(array('user_id' => $uid, 'id' => $kuaidi_id)) -> find();
	   $detail = D('Goods') -> where(array('goods_id' => $goods_id)) ->find();//找到商品信息
	 
	   
	   if($detail['is_reight'] != 0){
		   
		   $order_yunfei = D('Pyunfei') -> where(array('kuaidi_id' => $detail['kuaidi_id'], 'province_id' => $defaultAddress['province_id'])) -> find();
		   if(!empty($order_yunfei)){
			   if($num == 1 ){
					$weight = ($detail['weight'] -1 );  
			   }else{
					$weight = ($num *($detail['weight']))-1;  
			   }
			    
			   $express_price = $order_yunfei['shouzhong'] + ($weight * $order_yunfei['xuzhong']);
			   
		    }else{
				return $replace_order_express_price = 0;   
		    }
		   return $replace_order_express_price = $express_price;   
       }else{
		return $replace_order_express_price = 0;   
	   }
      return $replace_order_express_price = 0;  
    }
	
	
	
   protected function baoJump($jumpUrl){
        $str = '<script>';
        $str .= 'parent.jumpUrl("' . $jumpUrl . '");';
        $str .= '</script>';
        die($str);
    }
}