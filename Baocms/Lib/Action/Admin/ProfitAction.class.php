<?php
/*rollback目前分成管理还有很大问题，价格应该按照结算价格计算，因为系统没调试好，暂时用网站售价计算好了后期修改*/
class ProfitAction extends CommonAction{
    public function order(){
        $model = D('Order');
        import('ORG.Util.Page');// 导入分页类
        $map = array('is_profit'=>1);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        if ($user_id = (int)$this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        if (isset($_POST['status'])) {
            $status = (int)$this->_param('status');
            if ($status != -1) {
                $map['is_separate'] = $status;
            }
            $this->assign('status', $status);
        }
        $map['u.fuid1'] = array('GT', 0);
        $join = ' LEFT JOIN ' . C('DB_PREFIX') . 'users u ON u.user_id = o.user_id LEFT JOIN ' . C('DB_PREFIX') . 'user_profit_logs p ON p.order_id = o.order_id AND p.order_type = 1 AND p.user_id = u.fuid1';
		
        $count = $model->alias('o')->join($join)->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $model->alias('o')->field('o.*, u.account, u.fuid1')->join($join)->where($map)->order(array('o.order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $profitLogs = $orderIds = array();
        foreach($list as $k => $v) {
            $orderIds[] = $v['order_id'];
        }
        $map = array('order_type' => 1, 'order_id' => array('IN', $orderIds));
        $join = ' LEFT JOIN ' . C('DB_PREFIX') . 'users u ON u.user_id = o.user_id';
        $logs = D('Userprofitlogs')->alias('o')->field('o.*, u.account')->join($join)->where($map)->order(array('o.log_id' => 'ASC'))->select();
        foreach($logs as $k => $v) {
            $profitLogs[$v['order_id']][] = $v;
        }
        $this->assign('list', $list);
        $this->assign('profitLogs', $profitLogs);
        $this->assign('page', $show);
        $this->display(); 
    }

    public function tuanorder() {
        $model = D('Tuanorder');
        import('ORG.Util.Page');
        $map = array('is_profit'=>1);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        if ($user_id = (int)$this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        if (isset($_POST['status'])) {
            $status = (int)$this->_param('status');
            if ($status != -1) {
                $map['is_separate'] = $status;
            }
            $this->assign('status', $status);
        }
        $map['u.fuid1'] = array('GT', 0);
        $join = ' LEFT JOIN ' . C('DB_PREFIX') . 'users u ON u.user_id = o.user_id LEFT JOIN ' . C('DB_PREFIX') . 'user_profit_logs p ON p.order_id = o.order_id AND p.order_type = 0 AND p.user_id = u.fuid1';
        $count = $model->alias('o')->join($join)->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $model->alias('o')->field('o.*, u.account, u.fuid1')->join($join)->where($map)->order(array('o.order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $profitLogs = $orderIds = array();
        foreach($list as $k => $v) {
            $orderIds[] = $v['order_id'];
        }
        $map = array('order_type' => 0, 'order_id' => array('IN', $orderIds));
        $join = ' LEFT JOIN ' . C('DB_PREFIX') . 'users u ON u.user_id = o.user_id';
        $logs = D('Userprofitlogs')->alias('o')->field('o.*, u.account')->join($join)->where($map)->order(array('o.log_id' => 'ASC'))->select();
        foreach($logs as $k => $v) {
            $profitLogs[$v['order_id']][] = $v;
        }
        $this->assign('list', $list);
        $this->assign('profitLogs', $profitLogs);
        $this->assign('page', $show);
        $this->display(); 
    }
	
	
	 public function breaks(){// 优惠买单
        $model = D('Breaksorder');
        import('ORG.Util.Page');

		$map['status'] = array('EQ', 1);
		$map['is_profit'] = array('EQ', 1);
		
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        if ($user_id = (int)$this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        if (isset($_POST['status'])) {
            $status = (int)$this->_param('status');
            if ($status != -1) {
				$map['is_separate'] = array('EQ', $status);
            }
            $this->assign('status', $status);
        }
		
        $map['u.fuid1'] = array('GT', 0);
        $join = ' LEFT JOIN ' . C('DB_PREFIX') . 'users u ON u.user_id = o.user_id LEFT JOIN ' . C('DB_PREFIX') . 'user_profit_logs p ON p.order_id = o.order_id AND p.order_type = 2 AND p.user_id = u.fuid1';

        $count = $model->alias('o')->join($join)->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $model->alias('o')->field('o.*, u.account, u.fuid1')->join($join)->where($map)->order(array('o.order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $profitLogs = $orderIds = array();
        foreach($list as $k => $v) {
            $orderIds[] = $v['order_id'];
        }
        $map = array('order_type' => 2, 'order_id' => array('IN', $orderIds));
        $join = ' LEFT JOIN ' . C('DB_PREFIX') . 'users u ON u.user_id = o.user_id';
        $logs = D('Userprofitlogs')->alias('o')->field('o.*, u.account')->join($join)->where($map)->order(array('o.log_id' => 'ASC'))->select();
        foreach($logs as $k => $v) {
            $profitLogs[$v['order_id']][] = $v;
        }
        $this->assign('list', $list);
        $this->assign('profitLogs', $profitLogs);
        $this->assign('page', $show);
        $this->display(); 
    }
	
	

    public function ok($order_id, $order_type = 0) {
        $order_id = (int)$order_id;
        $order_type = (int)$order_type;
        if (!in_array($order_type, array(0, 1))) $order_type = 0;
        if ($order_type === 0) {
            $model = D('Tuanorder');
            $orderTypeName = '团购';
            $url = U('profit/tuanorder');
        }
        else {
            $model = D('Order');
            $orderTypeName = '商城';
            $url = U('profit/order');
        }
        $join = ' LEFT JOIN ' . C('DB_PREFIX') . 'users u ON u.user_id = o.user_id LEFT JOIN ' . C('DB_PREFIX') . 'user_profit_logs p ON p.order_id = o.order_id AND p.order_type = ' . $order_type;
        $map = array('o.order_id' => $order_id);
        $order = $model->alias('o')->field('o.*, u.account, u.fuid1, u.fuid2, u.fuid3')->join($join)->where($map)->limit(0, 1)->select();
        $order = $order[0];
        if (!$order) {
            $this->baoError('您要分成的订单不存在！');
        }
        elseif (!$order['is_separate']) {
            $userModel = D('Users');
            $profit_rate1 = (int)$this->_CONFIG['profit']['profit_rate1'];
            if ($order['fuid1']) {
//                $money1 = round($profit_rate1 * $order['total_price'] / 100);
                //修改分销 由百分比更改为固定金额 2019-11-16
                $money1 = $profit_rate1 * 100;
                if ($money1 > 0) {
                    $info1 = $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . round($money1 / 100, 2);
                    $fuser1 = $userModel->find($order['fuid1']);
                    if ($fuser1) {
                        $userModel->addMoney($order['fuid1'], $money1, $info1);
                        $userModel->addProfit($order['fuid1'], $order_type, $order_id, $money1, 1);
                    }
                }
            }
            $profit_rate2 = (int)$this->_CONFIG['profit']['profit_rate2'];
            if ($order['fuid2']) {
                //修改分销 由百分比更改为固定金额 2019-11-16
//                $money2 = round($profit_rate2 * $order['total_price'] / 100);
                $money2 = $profit_rate2 * 100;
                if ($money2 > 0) {
                    $info2 = $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . round($money2 / 100, 2);
                    $fuser2 = $userModel->find($order['fuid2']);
                    if ($fuser2) {
                        $userModel->addMoney($order['fuid2'], $money2, $info2);
                        $userModel->addProfit($order['fuid2'], $order_type, $order_id, $money2, 1);
                    }
                }
            }
            $profit_rate3 = (int)$this->_CONFIG['profit']['profit_rate3'];
            if ($order['fuid3']) {
                //修改分销 由百分比更改为固定金额 2019-11-16
//                $money3 = round($profit_rate3 * $order['total_price'] / 100);
                $money3 = $profit_rate3 * 100;
                if ($money3 > 0) {
                    $info3 = $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . round($money3 / 100, 2);
                    $fuser3 = $userModel->find($order['fuid3']);
                    if ($fuser3) {
                        $userModel->addMoney($order['fuid3'], $money3, $info3);
                        $userModel->addProfit($order['fuid3'], $order_type, $order_id, $money3, 1);
                    }
                }
            }
            $model->save(array('order_id' => $order_id, 'is_separate' => 1));
        }
        $this->baoSuccess('操作成功', $url);
    }

    public function cancel($order_id, $order_type = 0) {
        $order_id = (int)$order_id;
        $order_type = (int)$order_type;
        if (!in_array($order_type, array(0, 1))) $order_type = 0;
        if ($order_type === 0) {
            $model = D('Tuanorder');
            $url = U('profit/tuanorder');
        }
        else {
            $model = D('Order');
            $url = U('profit/order');
        }
        $join = ' LEFT JOIN ' . C('DB_PREFIX') . 'users u ON u.user_id = o.user_id LEFT JOIN ' . C('DB_PREFIX') . 'user_profit_logs p ON p.order_id = o.order_id AND p.order_type = ' . $order_type;
        $map = array('o.order_id' => $order_id);
        $order = $model->alias('o')->field('o.*, u.account, u.fuid1, u.fuid2, u.fuid3')->join($join)->where($map)->limit(0, 1)->select();
        $order = $order[0];
        if (!$order) {
            $this->baoError('您要取消分成的订单不存在！');
        }
        elseif (!$order['is_separate']) {
            $model->save(array('order_id' => $order_id, 'is_separate' => 3));
        }
        else {
            $this->baoError('对不起，此订单已处理过！');
        }
        $this->baoSuccess('操作成功', $url);
    }

    public function rollback($order_id, $order_type = 0) {
        $order_id = (int)$order_id;
        $order_type = (int)$order_type;
        if (!in_array($order_type, array(0, 1))) $order_type = 0;
        if ($order_type === 0) {
            $model = D('Tuanorder');
            $orderTypeName = '团购';
            $url = U('profit/tuanorder');
        }
        else {
            $model = D('Order');
            $orderTypeName = '商城';
            $url = U('profit/order');
        }
        $join = ' LEFT JOIN ' . C('DB_PREFIX') . 'users u ON u.user_id = o.user_id LEFT JOIN ' . C('DB_PREFIX') . 'user_profit_logs p ON p.order_id = o.order_id AND p.order_type = ' . $order_type;
        $map = array('o.order_id' => $order_id);
        $order = $model->alias('o')->field('o.*, u.account, u.fuid1, u.fuid2, u.fuid3')->join($join)->where($map)->limit(0, 1)->select();
        $order = $order[0];
        if (!$order) {
            $this->baoError('您要撤消分成的订单不存在！');
        }
		 
        elseif ($order['is_separate'] == 1) {
            $userModel = D('Users');
            $userProfitModel = D('Userprofitlogs');
            $profit_rate1 = (int)$this->_CONFIG['profit']['profit_rate1'];
			
			
			if ($order_type === 1) {//如果是商城找到分成比例
            $ordergoods_ids = D('Ordergoods') -> where('order_id ='.$order['order_id']) -> find();
			$goods_ids = D('Goods') -> where('goods_id ='.$ordergoods_ids['goods_id']) -> find();
			$profit_rate1_rollback = $goods_ids['profit_rate1'];
			$profit_rate2_rollback = $goods_ids['profit_rate2'];
			$profit_rate3_rollback = $goods_ids['profit_rate3'];
            }
			
			if ($order_type === 0) {//如果是团购,找到分成比例
			$tuan_ids = D('Tuan') -> where('tuan_id ='.$order['tuan_id']) -> find();
			$profit_rate1_rollback = $tuan_ids['profit_rate1'];
			$profit_rate2_rollback = $tuan_ids['profit_rate2'];
			$profit_rate3_rollback = $tuan_ids['profit_rate3'];
            }

			
            if ($order['fuid1']) {
				if(!empty($profit_rate1)){
//					$money1 = round($profit_rate1 * $order['total_price'] / 100);//这一步有问题,不应该按照原价计算，后期修改
                    $money1 = $profit_rate1 * 100;
					}else{
//					$money1 = round($profit_rate1_rollback * $order['total_price'] / 100);//这一步有问题，不应该按照原价计算，后期修改
                    $money1 = $profit_rate1_rollback * 100;
				}
                if ($money1 > 0) {
                    $info1 = '分成被管理员取消，' . $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . round($money1 / 100, 2);
                    $fuser1 = $userModel->find($order['fuid1']);
					
                    if ($fuser1) {
						
                        $userModel->addMoney($order['fuid1'], -$money1, $info1);
                        $userProfitModel->save(array('is_separate' => 2), array('where' => array('order_id' => $order_id, 'order_type' => $order_type, 'user_id' => $order['fuid1'])));
                    }
                }
            }
			
			
            $profit_rate2 = (int)$this->_CONFIG['profit']['profit_rate2'];
            if ($order['fuid2']) {
				
				if(!empty($profit_rate2)){
					$money2 = round($profit_rate2 * $order['total_price'] / 100);//这一步有问题
                    $money2 = $profit_rate2;
					}else{
//					$money2 = round($profit_rate2_rollback * $order['total_price'] / 100);//这一步有问题，不应该按照原价计算，后期修改
                    $money2 = $profit_rate2_rollback;
				}

				
				
                if ($money2 > 0) {
                    $info2 = '分成被管理员取消，' . $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . round($money2 / 100, 2);
                    $fuser2 = $userModel->find($order['fuid2']);
                    if ($fuser2) {
                        $userModel->addMoney($order['fuid2'], -$money2, $info2);
                        $userProfitModel->save(array('is_separate' => 2), array('where' => array('order_id' => $order_id, 'order_type' => $order_type, 'user_id' => $order['fuid2'])));
                    }
                }
            }
            $profit_rate3 = (int)$this->_CONFIG['profit']['profit_rate3'];
            if ($order['fuid3']) {
				
				if(!empty($profit_rate2)){
//					$money3 = round($profit_rate3 * $order['total_price'] / 100);//这一步有问题
                    $money3 = $profit_rate3;
					}else{
//					$money3 = round($profit_rate3_rollback * $order['total_price'] / 100);//这一步有问题，不应该按照原价计算，后期修改
                    $money3 = $profit_rate3_rollback;
				}
				
        				
   if ($money3 > 0) {
                    $info3 = '分成被管理员取消，' . $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . round($money3 / 100, 2);
                    $fuser3 = $userModel->find($order['fuid3']);
                    if ($fuser3) {
                        $userModel->addMoney($order['fuid3'], -$money3, $info3);
                        $userProfitModel->save(array('is_separate' => 2), array('where' => array('order_id' => $order_id, 'order_type' => $order_type, 'user_id' => $order['fuid3'])));
                    }
                }
            }
            $model->save(array('order_id' => $order_id, 'is_separate' => 2));
        }
        else {
            $this->baoError('对不起，此订单已处理过！');
        }
		
        $this->baoSuccess('操作成功1', $url);
    }

    public function user() {
        $User = D('Users');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('u.closed'=>array('IN','0,-1'));
        if($account = $this->_param('account','htmlspecialchars')){
            $map['u.account'] = array('LIKE','%'.$account.'%');
            $this->assign('account',$account);
        }
        if($nickname = $this->_param('nickname','htmlspecialchars')){
            $map['u.nickname'] = array('LIKE','%'.$nickname.'%');
            $this->assign('nickname',$nickname);
        }
        if($rank_id = (int)$this->_param('rank_id')){
            $map['u.rank_id'] = $rank_id;
            $this->assign('rank_id',$rank_id);
        }

        if($ext0 = $this->_param('ext0','htmlspecialchars')){
            $map['u.ext0'] = array('LIKE','%'.$ext0.'%');
            $this->assign('ext0',$ext0);
        }
        $profit_min_rank_id = (int)$this->_CONFIG['profit']['profit_min_rank_id'];
        if ($profit_min_rank_id) {
            $rank = D('Userrank')->find($profit_min_rank_id);
            if ($rank) {
                $map['u.prestige'] = array('EGT', $rank['prestige']);
            }
        }
        $join = ' LEFT JOIN ' . C('DB_PREFIX') . 'users f ON f.user_id = u.fuid1';
        $count = $User->alias('u')->join($join)->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->alias('u')->field('u.*, f.user_id AS fuserid, f.account AS fusername')->join($join)->where($map)->order(array('u.user_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $uids = $level1 = $level2 = $level3 = array();
        foreach($list as $k=>$val){
            $val['reg_ip_area'] = $this->ipToArea($val['reg_ip']);
            $val['last_ip_area']   = $this->ipToArea($val['last_ip']);
            $uids[$val['user_id']] = $val['user_id'];
            $list[$k] = $val;
        }
        $tmpLevel1 = $User->field(array('COUNT(*)' => 'cnt', 'fuid1'))->group('fuid1')->select();
        foreach($tmpLevel1 as $k => $v) {
            $level1[$v['fuid1']] = $v['cnt'];
        }
        $tmpLevel2 = $User->field(array('COUNT(*)' => 'cnt', 'fuid2'))->group('fuid2')->select();
        foreach($tmpLevel2 as $k => $v) {
            $level2[$v['fuid2']] = $v['cnt'];
        }
        $tmpLevel3 = $User->field(array('COUNT(*)' => 'cnt', 'fuid3'))->group('fuid3')->select();
        foreach($tmpLevel3 as $k => $v) {
            $level3[$v['fuid3']] = $v['cnt'];
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('level1', $level1);
        $this->assign('level2', $level2);
        $this->assign('level3', $level3);
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('ranks',D('Userrank')->fetchAll());
        $this->display(); // 输出模板
    }
	
	
	 public function breaks_rollback($order_id, $order_type = 0) {
        $order_id = (int)$order_id;
        $order_type = (int)$order_type;
		
		static $CONFIG;
        if (empty($CONFIG)) {
            $CONFIG = D('Setting')->fetchAll();
        }
		$model = D('Breaksorder');
		$url = U('profit/breaks');
		$orderTypeName = '优惠买单';
		
        $join = ' LEFT JOIN ' . C('DB_PREFIX') . 'users u ON u.user_id = o.user_id LEFT JOIN ' . C('DB_PREFIX') . 'user_profit_logs p ON p.order_id = o.order_id AND p.order_type = ' . $order_type;
        $map = array('o.order_id' => $order_id);
        $order = $model->alias('o')->field('o.*, u.account, u.fuid1, u.fuid2, u.fuid3')->join($join)->where($map)->limit(0, 1)->select();

		$order = $order[0];
        if (!$order) {
            $this->baoError('您要撤消分成的订单不存在！');
        }elseif ($order['is_separate'] == 0) {
            $userModel = D('Users');
            $userProfitModel = D('Userprofitlogs');
		
            if ($order['fuid1']) {
				$money1 = round($CONFIG['profit']['breaks_profit_rate1'] * $order['deduction'],2);//百分比*分成总金额加上2小数点
                if ($money1 > 0) {
                    $info1 = '分成被管理员取消，' . $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . round($money1 / 100, 2);
                    $fuser1 = $userModel->find($order['fuid1']);
                    if ($fuser1) {
                        $userModel->addMoney($order['fuid1'], -$money1, $info1);
                        $userProfitModel->save(array('is_separate' => 2), array('where' => array(
							'order_id' => $order_id, 
							'order_type' => $order_type, 
							'user_id' => $order['fuid1']
						)));
                    }
                }
            }
			
			
            if ($order['fuid2']) {
				$money2 = round($CONFIG['profit']['breaks_profit_rate2'] * $order['deduction'],2);//百分比*分成总金额加上2小数点
                if ($money2 > 0) {
                    $info2 = '分成被管理员取消，' . $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . round($money2 / 100, 2);
                    $fuser2 = $userModel->find($order['fuid2']);
                    if ($fuser2) {
                        $userModel->addMoney($order['fuid2'], -$money2, $info2);
                        $userProfitModel->save(array('is_separate' => 2), array('where' => array(
							'order_id' => $order_id, 
							'order_type' => $order_type, 
							'user_id' => $order['fuid2']
						)));
                    }
                }
            }
            if ($order['fuid3']) {
				$money3 = round($CONFIG['profit']['breaks_profit_rate3'] * $order['deduction'],2);//百分比*分成总金额加上2小数点
   				if ($money3 > 0) {
                    $info3 = '分成被管理员取消，' . $orderTypeName . '订单ID:' . $order_id . ', 分成: ' . round($money3 / 100, 2);
                    $fuser3 = $userModel->find($order['fuid3']);
                    if ($fuser3) {
                        $userModel->addMoney($order['fuid3'], -$money3, $info3);
                        $userProfitModel->save(array('is_separate' => 2), array('where' => array(
							'order_id' => $order_id, 
							'order_type' => $order_type, 
							'user_id' => $order['fuid3']
						)));
                    }
                }
            }
            $model->save(array('order_id' => $order_id, 'is_separate' => 2));
        }
        else {
            $this->baoError('对不起，此订单已处理过！');
        }
        $this->baoSuccess('操作成功1', $url);
    }
}
