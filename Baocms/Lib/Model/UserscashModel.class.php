<?php
class UserscashModel extends CommonModel {
    protected $pk = 'cash_id';
    protected $tableName = 'users_cash';

    //检测分站的提现每天提现多少次
	public function check_cash_addtime($user_id,$type){
		$config = D('Setting')->fetchAll();
		$bg_time = strtotime(TODAY);
		
		if($type == 1){
			$count = $this->where(array('user_id'=>$user_id,'type'=>user,'addtime' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
			if($config['cash']['user_cash_second']){
				if($count > $config['cash']['user_cash_second']){
					return false;
				}
			}
			return true; 
		}elseif($type == 2){
			$count = $this->where(array('user_id'=>$user_id,'type'=>shop,'addtime' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
			if($config['cash']['shop_cash_second']){
				if($count > $config['cash']['shop_cash_second']){
					return false;
				}
			}
			return true;
		}else{
			return true;
		}

    }
}
