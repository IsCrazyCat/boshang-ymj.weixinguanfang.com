<?php
class CoupondownloadModel extends CommonModel{
    protected $pk   = 'download_id';
    protected $tableName =  'coupon_download';
    
	public function getError() {
        return $this->error;
    }
	
	
    public function getCode(){       
        $i=0;
        while(true){
            $i++;
            $code = rand_string(8,1);
            $data = $this->find(array('where'=>array('code'=>$code)));
            if(empty($data)) return $code;
            if($i > 10) return $code;//CODE 做了唯一索引，如果大于10 我们也跳出循环以免更多资源消耗
        }
        
    }
	//检测状态通用
	 public function check_coupondownload_state($download_id,$uid){ 
	    if (!($detail = D('Coupondownload')->find($download_id))) {
			$this->error = '没有该优惠券';
			return false;
        }
        if ($detail['user_id'] != $uid) {
           $this->error = '请不要非法操作';
			return false;
        }
		if ($detail['is_used'] != 0) {
            $this->error = '该优惠券属于不可消费的状态';
			return false;
        }
		$coupon = D('Coupon')->find($detail['coupon_id']);
		if ($coupon['expire_date'] < TODAY) {
			$this->error = '该优惠券已经过期';
			return false;
        }
       return true; 
	 
	 }
	 
	 //如果用户没有手机号码，赠送的时候自动注册
	 public function register_account_give_coupon($download_id,$mobile){ 
	     $coupondownload = D('Coupondownload')->find($download_id);
	     $coupon = D('Coupon')->find($coupondownload['coupon']);
		 $shop = D('Shop')->find($coupon['shop_id']);
	     $user = D('Users')->where(array('mobile' => $mobile))->find();
         if (empty($coupondownload)) {
            $this->error = '没有找到该优惠劵';
			return false;
         }elseif($coupondownload['is_used'] ==1){
            $this->error = '该优惠劵已经是消费了';
			return false;
		 }elseif($coupon['expire_date'] < TODAY){
            $this->error = '该优惠券已经过期';
			return false;
		 }elseif(!empty($user)){
            $this->error = '这个手机已经存在账户，暂时无法注册';
			return false;
		 }elseif(empty($shop)){
            $this->error = '该优惠劵商家不存在';
			return false;
		 }elseif($shop['closed'] == 1){
            $this->error = '该优惠劵商家已被删除';
			return false;
		 }elseif($coupondownload['user_id'] == $uid){
			$this->error = '不能赠送给自己';
		    return false;
		 }else{
			    $data = array();
				$data['account'] = $mobile;
				$data['password'] = md5($mobile);
       			$data['nickname'] = $mobile;
				$data['reg_time'] = NOW_TIME;
				$data['reg_ip'] = get_client_ip();
				$user_id = D('Users')->add($data);
				if($user_id){
					if (FALSE !== D('Coupondownload')->save(array('download_id' => $download_id, 'user_id' => $user_id))){
						D('Users')->save(array('user_id' => $user_id, 'fuid1' => $coupondownload['user_id']));//注册的用户的上级是推荐的用户
						D('Sms')->sms_coupon_give_user($download_id,$user_id);
		    			return true; 
					}else{
						$this->error = '写入数据库操作失败';
						return false;
					}
				}else{
					$this->error = '注册新用户失败';
					return false;
				} 
	   }
       return true; 
	 
	 }
    
    public function CallDataForMat($items){ //专门针对CALLDATA 标签处理的
        if(empty($items)) return array();
        $obj = D('Coupon');        
        $coupon_ids = array();
        foreach($items as $k=>$val){
            $coupon_ids[$val['coupon_id']] = $val['coupon_id'];
        }       
        $coupons = $obj->itemsByIds($coupon_ids);
        foreach($items as $k=>$val)
        {
            $val['coupon'] = $coupons[$val['coupon_id']];
            $items[$k] = $val;
        }
        return $items;
    }
}