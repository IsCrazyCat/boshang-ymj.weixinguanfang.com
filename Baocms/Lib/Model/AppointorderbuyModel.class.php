<?php



class  AppointorderbuyModel  extends  CommonModel{
    protected $pk   = 'buy_id';
    protected $tableName =  'appoint_order_buy';
    
    //返回查询条件
    public function checkIsLook($shop_id,$housework_id){
        return $this->find(array('where'=>array('shop_id'=>(int)$shop_id,'appoint_id'=>(int)$appoint_id)));
    }
    //检测，这里已经修改
    public function checkLook($shop_id,$appoint_ids){
        $datas = $this->where(array('shop_id'=>(int)$shop_id,'appoint_id' => array('IN',$appoint_ids),))->select();
        $return = array();
        foreach($datas as $val){
            $return[$val['appoint_id']] = $val['appoint_id'];
        }
        return $return;
    }
    
}