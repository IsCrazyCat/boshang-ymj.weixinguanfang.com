<?php
class TuanorderModel extends CommonModel{
    protected $pk = 'order_id';
    protected $tableName = 'tuan_order';
	//检测套餐订单过期时间
	public function chenk_guoqi_time(){
		$CONFIG = D('Setting')->fetchAll();
		$guoqi_time = $CONFIG['tuan']['tuan_time']*60;
		$time = time();
		$jiancha_time = $CONFIG['tuan']['tuan_time']/10*60;
		if(file_exists(BASE_PATH.'/tuantime.txt')){
			$up_time = filemtime(BASE_PATH.'/tuantime.txt');
			if($time-$up_time>$jiancha_time){
				 $a =  fopen(BASE_PATH.'/tuantime.txt', 'w');
				 $this->update_guoqi_time($guoqi_time);
			}
		}else{
			$a =  fopen(BASE_PATH.'/tuantime.txt', 'w');
			$this->update_guoqi_time($guoqi_time);
		}
	}	
		
	//更新过期时间
	public function update_guoqi_time($guoqi_time){
		$time = time();
		$max_time = $time - $guoqi_time;
		$itmes = D('Tuanorder')->where(array('create_time'=>array('lt',$max_time),'status'=>'0'))->select();
		$array = $orders = array();
		foreach($itmes as $k => $v){
			$array[$v['tuan_id']] += $v['num'];
			$orders[] = $v['order_id'];
		}
		$order_list = implode(',',$orders);
		if(D('Tuanorder')->where(array('order_id'=>array('in',$order_list)))->save(array('status'=>'-1','update_time'=>$time))){
			foreach($array as $k => $v){
				D('Tuan')->where(array('tuan_id'=>$k))->setInc('num',$v);
				D('Tuan')->where(array('tuan_id'=>$k))->setDec('sold_num',$v);
			}
		}
	}
	//获取套餐实际价格
	public function get_tuan_need_pay($order_id,$user_id,$type){
        $order_id = (int)$order_id;
        $order = D('Tuanorder')->find($order_id);
		$users = D('Users')->find($user_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $user_id) {
            return false;
        }else{
			$tuan = D('Tuan')->find($order['tuan_id']);
			if (empty($tuan) || $tuan['closed'] == 1 || $tuan['end_date'] < TODAY) {
               return false;
            }
			$canuse = $tuan['use_integral'] * $order['num'];
            $used = 0;
            if ($users['integral'] < $canuse) {
                $used = $users['integral'];
                $users['integral'] = 0;
            } else {
                $used = $canuse;
                $users['integral'] -= $canuse;
            }
            D('Users')->save(array('user_id' => $user_id, 'integral' => $users['integral']));

			//如果后台没有开启积分比例按照原来的积分设置，如果以开启乘以比例数
			$config = D('Setting')->fetchAll();
			if(!empty($config['integral']['buy'])){
				$integral_price = $used * $config['integral']['buy'];
			}else{
				$integral_price = $used;
			}
			//这里加上判断，就是不管你怎么样，积分兑换的金额大于套餐结算价就返回失败
			if($integral_price == 0 && $integral_price > ($order['total_price'] - $order['mobile_fan'])){
				if($type ==1){
					$order['need_pay'] = $order['total_price']; //PC不减去手机下单立减
				}else{
					$order['need_pay'] = $order['total_price'] - $order['mobile_fan'];
				}
				$order['use_integral'] = 0;
			}else{//扣除成功
			    if (empty($order['use_integral'])){
					$intro = '套餐【'.$tuan["title"].'】订单' . $order_id . '积分抵用';
					D('Users')->addIntegral($user_id,-$canuse,$intro);
				}
				if($type ==1){
					$order['need_pay'] = $order['total_price']  - $integral_price; //PC不减去手机下单立减
				}else{
					$order['need_pay'] = $order['total_price'] - $order['mobile_fan'] - $integral_price;
				}
				$order['use_integral'] = $used;
			}
            if(!empty($order['download_id'])){
                $coupon_price = D('Coupon')->Obtain_Coupon_Price_tuan($order_id,$order['download_id']);
                if($type ==1){
                    $order['need_pay'] = $order['total_price'] - $coupon_price; //PC不减去手机下单立减
                }else{
                    $order['need_pay'] = $order['total_price'] - $order['mobile_fan'] - $coupon_price;
                }
            }
			D('Tuanorder')->save(array('order_id' => $order_id, 'use_integral'=>$order['use_integral'],'need_pay' => $order['need_pay']));
			return $order['need_pay'];
		}
        return false;
    }
	
	
    public function source(){
        $y = date('Y', NOW_TIME);
        $data = $this->query(" SELECT count(1) as num,is_mobile,FROM_UNIXTIME(create_time,'%c') as m from  " . $this->getTableName() . "  where status=1 AND FROM_UNIXTIME(create_time,'%Y') ='{$y}'  group by  is_mobile,FROM_UNIXTIME(create_time,'%c')");
        $showdata = array();
        $mobile = array();
        $pc = array();
        for ($i = 1; $i <= 12; $i++) {
            $mobile[$i] = 0;
            $pc[$i] = 0;
            foreach ($data as $val) {
                if ($val['m'] == $i) {
                    if ($val['is_mobile']) {
                        $mobile[$i] = $val['num'];
                    } else {
                        $pc[$i] = $val['num'];
                    }
                }
            }
        }
        ksort($mobile);
        ksort($pc);
        $showdata['mobile'] = join(',', $mobile);
        $showdata['pc'] = join(',', $pc);
        return $showdata;
    }
    public function money_yue(){
        $y = date('Y', NOW_TIME);
        $data = $this->query(" SELECT sum(total_price)/100 as price,FROM_UNIXTIME(create_time,'%c') as m from  " . $this->getTableName() . "  where status=1 AND FROM_UNIXTIME(create_time,'%Y') ='{$y}'  group by  FROM_UNIXTIME(create_time,'%c')");
        $showdata = array();
        for ($i = 1; $i <= 12; $i++) {
            $showdata[$i] = 0;
            foreach ($data as $val) {
                if ($val['m'] == $i) {
                    $showdata[$i] = $val['price'];
                }
            }
        }
        ksort($showdata);
        return join(',', $showdata);
    }
    public function money($bg_time, $end_time, $shop_id)
    {
        $bg_time = (int) $bg_time;
        $end_time = (int) $end_time;
        $shop_id = (int) $shop_id;
        if (!empty($shop_id)) {
            $data = $this->query(" SELECT sum(total_price)/100 as price,FROM_UNIXTIME(create_time,'%m%d') as d from  " . $this->getTableName() . "   where status=1 AND create_time >= '{$bg_time}' AND create_time <= '{$end_time}' AND shop_id = '{$shop_id}'  group by  FROM_UNIXTIME(create_time,'%m%d')");
        } else {
            $data = $this->query(" SELECT sum(total_price)/100 as price,FROM_UNIXTIME(create_time,'%m%d') as d from  " . $this->getTableName() . "   where status=1 AND create_time >= '{$bg_time}' AND create_time <= '{$end_time}'  group by  FROM_UNIXTIME(create_time,'%m%d')");
        }
        $showdata = array();
        $days = array();
        for ($i = $bg_time; $i <= $end_time; $i += 86400) {
            $days[date('md', $i)] = '\'' . date('m月d日', $i) . '\'';
        }
        $price = array();
        foreach ($days as $k => $v) {
            $price[$k] = 0;
            foreach ($data as $val) {
                if ($val['d'] == $k) {
                    $price[$k] = $val['price'];
                }
            }
        }
        $showdata['d'] = join(',', $days);
        $showdata['price'] = join(',', $price);
        return $showdata;
    }
    public function weeks(){
        $y = NOW_TIME - 86400 * 6;
        $data = $this->query(" \r\n\r\n            SELECT count(1) as num,is_mobile,FROM_UNIXTIME(create_time,'%d') as d from  __TABLE__ \r\n\r\n            where status=1 AND create_time >= '{$y}'  group by  \r\n\r\n                is_mobile,FROM_UNIXTIME(create_time,'%d')");
        $showdata = array();
        $mobile = array();
        $pc = array();
        $days = array();
        for ($i = 0; $i <= 6; $i++) {
            $d = date('d', $y + $i * 86400);
            $mobile[$i] = 0;
            $pc[$i] = 0;
            $days[] = '\'' . $d . '号\'';
            foreach ($data as $val) {
                if ($val['d'] == $d) {
                    if ($val['is_mobile']) {
                        $mobile[$i] = $val['num'];
                    } else {
                        $pc[$i] = $val['num'];
                    }
                }
            }
        }
        ksort($mobile);
        ksort($pc);
        $showdata['mobile'] = join(',', $mobile);
        $showdata['pc'] = join(',', $pc);
        $showdata['days'] = join(',', $days);
        return $showdata;
    }
}