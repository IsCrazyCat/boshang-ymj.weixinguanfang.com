<?php
class  UsertransferlogsModel extends CommonModel{
     protected $pk   = 'log_id';
     protected $tableName =  'user_transfer_logs';
	 
	 public function getError() {
        return $this->error;
    }
	
	
	//判断网站后台设置是否合法
	public function check_admin_is_transfer($cash_is_transfer){
		$config = D('Setting')->fetchAll();
		if($config['cash']['is_transfer'] == 0){
			return false;
		}elseif($config['cash']['is_transfer_big'] <= $config['cash']['is_transfer_small']){
			return false;
		}else{
			return true;
		}
		return true;
    }
	
    
	
	//检测被赠送的用户手机封装
	public function check_transfer_user_mobile($mobile,$user_mobile){
		if(!empty($mobile)){
		    $count_mobile = D('Users')->where(array('mobile' => $mobile))->count();
			if($count_mobile == 1){
				$user = D('Users')->where(array('mobile' => $mobile))->find();//这个版本不加手机号
				if (empty($user) || $user['mobile'] == $user_mobile) {
					$this->error = '手机号不存在或者手不能转账给自己';
					return false;
				} else {
					return true;
				}
			}else{
				$this->error = '检测到多个手机号重复，无法转账';
				return false;
			}
		}else{
			$this->error = '手机号不能为空';
			return false;
		}	
		return true;
     }
	 
	//检测余额小于0，用户余额是不是不足，超过最大限制，最小限制，检测用户转账间隔时间
	public function check_transfer_user_money($money,$uid){
		$config = D('Setting')->fetchAll();
		$users = D('Users')->find($uid);
		if(!empty($config['cash']['is_transfer'])){
			if(!empty($users)){
				if($money > 0){
				  if($money < $config['cash']['is_transfer_small']*100){
					  $this->error = '您转账的金额最少不得低于'.$config['cash']['is_transfer_small'].'元';
					  return false;
				  }elseif($money > $config['cash']['is_transfer_big']*100){
					  $this->error = '您转账的金额最多不得高于'.$config['cash']['is_transfer_big'].'元';
					  return false;
				  }
				  
				  if(!empty($config['cash']['is_transfer_commission'])){
					   $commission = intval(($money*$config['cash']['is_transfer_commission'])/100);
					   $receive_money = $money + $commission ;//实际扣除
					   if($users['money'] <= $receive_money){
						  $this->error = '对不起您的余额不足，无法完成转账，请先充值';
						  return false;
					  }
				  
				  }
				  if(!empty($config['cash']['is_transferrank_id'])){
					    $Userrank = D('Userrank')->find($users['rank_id']);
						if ($users['rank_id'] < $config['cash']['is_transferrank_id']) {
					 		 $this->error = '对不起，需要达到等级'.$Userrank['rank_name'].'后才能转账';
							 return false;
						}
				   }
				  $usertransferlogs = D('Usertransferlogs')->order(array('create_time' => 'desc'))->find($uid);
				  if(!empty($config['cash']['is_transfer_interval_time'])){
					    $present_time = NOW_TIME;//当前时间
					    $present_time_cha = $present_time - $usertransferlogs['create_time'];
						if ($present_time_cha < $config['cash']['is_transfer_interval_time']) {
							 $echo_cha_time = $config['cash']['is_transfer_interval_time'] - $present_time_cha;
					 		 $this->error = '操作太快请'.$echo_cha_time.'秒后再试';
							 return false;
						}
				   }
				  
				}else{
					$this->error = '您输入的转账金额不合法';
					return false;
				}
			}else{
				$this->error = '没有找到用户会员信息';
				return false;		
			}
			
		}else{
			$this->error = '网站没有开启转账功能';
			return false;
		}
		return true;
     }
	 
	 //获取接收的USER
	public function get_receive_users($mobile){
		if(!empty($mobile)){
			$user = D('Users')->where(array('mobile' => $mobile))->find();
			if (empty($user)) {
				return false;
			} else {
				return $user;
			}
		}else{
			return false;
		}	
		return false;
	}
}