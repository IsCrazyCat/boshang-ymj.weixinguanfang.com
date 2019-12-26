<?php
class RegisteripModel extends CommonModel{
    protected $pk = 'ip_id';
    protected $tableName = 'register_ip';
	//构造函数
	public function _initialize() {
        $config = D('Setting')->fetchAll();
        $this->_CONFIG = $config;
    }
	
	
	public function add_register_ip($ip){
		$obj = D('Registerip');
		$register_count = $obj->where(array('ip'=>$ip))->count();//统计
		if($register_count >= $this->_CONFIG['register']['register_register_ip_num'] ){
			$register_ip = $obj->where(array('ip'=>$ip))->select();
			foreach ($register_ip as $k => $v){
				$obj->save(array('ip_id' => $v['ip_id'],'is_lock'=>1)); 
			}
		}else{
			$data['ip']= $ip;
			$data['create_time'] = NOW_TIME;
			$obj->add($data);	
		}
		return true;	
	}
	
	
	public function check_register_time($ip){
		$obj = D('Registerip');
		$register_count = $obj->where(array('ip'=>$ip))->count();//统计
		if($register_count >= $this->_CONFIG['register']['register_register_ip_num'] ){
			$register = $obj->where(array('ip'=>$ip))->order(array('create_time' => 'desc'))->find();
			if(!empty($register)){
				$present_now_time = NOW_TIME;//当前时间
					if (($present_now_time - $register['create_time']) < $this->_CONFIG['register']['register_register_is_lock_time'] ) {
						 return false;
					}
			}
		   return true;	
		}
		return true;
	}
	
	public function delete_register_ip($ip){
		$obj = D('Registerip');
		$register_ip = $obj->where(array('ip'=>$ip))->select();
		foreach ($register_ip as $k => $v){
			$obj->delete(array('ip' => $v['ip_id'])); 
        }
		return true;
	}
	
   
}