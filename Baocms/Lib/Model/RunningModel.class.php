<?php
class RunningModel extends CommonModel{
    protected $pk   = 'running_id';
    protected $tableName =  'running';
	protected $types = array(
		0 => '未付款', 
		1 => '已付款', 
		2 => '跑腿中', 
		3 => '跑完腿', 
		4 => '退款中', //待开发
		5 => '已退款', //待开发
		8 => '已完成'
	);

    public function getType(){
        return $this->types;
    }
	public function getError() {
        return $this->error;
    }
	//检测发布跑腿时间
	public function Check_Running_Interval_Time($uid) {
		$uid = (int) $uid;
		$running = D('Running')->where(array('user_id'=>$uid))->order('create_time desc')->find();
		if(!empty($running)){
			$config = D('Setting')->fetchAll();
			$current_time = NOW_TIME;
			$interval_time_difference = $current_time - $running['create_time'];
			$cha = $config['running']['interval_time'] - $interval_time_difference;
			if($interval_time_difference < $config['running']['interval_time']){
				$this->error = '发布太频繁了请休息'.$cha.'秒后再来提交哦';
            	return false;
			}
		}else{
			return true;
		}
        return true;
    }
	//有余额直接在线付款
	public function Pay_Running($running_id,$uid){
        $running_id = (int) $running_id;
		$uid = (int) $uid;
        $running = D('Running')->find($running_id);
		$users = D('Users')->find($uid);
        if (empty($running)){
			$this->error = '该订单不存在';
            return false;
        }elseif($running['status'] != 0 ){
			$this->error = '订单状态不正确';
            return false;
		}elseif($running['user_id'] != $uid){
			$this->error = '请不要非法操作1';
            return false;
		}elseif($running['need_pay'] <=0){
			$this->error = '交易金额不正确';
            return false;
		}elseif($running['need_pay'] >= $users['money']){
			$this->error = '您的余额不足,请不要非法操作哦';
            return false;
		}
		if (false !== D('Users')->addMoney($uid, -$running['need_pay'], '发布跑腿' . $running['title'] . '扣费，订单号：'.$running_id)) {
			 if (D('Running')->save(array('running_id' => $running_id, 'status' => '1'))) {
				D('Sms')->sms_running_user($running_id);//短信通知用户
				D('Sms')->sms_delivery_user($running_id,2);//批量通知配送员抢单
				return TRUE; 
			 }else{
				$this->error = '更新付费状态失败，请联系管理员';
				return false;	 
			}
        } else {
			$this->error = '抱歉扣费失败，请稍后再试';
            return false;
        }
		return true;
    }
	
	//配送员接单偶处理逻辑的封装
	public function Running_Confirm_Complete($running_id,$cid){
		$running = D('Running')->find($running_id);
		if(!empty($running)){
			if($running['status'] == 1){//抢单中
			    $data = array('running_id' => $running_id,'cid' => $cid,'status' => 2,'update_time' => NOW_TIME);
                if (D('Running')->save($data)){
					D('Sms')->sms_Running_Delivery_User($running_id);//给买家通知配送状态
                    return true;
                }
				return true;
		    }elseif($running['status'] == 2){//配送逻辑
				 if (false !== D('Running')->save( array('running_id' => $running_id,'status' => 3,'end_time' => NOW_TIME))){
                    D('Sms')->sms_Running_Delivery_User($running_id);//给买家通知配送状态
                    $info = '跑腿订单结算：订单ID'.$running_id;
	
					if ($running['freight'] > 0) {
                        D('Runningmoney')->add(array(
						   'running_id' => $running_id, 
						   'delivery_id' => $running['cid'], 
						   'user_id' => $running['cid'], 
						   'money' => $running['freight'], 
						   'type' => running, 
						   'create_time' => NOW_TIME, 
						   'create_ip' => get_client_ip(), 
						   'intro' => $info
					));
                       D('Users')->addMoney($running['cid'], $running['freight'],$info);  //写入配送员余额
                    }
                    return true;
                }
			}else{
				return false;	 
			}
			
		}else{
			return true;
		}
        return true;
    }
}