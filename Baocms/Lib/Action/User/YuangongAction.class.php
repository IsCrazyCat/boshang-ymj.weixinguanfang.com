<?php

class YuangongAction extends CommonAction {

	public function ygtuan() {
		if (empty($this->uid)) {
			$this->error('登录状态失效!', U('Wap/passport/login'));
		}
		$user_id = D('Users')->where(array('user_id'=>$this->uid))->getField('user_id');
		//$worker = D('Shopworker')->find($this->uid);
		$worker = D('Shopworker')->where(array('user_id'=>$user_id))->find();
        $scan_shop_id = 0;
        if(empty($worker)){
            if(!$scan_shop = D('Shop')->where(array('user_id'=>$user_id))->find()){
                $this->error('您不属于任何一个店铺的管理人或授权员工，无权进行管理！', U('index/index'));
            }
            $scan_shop_id = $scan_shop['shop_id'];
        }else{
            if(empty($worker['status']) || $worker['status'] !=1 ){
                $this->error('您的员工信息还处于待通过状态，无权进行操作！', U('information/worker',array('worker_id'=>$worker['worker_id'])));
            }
            $scan_shop_id = $worker['shop_id'];
        }

        if ($this->isPost()) {
            $code = $this->_post('code', false);
            if (empty($code)) {
                $this->fengmiMsg('请输入套餐码！');
            }

            $obj = D('Tuancode');
            $shopmoney = D('Shopmoney');
            $return = array();
            $ip = get_client_ip();
            if (count($code) > 10) {
                $this->fengmiMsg('一次最多验证10条套餐码！');
            }
            $userobj = D('Users');
            foreach ($code as $key => $var) {
                $var = trim(htmlspecialchars($var));

                if (!empty($var)) {
                    $data = $obj->find(array('where' => array('code' => $var)));
                    $data['shop_id']=$scan_shop_id;
                    // echo '<script>alert('.$data['status'].'); < /script>';die;
                   
                    if (!empty($data) && $data['shop_id'] == $worker && (int) $data['is_used'] == 0 && (int) $data['status'] == 0) {
                        if ($obj->save(array('code_id' => $data['code_id'], 'is_used' => 1))) { //这次更新保证了更新的结果集              
                            //增加MONEY 的过程 稍后补充
                            if (!empty($data['price'])) {
                                $data['intro'] = '套餐消费' . $data['order_id'];



                                $shopmoney->add(array(
                                    'shop_id' => $data['shop_id'],
                                    'money' => $data['settlement_price'],
                                    'create_ip' => $ip,
                                    'create_time' => NOW_TIME,
                                    'order_id' => $data['order_id'],
                                    'intro' => $data['intro'],
                                ));
                                $shop = D('Shop')->find($data['shop_id']);
                                //D('Users')->addMoney($shop['user_id'], $data['settlement_price'], $data['intro']);
                                $return[$var] = $var;

                                $obj->save(array('code_id' => array('used_time' => NOW_TIME, 'used_ip' => $ip))); //拆分2次更新是保障并发情况下安全问题
                                echo '<script>parent.used(' . $key . ',"√验证成功",1);</script>';
                            } else {
                                echo '<script>parent.used(' . $key . ',"√到店付套餐码验证成功",2);</script>';
                            }
                            //这样性能有点不好
                            $order = D('Tuanorder')->find($data['order_id']);
                            $tuan = D('Tuan')->find($data['tuan_id']);
                            $integral = (int) ($order['total_price'] / 100 / $order['num']);
                            D('Users')->addIntegral($data['user_id'], $integral, '套餐' . $tuan['title'] . ';订单' . $order['order_id']);
                            //可以优化的 不过最多限制了10条！
                        }
                    } else {
                        echo '<script>parent.used(' . $key . ',"X该套餐码无效",3);</script>';
                    }
                }
            }
        } else {
            $this->display();
        }
	}
	
	


}
