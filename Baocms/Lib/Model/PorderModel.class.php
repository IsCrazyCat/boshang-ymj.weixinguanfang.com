<?php

class PorderModel extends CommonModel {

	protected $pk = 'id';
	protected $tableName = 'porder';

	protected $orderStatusArray = array(1 => '待支付', 2 => '已支付', 3 => '已确认，待发货', 4 => '配送中', 5 => '已签收', 6 => '交易已取消', 7 => '退款处理中', 8 => '退款成功',9 => '已出单，待配送' );
	protected $tstatusArray = array(0 => '普通订单', 1 => '开团订单', 2 => '参团订单', );
	protected $tuanStatusArray = array(1 => '未支付', 2 => '已支付，拼团中', 3 => '拼团成功', 4 => '拼团失败', );

	public function getorderStatus() {

		return $this -> orderStatusArray;

	}

	public function getTstatus() {

		return $this -> tstatusArray;

	}

	public function gettuanStatus() {

		return $this -> tuanStatusArray;

	}


}
