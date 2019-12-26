<?php

class LogisticsModel extends CommonModel{
    protected $pk   = 'express_id';
    protected $tableName =  'logistics';
	
	public function get_order_express($order_id){
		import('ORG.Util.Express');//引入类
		$express_obj = new Express();
		$express_obj -> keys = $this->_CONFIG['config']['express_api'];
		$mall_order = D('Order')->where(array('order_id' => $order_id))->find();//订单
		$logistics = D('Logistics')->where(array('express_id' => $mall_order['express_id']))->find();
		$express_obj -> company = $logistics['express_com'];//传入快递编号
		$express_obj -> num = $mall_order['express_number'];//传入快递单号
		$data = $express_obj->getContent();//获取数组
		return $data;
	}

}