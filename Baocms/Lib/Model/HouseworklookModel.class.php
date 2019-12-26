<?php

class HouseworklookModel  extends  CommonModel{
    protected $pk   = 'look_id';
    protected $tableName =  'housework_look';
    
    public function checkIsLook($shop_id,$housework_id){
        
        return $this->find(array('where'=>array('shop_id'=>(int)$shop_id,'housework_id'=>(int)$housework_id)));
    }
    
    public function checkLook($shop_id,$housework_ids){
        $datas = $this->where(array(
            'shop_id'=>(int)$shop_id,
            'housework_id' => array('IN',$housework_ids),
        ))->select();
        $return = array();
        foreach($datas as $val){
            $return[$val['housework_id']] = $val['housework_id'];
        }
        return $return;
    }
    
}