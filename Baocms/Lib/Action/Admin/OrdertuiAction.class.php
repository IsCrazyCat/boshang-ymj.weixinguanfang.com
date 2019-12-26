<?php
class OrdertuiAction extends CommonAction
{
    public function index()
    {
        $Order = D('Order');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('closed' => 0, 'is_shop' => 0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
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
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        // var_dump($map);die();
        $count = $Order->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 10);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Order->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $shop_ids = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if (!empty($order_ids)) {
            $goods = D('Ordergoods')->where(array('order_id' => array('IN', $order_ids)))->select();
            $goods_ids = array();
            foreach ($goods as $val) {
                $goods_ids[$val['goods_id']] = $val['goods_id'];
            }
            $this->assign('goods', $goods);
            $this->assign('products', D('Goods')->itemsByIds($goods_ids));
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('types', D('Order')->getType());
        $this->assign('goodtypes', D('Ordergoods')->getType());
        $this->assign('list', $list);
        // 赋值数据集
        //p($list);die;
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function tui($order_id = 0)
    {
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $detail = D('Order')->find($order_id);
            if ($detail['status'] == 4) {
                if (D('Order')->save(array('order_id' => $order_id, 'status' => 5))) {
                    //将内容变成
                    $obj = D('Users');
                    if ($detail['total_price'] > 0) {
                        $obj->addMoney($detail['user_id'], $detail['total_price'], '商城退款');
                    }
                    if ($detail['use_integral'] > 0) {
                        $obj->addIntegral($detail['user_id'], $detail['use_integral'], '取消订单' . $detail['order_id'] . '积分退还');
                    }
                }
            }
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                $obj = D('Users');
                $Order = D('Order');
                foreach ($order_id as $id) {
                    $detail = $Order->find($id);
                    if ($detail['status'] == 4) {
                        if (D('Order')->save(array('order_id' => $order_id, 'status' => 5))) {
                            //将内容变成
                            if ($detail['total_price'] > 0) {
                                $obj->addMoney($detail['user_id'], $detail['total_price'], '商城退款');
                            }
                            if ($detail['use_integral'] > 0) {
                                $obj->addIntegral($detail['user_id'], $detail['use_integral'], '取消订单' . $detail['order_id'] . '积分退还');
                            }
                        }
                    }
                }
            }
        }
        $this->baoSuccess('退款成功！', U('ordertui/index'));
    }
}