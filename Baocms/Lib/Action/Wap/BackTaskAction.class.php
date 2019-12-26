<?php

/**
 * Class AtuoBackTask
 * 自动返款任务类
 */
class BackTaskAction extends CommonAction {

    /**
     * 自动返款
     */
    public function backtask(){
        //查询需要返款的数据， 订单表中is_back状态为1 返款中的订单 中的 订单商品表中 back_status 为1 的数据
        $back_orders = D('Order')->where(array('is_back'=>1))->select();
        foreach ($back_orders as $key=>$order){
            $user_id = $order['user_id'];
            //获取订单下返款中的商品
            $back_goods = D('OrderGoods')->where(array('order_id'=>$order['order_id'],'back_status'=>1))->select();
            if(empty($back_goods)){
                D('Order')->save(array('order_id'=>$order['order_id'],'status'=>8,'is_back'=>2));
            }else{
                $back_money = 0;
                foreach ($back_goods as $gk=>$good){
                    //上次返还的期数 是否等于总期数 等于则修改状态 返还结束
                    if($good['cur_back_count'] == $good['back_count']){
                        D('OrderGoods')->save(array('id'=>$good['id'],'back_status'=>2,'back_end_time'=>NOW_TIME));
                    }else if($good['cur_back_count'] < $good['back_count']){
                        //进行返还
                        $back_money += $good['back_money'];
                        //添加资金记录日志
                        $goods = D('Goods')->find($good['goods_id']);
                        ////添加用户余额变动日志 这里intro
                        D('Usermoneylogs')->add(array(
                            'user_id' => $user_id,
                            'money' => $good['back_money'],
                            'create_time' => NOW_TIME,
                            'create_ip' => get_client_ip(),
                            'intro' => '订单：'.$good['order_id'].'中商品：'.$goods['title'].'第'.($good['cur_back_count']+1).'次返款'
                        ));
                        if(($good['cur_back_count']+1) == $good['back_count']){
                            D('OrderGoods')->save(array('id'=>$good['id'],'back_status'=>2,'back_end_time'=>NOW_TIME,'cur_back_count'=>$good['back_count']));
                        }
                    }
                }
                //更新用户余额
                $result = D('Users')->save(array('user_id'=>$user_id,'money'=>array('exp','money+'.$back_money)));
                //检查该订单下是否都返还完毕
                $back_goods = D('OrderGoods')->where(array('order_id'=>$order['order_id'],'back_status'=>1))->select();
                if(empty($back_goods)) {
                    D('Order')->save(array('order_id' => $order['order_id'], 'status' => 8, 'is_back' => 2));
                }
            }
        }
    }
}