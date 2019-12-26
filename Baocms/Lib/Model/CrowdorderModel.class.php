<?php

class CrowdorderModel extends CommonModel {
    protected $pk = 'order_id';
    protected $tableName = 'crowd_order';
    protected $cfg = array(
        0 => '等待付款',
        1 => '已付款',		
        8 => '已完成',
    );


    public function getCfg() {
        return $this->cfg;
    }
	//生成众筹劵
	public function getCode(){       
        $i=0;
        while(true){
            $i++;
            $code = rand_string(8,1);
            $data = $this->find(array('where'=>array('code'=>$code)));
            if(empty($data)) return $code;
            if($i > 20) return $code;//CODE 做了唯一索引，如果大于20 我们也跳出循环以免更多资源消耗
        }
    }
	//检测用户地址
    public function check_user_address_id($goods_id,$user_id,$type) {
		$goods_id= (int) $goods_id;
		$user_id = (int) $user_id;
		if(!empty($user_id)){
			$user_address_default = D('Paddress')->where(array('user_id' => $user_id))->order(array('default' =>1, 'id' => 'desc'))->find();
			if(!empty($user_address_default)){
				return $user_address_default['id'];//返回用户默认地址ID
			}else{
				$user_address = D('Paddress')->where(array('user_id' => $user_id))->order(array('id' => 'desc'))->find();	
				if(!empty($user_address)){
					return $user_address['id'];//返回用户第一条地址
				}else{
					if($type == 1){
						$this->fengmiMsg('您暂无收货地址！,正在为您跳', U('wap/address/addrcat', array('goods_id' => $goods_id,'type'=>crowd))); 
					}else{
						$this->baoJump(U('members/malladdress/create',  array('type' => crowd, 'goods_id' => $goods_id)));
					}
					//return false;//找不到地址
				}
			}
		}else{
			return false;//找不到地址
		}
        return true;//默认
    }
	
   //下单时候获取默认地址
    public function addrs_address($order_id,$user_id) {
		 $order_id = (int) $order_id;
		 $user_id = (int) $user_id;
		 $order = D('Crowdorder')->find($order_id);
		 if (!empty($order['address_id'])) {
			 //如果订单里面的地址查询为空就下面找到默认地址
            $addrs = D('Paddress')->where(array('user_id' => $user_id, 'id' => array('NEQ', $order['address_id'])))->order('id DESC')->limit(0, 6)->select();
            if (!empty($addrs)) {
				return $addrs;
            } else {//先找默认地址排序
				$addrs_default = D('Paddress')->where(array('user_id' => $user_id))->order(array('default' => 'desc', 'id' => 'desc'))->limit(0, 6)->select();
				if(!empty($addrs_default)){
					return $addrs_default;
				}else{
					$Paddress = D('Paddress')->where(array('user_id' => $user_id))->order(array('id' => 'desc'))->limit(0, 6)->select();
					return $Paddres;	
				}				
            }
        } else {
			$this->baoJump(U('members/malladdress/create',  array('type' => crowd, 'goods_id' =>$order['goods_id'])));//都没找到
        }
        return true;//默认
    }
	
   //付款页面获取收货地址
    public function wap_addrs_address($order_id,$user_id) {
		 $order_id = (int) $order_id;
		 $user_id = (int) $user_id;
		 $order = D('Crowdorder')->find($order_id);
		 if (!empty($order['address_id'])) {
			 //如果订单里面的地址查询为空就下面找到默认地址
            $addrs = D('Paddress')->where(array('user_id' => $user_id, 'id' => $order['address_id']))->find();
            if (!empty($addrs)) {
				return $addrs;
            } else {//先找默认地址排序
				$addrs_default = D('Paddress')->where(array('user_id' => $user_id,'default'=>1))->find();
				if(!empty($addrs_default)){
					return $addrs_default;
				}else{
					$Paddress = D('Paddress')->where(array('user_id' => $user_id))->order(array('id' => 'desc'))->find();//随便去第一条
					return $Paddres;	
				}				
            }
        } else {
			return false;//找不到地址
        }
        return true;//默认
    }
	
	//更换众筹付款页面的地址
    public function replace_crowd_pay_addr($order_id,$id,$uid) {
		 $order_id = (int) $order_id;
		 $id = (int) $id;
		 $uid = (int) $uid;
		 $Paddress =D('Paddress');
		 if(!empty($uid)){
			 $default = $Paddress->where(array('user_id'=>$uid,'default'=>1))->select();
			 if(!empty($default)){
				foreach ($default as $k => $v){
					$Paddress->save(array('id' => $v['id'], 'default' => 0)); //取消默认收货地址
				} 
			 }
			 $Paddress->where(array('id'=>$id))->save(array('default' => 1));
			  //修改订单地址
			 D('Crowdorder')->where(array('order_id'=>$order_id))->save(array('address_id' =>$id));
		 }else{
			return false;//找不到用户ID
		 }
		 return true;//默认
    }
	
	
	
}

