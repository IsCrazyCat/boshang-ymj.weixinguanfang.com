<?php


class CrowdModel extends CommonModel{
    protected $pk   = 'goods_id';
    protected $tableName =  'crowd';
    
    
     public function _format($data){
        $data['all_price'] = round($data['all_price']/100,2); 
		$data['commission'] = round($data['commission']/100,2); 
        $data['have_price'] = round($data['have_price']/100,2); 
        return $data;
    }

	public function merge($arr1,$arr2){
		$detail = array();
		foreach($arr1 as $k => $v){
			foreach($arr2 as $kk => $vv){
				if($v['goods_id'] == $vv['goods_id']){
					$detail[$v['goods_id']] = array_merge($v,$vv);
				}
			}
		}
		return $detail;
	}
	
	public function crowd_time($ltime) {
		$current_time = time();
        $crowd_strtotime = strtotime($ltime) - $current_time;
		$crowd_time = floor($crowd_strtotime/(3600*24));
		if($crowd_time > 0){
			return $crowd_time;	
		}else{
			return '已过期';
		}
		return TRUE;
		
	}
	
    //这里是去更已众筹的各种状态逻辑
	public function add_crowd_list($order_id, $user_id){
		
        $order_id = (int) $order_id;
		$user_id = (int) $user_id;
		$order = D('Crowdorder')->find($order_id);//订单信息
		
		if(empty($order)){
			return false;//没有找到订单信息	
		}
		$Crowd = D('Crowd');
		$Crowdtype = D('Crowdtype');
		$Crowdlist = D('Crowdlist');
		$detail = $Crowd->find($order['goods_id']);//商品信息		
		
		if($detail['ltime'] <= TODAY){
			 return false;//时间错误，这里要修正
		}
		
		$type_id = $order['type_id'];
		if(empty($type_id)){
			return false;//没找到类型错误
		}
		
		
		$type = $Crowdtype->where(array('type_id'=>$type_id))->find();
        if ($type['have_num'] >= $type['max_num']) {
            return false;//已满
        }
		
		if($type['choujiang'] == 1){
			$choujiang = 0;
		}else{
			$choujiang = 1;
		}
		$addrs = D('Paddress')->find($order['address_id']);
		if(empty($addrs)){
			return false;//没有找到收货地址	
		}
		
		$find_crowd_list = $Crowdlist->where(array('order_id'=>$order_id))->find();	
        if (empty($find_crowd_list)) {
            $Crowdlist->add(array(
				'goods_id' => $detail['goods_id'], 
				'order_id' => $order_id, 
				'uid' => $user_id, 
				'type_id' => $type_id, 
				'name' => $addrs['xm'], 
				'mobile' => $addrs['tel'], 
				'addr' => $addrs['area_str']."".$addrs['info'], 
				'price' => $order['price'],
				'is_zhong'=>$choujiang, 
				'is_lock'=>1,//锁定更新状态 
				'dateline' => NOW_TIME
			));
			
			$Crowd->updateCount($order['goods_id'], 'have_price',$order['price']);
			$Crowd->updateCount($order['goods_id'], 'have_num');
			$Crowdtype->updateCount($type_id, 'have_num');
            return TRUE;
        } else {
            return false;
        }
    }
	
	protected function baoError($message, $time = 3000, $yzm = false, $parent = true){
        $parent = $parent ? 'parent.' : '';
        $str = '<script>';
        if ($yzm) {
            $str .= $parent . 'bmsg("' . $message . '","",' . $time . ',"yzmCode()");';
        } else {
            $str .= $parent . 'bmsg("' . $message . '","",' . $time . ');';
        }
        $str .= '</script>';
        die($str);
    }
}