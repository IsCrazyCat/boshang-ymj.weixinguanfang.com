<?php

class UserprofitlogsModel extends CommonModel {
    protected $pk = 'log_id';
    protected $tableName = 'user_profit_logs';
	
	
	
  //分销
	public function profitFusers($order_type = 0, $uid = 0, $order_id = 0) {
		if ($order_type === 0) {
			$model = D('Tuan');
			$map['o.order_id'] = $order_id;
			$join = ' INNER JOIN ' . C('DB_PREFIX') . 'tuan_order o ON o.tuan_id = t.tuan_id INNER JOIN ' . C('DB_PREFIX') . 'users u ON o.user_id = u.user_id';
			$goods = $model->alias('t')->field('t.*, o.total_price, u.fuid1, u.fuid2, u.fuid3, o.is_separate')->join($join)->where($map)->limit(0, 1)->select();
		}
		else {
			$model = D('Goods');
			$map['og.order_id'] = $order_id;

			$join = ' INNER JOIN ' . C('DB_PREFIX') . 'order_goods og ON g.goods_id = og.goods_id INNER JOIN ' . C('DB_PREFIX') . 'order o ON o.order_id = og.order_id INNER JOIN ' . C('DB_PREFIX') . 'users u ON o.user_id = u.user_id';
			$goods = $model->alias('g')->field('g.*, og.total_price, u.fuid1, u.fuid2, u.fuid3, o.is_separate')->join($join)->where($map)->limit(0, 1)->select();
		}
		$goods = $goods[0];
		if ($goods) {
			$userModel = D('Users');
			if ($goods['profit_rank_id']) {
				$rank = D('Userrank')->find($goods['profit_rank_id']);
				if ($rank) {
					$userModel->save(array('user_id' => $uid, 'rank_id' => $rank['rank_id'], 'prestige' => $rank['prestige']));
				}
			}
			if ($goods['profit_enable']  && !$goods['is_separate']) {
				if ($order_type === 0) {
					$modelOrder = D('Tuanorder');
					$orderTypeName = '团购';
				}
				else {
					$modelOrder = D('Order');
					$orderTypeName = '商城';
				}
				$profit_rate1 = (int)$goods['profit_rate1'];
				if ($goods['fuid1']) {
//					$money1 = round($profit_rate1 * $goods['total_price'] / 100);
                    //修改分销 由百分比更改为固定金额 2019-11-16
                    $money1 = $profit_rate1 * 100;
					if ($money1 > 0) {
						$info1 = $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . round($money1 / 100, 2);
						$fuser1 = $userModel->find($goods['fuid1']);
						if ($fuser1) {
							$userModel->addMoney($goods['fuid1'], $money1, $info1);
							$userModel->addProfit($goods['fuid1'], $order_type, $order_id, $money1, 1);
						}
					}
				}
				$profit_rate2 = (int)$goods['profit_rate2'];
				if ($goods['fuid2']) {
//					$money2 = round($profit_rate2 * $goods['total_price'] / 100);
                    //修改分销 由百分比更改为固定金额 2019-11-16
                    $money2 = $profit_rate2 * 100;
					if ($money2 > 0) {
						$info2 = $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . round($money2 / 100, 2);
						$fuser2 = $userModel->find($goods['fuid2']);
						if ($fuser2) {
							$userModel->addMoney($goods['fuid2'], $money2, $info2);
							$userModel->addProfit($goods['fuid2'], $order_type, $order_id, $money2, 1);
						}

					}

				}
				$profit_rate3 = (int)$goods['profit_rate3'];
				if ($goods['fuid3']) {
//					$money3 = round($profit_rate3 * $goods['total_price'] / 100);
                    //修改分销 由百分比更改为固定金额 2019-11-16
                    $money3 = $profit_rate3 * 100;
					if ($money3 > 0) {
						$info3 = $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . round($money3 / 100, 2);
						$fuser3 = $userModel->find($goods['fuid3']);
						if ($fuser3) {
							$userModel->addMoney($goods['fuid3'], $money3, $info3);
							$userModel->addProfit($goods['fuid3'], $order_type, $order_id, $money3, 1);
						}
					}
				}
				$modelOrder->save(array('order_id' => $order_id, 'is_separate' => 0, 'is_profit' => 1));
			}
		}
	}
   //三级分销结束

}
