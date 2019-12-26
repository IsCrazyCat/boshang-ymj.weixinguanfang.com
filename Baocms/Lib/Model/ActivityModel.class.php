<?php
class ActivityModel extends CommonModel{
    protected $pk   = 'activity_id';
    protected $tableName =  'activity';
    
    
     public function CallDataForMat($items) { //专门针对CALLDATA 标签处理的
        if (empty($items))
            return array();
        $obj = D('Shop');
        $shop_ids = array();
        foreach ($items as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $shops = $obj->itemsByIds($shop_ids);
        foreach ($items as $k => $val) {
            $val['shop'] = $shops[$val['shop_id']];
            $items[$k] = $val;
        }
        return $items;
    }
}