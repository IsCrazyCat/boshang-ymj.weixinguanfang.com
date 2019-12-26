<?php
class EleorderAction extends CommonAction
{
    public function index()
    {
        $mapss = array('city_id' => $this->city_id);
        //查询城市ID为当前登录账户的ID
        $shop_city = D('Shop')->where($mapss)->order(array('shop_id' => 'desc'))->select();
        //查询所在城市的商家
        foreach ($shop_city as $val) {
            $cityids[$val['shop_id']] = $val['shop_id'];
            //对比shop_id
        }
        $maps['shop_id'] = array('in', $cityids);
        //取得当前商家ID，给下面的maps查询
        $Eleorder = D('Eleorder');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('closed' => 0);
        if ($order_id = (int) $this->_param('order_id')) {
            $maps['order_id'] = $order_id;
            $this->assign('order_id', $order_id);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $maps['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $maps['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $maps['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
        $count = $Eleorder->where($maps)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Eleorder->where($maps)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $addr_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if (!empty($order_ids)) {
            $products = D('Eleorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
            $product_ids = array();
            foreach ($products as $val) {
                $product_ids[$val['product_id']] = $val['product_id'];
            }
            $this->assign('products', $products);
            $this->assign('eleproducts', D('Eleproduct')->itemsByIds($product_ids));
        }
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('cfg', D('Eleorder')->getCfg());
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function delete($order_id = 0)
    {
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('Eleorder')->find($order_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('eleorder/index'));
            }
            $obj = D('Eleorder');
            $obj->save(array('order_id' => $order_id, 'closed' => 1));
            $this->baoSuccess('取消订单成功！', U('eleorder/index'));
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                $obj = D('Eleorder');
                foreach ($order_id as $id) {
                    $detail = $obj->find($id);
                    if ($detail['status'] >= 1) {
                        $obj->save(array('order_id' => $id, 'closed' => 1));
                    }
                }
                $this->baoSuccess('取消订单成功！', U('eleorder/index'));
            }
            $this->baoError('请选择要取消的订单');
        }
    }
    public function tui($order_id = 0)
    {
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('Eleorder')->find($order_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('eleorder/index'));
            }
            $detail = D('Eleorder')->find($order_id);
            if ($detail['status'] == 3) {
                if (D('Eleorder')->save(array('order_id' => $order_id, 'status' => 4))) {
                    //将内容变成
                    $obj = D('Users');
                    if ($detail['need_pay'] > 0) {
                        $obj->addMoney($detail['user_id'], $detail['need_pay'], '订餐退款');
                    }
                }
            }
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                $obj = D('Users');
                $eleorder = D('Eleorder');
                foreach ($order_id as $id) {
                    $detail = $eleorder->find($id);
                    if ($detail['status'] == 3) {
                        if (D('Eleorder')->save(array('order_id' => $order_id, 'status' => 4))) {
                            //将内容变成
                            if ($detail['need_pay'] > 0) {
                                $obj->addMoney($detail['user_id'], $detail['need_pay'], '订餐退款');
                            }
                        }
                    }
                }
            }
        }
        $this->baoSuccess('退款成功！', U('eleorder/index'));
    }
}