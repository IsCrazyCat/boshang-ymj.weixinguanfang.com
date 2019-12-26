<?php


class ThreadcollectModel extends CommonModel{
    protected $pk   = 'thread_id,user_id';
    protected $tableName =  'thread_collect';
    
    
    public function check($thread_id,$user_id){
        $data = $this->find(array('where'=>array('thread_id'=>(int)$thread_id,'user_id'=>(int)$user_id)));
        return $this->_format($data);
    }
    
}