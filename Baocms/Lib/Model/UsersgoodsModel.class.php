<?php
class UsersgoodsModel extends CommonModel{
    protected $pk   = 'record_id';
    protected $tableName =  'users_goods';
    
    public function getRecord($user_id,$goods_id){
        if (!empty($user_id)) {
            $result['user_id'] = $user_id;
            $result['goods_id'] = $goods_id;
            $result['record_time'] = NOW_TIME;
            $result['record_ip'] = get_client_ip();
            $res = $this->where(array('user_id' => $user_id, 'goods_id' => $goods_id))->find();
            if (empty($res)) {
                $record_id = $this->add($result);
            } else {
                $result['record_id'] = $res['record_id'];
                $this->save($result);
            }
        }
    }
    
}