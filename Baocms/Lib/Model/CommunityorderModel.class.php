<?php
class CommunityorderModel extends CommonModel{
    protected $pk = 'order_id';
    protected $tableName = 'community_order';
    public function getType(){
        return array(
			'1' => '水费', 
			'2' => '电费', 
			'3' => '燃气费', 
			'4' => '停车费', 
			'5' => '物业费'
		);
    }
    public function orderpay($order_id, $user_id, $type, $total){
        $order_id = (int) $order_id;
        $detail = $this->find($order_id);
        $user_id = (int) $user_id;
        $products = D('Communityorderproducts')->where(array('order_id' => $order_id))->select();
        $needs = array();
        foreach ($products as $k => $val) {
            foreach ($type as $kk => $v) {
                if ($kk == $val['type']) {
                    $needs[$k] = $val;
                }
            }
        }
		//防止动作快多次点击付款重复扣费
        foreach ($needs as $k => $value) {
            $check = D('Communityorderproducts')->find($value['id']);
            if ($check[is_pay] == 1) {
                return true;
            }
        }
        $member = D('Users')->find($user_id);
        D('Users')->addMoney($user_id, -$total, '生活缴费，扣费');
        foreach ($needs as $k => $val) {
            D('Communityorderproducts')->save(array('id' => $val['id'], 'is_pay' => 1));
            D('Communityorderlogs')->add(array(
				'user_id' => $user_id, 
				'community_id' => $detail['community_id'], 
				'money' => $val['money'], 
				'type' => $val['type'], 
				'create_time' => NOW_TIME, 
				'create_ip' => get_client_ip()
			));
        }
        return true;
    }
}