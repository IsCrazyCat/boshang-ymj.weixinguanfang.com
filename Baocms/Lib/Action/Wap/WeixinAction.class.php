<?php



class WeixinAction extends CommonAction {

    public function index() {
        $code_id = $this->_get('code_id');
        $t = $this->_get('t');
        $sign = $this->_get('sign');
        if (empty($sign) || empty($t) || empty($code_id)) {
            $this->error('参数错误！');
        }
        if ($sign != md5($code_id . C('AUTH_KEY') . $t)) {
            $this->error('签名不正确');
        }
        if (!$data = D('Tuancode')->find($code_id)) {
            $this->error('套餐码不存在！');
        }
        if ((int) $data['is_used'] == 0 && (int) $data['status'] == 0) {
            $ip = get_client_ip();
            $obj = D('Tuancode');
            $shopmoney = D('Shopmoney');
            if ($obj->save(array('code_id' => $data['code_id'], 'is_used' => 1))) { //这次更新保证了更新的结果集              
                if ((int) $data['price'] == 0) {
                    D('Tuanorder')->save(array(//将原有的订单更新成已经完成
                        'order_id' => $data['order_id'],
                        'status' => 1,
                    ));
                    $obj->save(array('code_id' => array('used_time' => NOW_TIME, 'used_ip' => $ip))); //拆分2次更新是保障并发情况下安全问题
                    $this->assign('waitSecond', 60);
                    $this->success("该套餐码为到店付套餐码，该用户需要额外付消费款给您！", U('index/index'));
                } else {
                    //增加MONEY 的过程 稍后补充
                   $data['settlement_price'] =  D('Quanming')->quanming($data['user_id'],$data['settlement_price'],'tuan'); //扣去全民营销
                    $shopmoney->add(array(
                        'shop_id' => $data['shop_id'],
                        'money' => $data['settlement_price'],
                        'create_ip' => $ip,
                        'create_time' => NOW_TIME,
                        'order_id' => $data['order_id'],
                        'intro' => '套餐消费'.$data['order_id'],
                    ));
                    $shop = D('Shop')->find($data['shop_id']);
                    D('Users')->addMoney($shop['user_id'], $data['settlement_price'], '套餐消费'.$data['order_id']);
                    $obj->save(array('code_id' => array('used_time' => NOW_TIME, 'used_ip' => $ip))); //拆分2次更新是保障并发情况下安全问题
                    D('Users')->gouwu($data['user_id'],$data['price'],'套餐码消费');
                    
                    $this->assign('waitSecond', 60);
                    $this->success("恭喜您成功使用了该消费券！", U('index/index'));
                }
            }
        }

        $this->error('该套餐码无效');
    }

}