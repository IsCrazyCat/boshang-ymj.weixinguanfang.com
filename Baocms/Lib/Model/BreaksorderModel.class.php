<?php


class BreaksorderModel extends CommonModel{
    protected $pk   = 'order_id';
    protected $tableName =  'breaks_order';
	
    //更新优惠买单销售接口
    public function settlement($order_id) {
        $order_id = (int) $order_id;
		$logs = D('Paymentlogs')->where(array('type'=>breaks,'order_id'=>$order_id))->find();//支付日志
		$order = D('Breaksorder')->find($order_id );//查询订单信息
		$shopyouhui = D('Shopyouhui')->find($order['shop_id']);//商家优惠信息
		$shop = D('Shop')->find($order['shop_id']);//商家信息
		
		$deduction = $this->get_deduction($shop['shop_id'],$order['amount'],$order['exception']);//网站扣除金额，暂时写到购买的会员余额
		$intro = '优惠买单，支付记录ID：' . $logs['log_id'];
		$ip = get_client_ip();//IP
		
		
		
		if($shopyouhui['type_id'] == 0){//打折
			if(!empty($shopyouhui['deduction'])){
				$money = round(($order['need_pay'] - $deduction)*100,2);//商户实际到账
			}else{
				$money = round($order['need_pay']*100,2);	
			}
		}else{//满减
			if(!empty($shopyouhui['vacuum'])){
				$money = round(($order['need_pay'] - $deduction)*100,2);//商户实际到账
			}else{
				$money = round($order['need_pay']*100,2);	
			}	
		}

		
		//会员买单实际支付日志
		D('Usermoneylogs')->add(array(
          'user_id' => $order['user_id'],
          'money' => $logs['need_pay'],
          'create_time' => NOW_TIME,
          'create_ip' => $ip,
          'intro' => $intro
        ));			
					
					
		//写入商户资金日志
        D('Shopmoney')->add(array(
            'shop_id' => $order['shop_id'],
			'city_id' => $shop['city_id'], 
			'area_id' => $shop['area_id'], 
			'branch_id' => $data['branch_id'], 
            'money' => $money,//写入实际金额
            'type'=>'breaks',
            'order_id' =>$logs['order_id'],
            'create_time' => NOW_TIME,
            'create_ip' => $ip,
            'intro' => $intro
         ));
		 $this->Breaks_profit(2, $order['user_id'], $order['order_id'],$deduction);//带入三级分销
		 D('Users')->Money($shop['user_id'], $money, '用户买单结算金额，订单号:' . $order['order_id']);//写入商户资金
					
        return TRUE;
    }
	
	
    public function get_deduction($shop_id,$amount,$exception){
        $shopyouhui = D('Shopyouhui')->where(array('shop_id'=>$shop_id,'is_open'=>1,'audit'=>1))->find();
        $need = $amount - $exception;//应该计算的金额=消费总额-参与优惠
        if($shopyouhui['type_id'] == 0){
            $result_deduction = round($need *$shopyouhui['deduction']/10,2); //减去金额=总金额-不参与优惠金额*点数
        }else{
            $t = (int)$need/$shopyouhui['vacuum'];//$T是应付款除以网站抽成金额，比如100元，网站抽3元，这里的t就是百分之3
            $result_deduction = round($t*$need/10,2);//实际付款金额*百分比
        }
        return $result_deduction;//返回网站扣除金额
    }
	
	//买单三级分销
	private function Breaks_profit($order_type = 2, $uid = 0, $order_id = 0,$deduction) {
		
		static $CONFIG;
        if (empty($CONFIG)) {
            $CONFIG = D('Setting')->fetchAll();
        }

		$user_fuid = D('Users')->find($uid);//查询会员的fuid1
		$breaksorder = D('Breaksorder')->find($order_id );//查询订单信息
	
		if ($user_fuid) {
			$userModel = D('Users');//找到会员表
			if ($breaksorder['is_separate'] == 0) {
				
				if ($order_type === 2) {
					$modelOrder = D('Breaksorder');
					$orderTypeName = '优惠买单';
				}

				if ($user_fuid['fuid1']) {//如果一级会员不等于空
					$money1 = round($CONFIG['profit']['breaks_profit_rate1'] * $deduction,2);//这里应该就是实际金额
					if ($money1 > 0) {
						$info1 = $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . $money1;
						$fuser1 = $userModel->find($user_fuid['fuid1']);//查询会员是否存在
						if ($fuser1) {
							$userModel->addMoney($user_fuid['fuid1'], round($money1 * 100, 2), $info1);//写入用户金额*100
							$userModel->addProfit($user_fuid['fuid1'], $order_type, $order_id, round($money1 * 100, 2), 1);//写入分销日志
						}
					}
				}
			
				if ($user_fuid['fuid2']) {
					$money2 = round($CONFIG['profit']['breaks_profit_rate2'] * $deduction,2);//这里应该就是实际金额
					if ($money2 > 0) {
						$info2 = $orderTypeName . '订单ID:' . $order_id . ', 分成: ' .$money2;
						$fuser2 = $userModel->find($user_fuid['fuid2']);
						if ($fuser2) {
							$userModel->addMoney($user_fuid['fuid2'], round($money2 * 100, 2), $info2);//写入用户金额
							$userModel->addProfit($user_fuid['fuid2'], $order_type, $order_id, round($money2 * 100, 2), 1);//写入分销日志
						}

					}

				}
				if ($user_fuid['fuid3']) {
					$money3 = round($CONFIG['profit']['breaks_profit_rate3'] * $deduction,2);//这里应该就是实际金额
					if ($money3 > 0) {
						$info3 = $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . $money3;
						$fuser3 = $userModel->find($user_fuid['fuid3']);
						if ($fuser3) {
							$userModel->addMoney($user_fuid['fuid3'], round($money3 * 100, 2), $info3);
							$userModel->addProfit($user_fuid['fuid3'], $order_type, $order_id, round($money3 * 100, 2), 1);
						}
					}
				}
				$modelOrder->save(array('order_id' => $order_id,'deduction'=>round($deduction * 100, 2), 'is_separate' => 0,'is_profit'=>1));//更新状态
			}
		}
	}
   //三级分销结束			
					
}