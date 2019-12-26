<?php
class MoneyAction extends CommonAction {
    public function index() {
    	$user_delivery_count_money = D('Runningmoney')->where(array('user_id'=>$this->uid))->sum('money');
		$this->assign('user_delivery_count_money', $user_delivery_count_money);
        $this->display();
    }
}