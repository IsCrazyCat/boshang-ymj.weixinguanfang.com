<?php
class CloudgoodsModel extends CommonModel{
    protected $pk = 'goods_id';
    protected $tableName = 'cloud_goods';
    public function getType(){
        return array(
			'1' => array('type_name' => '1元区', 'num' => 1),
			'2' => array('type_name' => '5元区', 'num' => 5), 
			'3' => array('type_name' => '10元区', 'num' => 10)
		);
    }
	//返回订单ID
    public function cloud($goods_id, $user_id, $num){
        $obj = D('Cloudlogs');
        $detail = $this->find($goods_id);
        $lefts = $detail['price'] - $detail['join'];
        if ($num > $lefts) {
            return false;
        }
        $count = $obj->where(array('goods_id' => $goods_id, 'user_id' => $user_id))->count();
        $left = $detail['max'] - $count;
        if ($num > $left) {
            return false;
        }
        $t = microtime(false);
        $tt = substr($t, 0, 5);
        $microtime = round($tt, 3) * 1000;
        if (!$microtime || $microtime == NULL || empty($microtime)) {
            $microtime = 00;
        }
        if (strlen($microtime) == 0) {
            $microtime = 00;
        } elseif (strlen($microtime) == 1) {
            $microtime = '00' . $microtime;
        } elseif (strlen($microtime) == 2) {
            $microtime = '0' . $microtime;
        }
        $log_id = $obj->add(array(
			'goods_id' => $goods_id, 
			'user_id' => $user_id, 
			'shop' => $detail['shop_id'], 
			'num' => $num, 
			'type' => $detail['type'],//防止商家修改云购类型
			'money' => $num*100, 
			'status' => 0, 
			'create_time' => NOW_TIME, 
			'microtime' => $microtime,
			'create_ip' => get_client_ip(), 
		));
		if($log_id){
			return $log_id;
		}else{
			return false;
		}
       
    }
	//云购扣费直接扣费
    public function pay_cloud($goods_id, $user_id, $num,$log_id){
		$obj = D('Cloudlogs');
        $detail = $this->find($goods_id);
        if (false !== D('Users')->addMoney($user_id, -$num * 100, '云购商品' . $detail['title'] . '购买，扣费成功')) {
			if($log_id){
				if ($obj->save(array('log_id' => $log_id, 'status' => '1'))) {
					$new_num = $detail['join'] + $num;//增加已云购的数量
					$this->where('goods_id=' . $goods_id)->setField('join', $new_num);
					if ($detail['price'] <= $new_num ){
						$this->lottery($detail['goods_id']);
					}
					return TRUE; 
				 }else{
					return false;	 
				}
			}else{
				return false;
			}
        } else {
            return false;
        }
    }
	 //云购在线付款回调
    public function save_cloud_logs_status($log_id){
		$obj = D('Cloudlogs');
		$cloudlogs = $obj->find($log_id);
        $detail = $this->find($cloudlogs['goods_id']);
        if (!empty($log_id)) {
			if ($obj->save(array('log_id' => $log_id, 'status' => '1'))) {
				$new_num = $detail['join'] + $cloudlogs['num'];//增加已云购的数量
				$this->where('goods_id=' . $cloudlogs['goods_id'])->setField('join', $new_num);
				if ($detail['price'] <= $new_num ){
					$this->lottery($detail['goods_id']);
				}
				return TRUE; 
			}
        }else{
			return TRUE; 
           //由于支付回调，直接忽略报错 return false;
        }
    }
	
    public function get_datas($datas){
        $return = array();
        $i = 0;
        foreach ($datas as $val) {
            $data = $val;
            for ($a = 0; $a < $val['num']; $a++) {
                $num = 10000001 + $i;
                $data['number'] = $num;
                $return[$num] = $data;
                $i++;
            }
        }
        krsort($return);
        return $return;
    }
    public function get_last50_time($data) {
        $return = array('total' => 0, 'datas' => array());
        $i = 0;
        foreach ($data as $val) {
            for ($a = 0; $a < $val['num']; $a++) {
                $user_time = intval(date('His', $val['create_time']) . $val['microtime']);
                if ($i < 50) {
                    $return['total'] += $user_time;
                    $return['datas'][] = $val;
                } else {
                    break;
                }
                $i++;
            }
        }
        krsort($return['datas']);
        return $return;
    }
    public function lottery($goods_id){
        $goods_id = (int) $goods_id;
        $detail = $this->find($goods_id);
        $res = D('Cloudlogs')->where(array('goods_id' => $goods_id))->order(array('log_id' => 'asc'))->select();
        $list = $this->get_datas($res);
        $return = $this->get_last50_time($res);
        $zhongjiang = fmod($return['total'], $detail['price']) + 10000001;
		$ip = get_client_ip();
        if (false !== $this->save(array(
			'goods_id' => $goods_id, 
			'win_user_id' => $list[$zhongjiang]['user_id'], 
			'win_number' => $list[$zhongjiang]['number'], 
			'status' => 1, 
			'lottery_time' => NOW_TIME
		))) {
            if (!empty($detail['shop_id'])) {
				D('Sms')->sms_cloud_win_user($goods_id,$list[$zhongjiang]['user_id'],$list[$zhongjiang]['number']);//中奖短信通知
                $shops = D('Shop')->find($detail['shop_id']);
				$intro = '云购商品编号' . $detail['goods_id'].'卖出结算金额'.$detail['settlement_price'].'元';
					D('Shopmoney')->add(array(
						'shop_id' => $shops['shop_id'], 
						'city_id' => $shops['city_id'], 
						'area_id' => $shops['area_id'], 
						'branch_id' => $shops['branch_id'], 
						'money' => $detail['settlement_price'], 
						'type' => cloud, 
						'create_ip' => $ip, 
						'create_time' => NOW_TIME, 
						'order_id' => $detail['goods_id'], 
						'intro' => $intro
					));
                D('Users')->Money($shops['user_id'], $detail['settlement_price'], '云购商品卖出' . $detail['title'] . '成功卖出，收款！');
            }
            return true;
        }
    }
}