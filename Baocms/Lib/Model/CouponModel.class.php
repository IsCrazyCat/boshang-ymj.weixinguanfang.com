<?php
class CouponModel extends CommonModel{
    protected $pk   = 'coupon_id';
    protected $tableName =  'coupon';
	//获取用户的优惠劵返回优惠劵的详情
	public function Obtain_Coupon($order_id,$uid){
		if(!empty($order_id)){
			$order = D('Order')->where(array('order_id'=>$order_id,'closed'=>0,'status'=>0))->find();//取得订单的ID
			$coupon_download_list = D('Coupondownload')->where(array('user_id'=>$uid,'is_used'=>0))->order(array('create_time' => 'asc'))->select();
			
			foreach ($coupon_download_list as $k => $val ){
			  if (!empty($val['coupon_id'])) {
					$coupon= D('Coupon')->where(array('coupon_id'=>$val['coupon_id'],'closed'=>0, 'expire_date' => array('EGT', TODAY)))->find();
					
					if ($coupon['expire_date'] <= TODAY) {
						unset($coupon_download_list[$k]);
					}
					//判断当前订单的商家ID是否跟优惠劵的商家ID一致
					if ($order['shop_id'] != $val['shop_id']) {
						unset($coupon_download_list[$k]);
					}
				}
			 }
			 //取出第一条最后时间的优惠劵
			if(!empty($coupon_download_list[0])){
				$coupon= D('Coupon')->where(array('coupon_id'=>$coupon_download_list[0]['coupon_id'],'closed'=>0, 'expire_date' => array('EGT', TODAY)))->find();
				if(!empty($coupon)){
					if($order['total_price'] >= $coupon['full_price']){
						$download_id = $coupon_download_list[0]['download_id'];
						$coupon['download_id'] = $download_id;
						if($coupon['reduce_price'] > 0){
							return $coupon;	
						}else{
							return false;
						}
						
					}else{
						return false;
					}
					
				}else{
					return false;
				}
			}else{
				return false;	
			}
		}else{
			return false;		
		}
		return false;	
	}
    //获取套餐用户的优惠劵返回优惠劵的详情
    public function Obtain_Coupon_tuan($tuan_id,$uid){
        if(!empty($tuan_id)){
            $tuan = D('Tuan')->where(array('tuan_id'=>$tuan_id,'closed'=>0,'status'=>0))->find();//取得订单的ID
            $coupon_download_list = D('Coupondownload')->where(array('user_id'=>$uid,'is_used'=>0))->order(array('create_time' => 'asc'))->select();

            foreach ($coupon_download_list as $k => $val ){
                if (!empty($val['coupon_id'])) {
                    $coupon= D('Coupon')->where(array('coupon_id'=>$val['coupon_id'],'closed'=>0, 'expire_date' => array('EGT', TODAY)))->find();

                    if ($coupon['expire_date'] <= TODAY) {
                        unset($coupon_download_list[$k]);
                    }
                    //判断当前订单的商家ID是否跟优惠劵的商家ID一致
                    //不限制单店使用
//                    if ($tuanorder['shop_id'] != $val['shop_id']) {
//                        unset($coupon_download_list[$k]);
//                    }
                }
            }
            //取出第一条最后时间的优惠劵
            if(!empty($coupon_download_list[0])){
                $coupon= D('Coupon')->where(array('coupon_id'=>$coupon_download_list[0]['coupon_id'],'closed'=>0, 'expire_date' => array('EGT', TODAY)))->find();
                if(!empty($coupon)){
                    if($tuan['tuan_price'] >= $coupon['full_price']){
                        $download_id = $coupon_download_list[0]['download_id'];
                        $coupon['download_id'] = $download_id;
                        if($coupon['reduce_price'] > 0){
                            return $coupon;
                        }else{
                            return false;
                        }

                    }else{
                        return false;
                    }

                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
        return false;
    }
	
	//获取满减的价格
	public function Obtain_Coupon_Price($order_id,$download_id){
		if(!empty($order_id)){
			$order = D('Order')->where(array('order_id'=>$order_id,'closed'=>0))->find();//订单总额大于优惠劵的满减条件
			if(!empty($order)){
				$Coupondownload= D('Coupondownload')->where(array('download_id'=>$download_id,'closed'=>0))->find();
				$coupon= D('Coupon')->where(array('coupon_id'=>$Coupondownload['coupon_id'],'closed'=>0, 'expire_date' => array('EGT', TODAY)))->find();
				if(!empty($coupon)){
					$coupon_price = $order['total_price'] > $coupon['full_price'];
					if($order['total_price'] > $coupon['full_price'] && $coupon_price >0){//满足2条件才返回
						return $coupon['reduce_price'];	
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
	}
    //获取套餐满减的价格
    public function Obtain_Coupon_Price_tuan($tuan_order_id,$download_id){
        if(!empty($tuan_order_id)){
            $Tuanorder = D('TuanOrder')->where(array('order_id'=>$tuan_order_id,'closed'=>0))->find();//订单总额大于优惠劵的满减条件
            if(!empty($Tuanorder)){
                $Coupondownload= D('Coupondownload')->where(array('download_id'=>$download_id,'closed'=>0))->find();
                $coupon= D('Coupon')->where(array('coupon_id'=>$Coupondownload['coupon_id'],'closed'=>0, 'expire_date' => array('EGT', TODAY)))->find();
                if(!empty($coupon)){
                    $coupon_price = $Tuanorder['total_price'] > $coupon['full_price'];
                    if($Tuanorder['total_price'] > $coupon['full_price'] && $coupon_price >0){//满足2条件才返回
                        return $coupon['reduce_price'];
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
    }
	//付款成功后修改is_used状态
	public function change_download_id_is_used($order_id){
		$order = D('Order')->where(array('order_id'=>$order_id))->find();//订单总额大于优惠劵的满减条件
	    $ip = get_client_ip();
		D('Coupondownload')->save(array('download_id' =>$order['download_id'],'is_used' => 1,'used_time' => NOW_TIME,'used_ip' => $ip));
	}
    //付款成功后修改is_used状态
    public function change_download_id_is_used_tuan($tuan_order_id){
        $TuanOrder = D('TuanOrder')->where(array('order_id'=>$tuan_order_id))->find();//订单总额大于优惠劵的满减条件
        $ip = get_client_ip();
        D('Coupondownload')->save(array('download_id' =>$TuanOrder['download_id'],'is_used' => 1,'used_time' => NOW_TIME,'used_ip' => $ip));
    }

}
