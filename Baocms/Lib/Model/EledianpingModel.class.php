<?php



class EledianpingModel extends CommonModel {

    protected $pk = 'order_id';
    protected $tableName = 'ele_dianping';

    public function check($order_id, $user_id) {
        $data = $this->find(array('where' => array('order_id' => (int) $order_id, 'user_id' => (int) $user_id)));
        return $this->_format($data);
    }

}