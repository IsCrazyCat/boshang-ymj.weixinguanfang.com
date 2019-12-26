<?php
class ShopgradeorderModel extends CommonModel {
    protected $pk = 'order_id';
    protected $tableName = 'shop_grade_order';
	
	public function getError() {
        return $this->error;
    }
	//统计当前等级下面多少商家
	public function shop_pay_grade($grade_id,$shop_id){
		$obj = D('Shopgrade');
        $Shop = D('Shop')->find($shop_id);
		$users = D('Users')->find($Shop['user_id']);
		$shop_grade = $obj->find($grade_id);//准备购买的商家等级
		$old_shop_grade = $obj->find($Shop['grade_id']);//当前商家的等级
		if(empty($shop_grade)){
			$this->error = '您购买的等级不存在或者被删除了';
			return false;
		}elseif($Shop['grade_id'] == $grade_id){
			$this->error = '购买的等级跟您的商家等级一致，无法购买';
			return false;
	    }elseif($old_shop_grade['orderby'] >= $shop_grade['orderby']){
			$this->error = '您不能降级，只能购买高权限的等级';
			return false;
		}elseif($users['money'] < $shop_grade['money']){
			$this->error = '您的会员余额不足，无法购买，请先到会员中心充值后购买';
			return false;
		}
		
		$order_id = $this->add(array(
			'shop_id' => $shop_id,
			'user_id' => $users['user_id'], 
			'grade_id' => $grade_id, 
			'money' => $shop_grade['money'], 
			'status' => 1,//
			'create_time' => NOW_TIME, 
			'create_ip' => get_client_ip(), 
		));
		
	   if($order_id){
			if (false !== D('Users')->addMoney($users['user_id'], -$shop_grade['money'], '提升商家等级【' . $shop_grade['grade_name'] . '】扣费成功')) {
				D('Shop')->save(array('shop_id' => $shop_id, 'grade_id' => $grade_id));
				return TRUE; 
			} else {
				$this->error = '扣费失败请重试';
				return false;
			}
		}else{
			$this->error = '订单处理非法错误，请稍后再试试';
			return false;
		}
    }

}
