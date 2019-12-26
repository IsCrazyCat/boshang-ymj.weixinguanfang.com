<?php
class MessageModel extends CommonModel{
    protected $pk   = 'msg_id';
    protected $tableName =  'message';

	public function send($send_id,$receive_id,$parent_id,$content) { 
		$send_id = (int) $send_id;
		$receive_id = (int) $receive_id;
		$parent_id = (int) $parent_id;
		if($send_id != 0){
			if(!D('User')->find($send_id)){
				return '0';
				die;
			}
		}
		if($receive_id != 0){
			if(!D('User')->find($receive_id)){
				return '0';
				die;
			}
		}else{
			return '0';
			die;
		}
		if(empty($content)){
			return '0';
			die;
		}
		$data = array();
		$data['send_id'] = $send_id;
		$data['receive_id'] = $receive_id;
		$data['parent_id'] = $parent_id;
		$data['content'] = $content;
		$data['create_time'] = time();
		$msg_id = D('Message')->add($data);
		return $msg_id;
	}

	
}



