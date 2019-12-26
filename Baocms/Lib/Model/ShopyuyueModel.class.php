<?php

class ShopyuyueModel extends CommonModel{
    protected $pk   = 'yuyue_id';
    protected $tableName =  'shop_yuyue';
    
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
    
}