<?php

class AppointworkerModel extends CommonModel{
    protected $pk   = 'worker_id';
    protected $tableName =  'appoint_worker';


	
	//调用技师
	public function take_out_Appoint_worker($appoint_id){
		 $appoint_id = (int) $appoint_id;
		 $data = D('Appointworker')->where(array('appoint_id'=>$appoint_id,'closed'=>0))->order(array('create_time' => 'desc'))->select();
		 if(!empty($data)){
			 return $data;
		}else{
			return false;	 
		}
    }
	
	
	//获取技师的评分值返回整数
	public function get_worker_score($worker_id){
		$score = (int) D('Appointdianping')->where(array('worker_id' => $worker_id,'closed' => 0, 'show_date' => array('ELT', TODAY)))->avg('score');
        if ($score == 0) {
            $score = 0;
        }
		return $score;
    }
}