<?php
class OrderAction extends CommonAction{
    public function index(){
        $Tuanorder = D('Tuanorder');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'closed' => 0);
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
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
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
        $count = $Tuanorder->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Tuanorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $tuan_ids = $order_ids = array();
        foreach ($list as $k => $val) {
            if (!empty($val['shop_id'])) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $order_ids[$val['order_id']] = $val['order_id'];
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('tuan', D('Tuan')->itemsByIds($tuan_ids));
        $this->assign('dianping', D('Tuandianping')->itemsByIds($order_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //PC团购详情
    public function detail($order_id)
    {
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D('Tuanorder')->find($order_id))) {
            $this->error('该订单不存在');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error('请不要操作他人的订单');
        }
        if (!($dianping = D('Tuandianping')->where(array('order_id' => $order_id, 'user_id' => $this->uid))->find())) {
            $detail['dianping'] = 0;
        } else {
            $detail['dianping'] = 1;
        }
        $this->assign('tuans', D('Tuan')->find($detail['tuan_id']));
        $this->assign('detail', $detail);
        $this->display();
    }
    public function noindex()
    {
        $Tuanorder = D('Tuanorder');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'closed' => 0);
        $lists = $Tuanorder->where($map)->order(array('order_id' => 'desc'))->select();
        $dianping = D('Tuandianping')->where(array('user_id' => $this->uid))->select();
        $orders = array();
        foreach ($dianping as $key => $v) {
            $orders[] = $v['order_id'];
        }
        foreach ($lists as $kk => $vv) {
            if (in_array($vv['order_id'], $orders)) {
                unset($lists[$kk]);
            }
        }
        $count = count($lists);
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);
        $shop_ids = $tuan_ids = $order_ids = array();
        foreach ($list as $k => $val) {
            if (!empty($val['shop_id'])) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $order_ids[$val['order_id']] = $val['order_id'];
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('tuan', D('Tuan')->itemsByIds($tuan_ids));
        $this->assign('dianping', D('Tuandianping')->itemsByIds($order_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function delete($order_id){
        $order_id = I('order_id', 0, 'trim,intval');
        $obj = D('Tuanorder');
        if (!($detail = D('Tuanorder')->find($order_id))) {
            $this->baoError('套餐不存在', U('order/index'));
        }
        if ($detail['status'] == -1) {
            $Tuancode = D('Tuancode');
            $tuan_code_is_used = $Tuancode->where(array('order_id' => $order_id, 'status' => 0, 'is_used' => 1))->select();
            $maps['order_id'] = array('eq', $order_id);
            $maps['status'] = array('gt', 0);
            $tuan_code_status = $Tuancode->where($maps)->select();
            if (!empty($tuan_code_is_used)) {
                $this->baoError('已有套餐劵验证不能取消订单');
            } elseif (!empty($tuan_code_status)) {
                $this->baoError('已有套餐劵申请退款不行执行此操作');
            } else {
                $tuan_code = $Tuancode->where(array('order_id' => $order_id, 'status' => 0, 'is_used' => 0))->select();
                foreach ($tuan_code as $k => $value) {
                    $Tuancode->save(array('code_id' => $value['code_id'], 'closed' => 1));
                }
                $obj->save(array('order_id' => $order_id, 'closed' => 1));
                D('Users')->addIntegral($detail['user_id'], $detail['use_integral'], '取消套餐订单' . $detail['order_id'] . '积分退还');
                //返积分
                $this->baoSuccess('取消订单成功!', U('tuan/index'));
            }
        } elseif ($detail['status'] != 0) {
            $this->baoSuccess('状态不正确', U('order/index'));
        } elseif ($detial['closed'] == 1) {
            $this->baoSuccess('套餐已关闭', U('order/index'));
        } elseif ($detail['user_id'] != $this->uid) {
            $this->baoSuccess('不能操作别人的套餐', U('order/index'));
        } else {
            if ($obj->save(array('order_id' => $order_id, 'closed' => 1))) {
                D('Users')->addIntegral($detail['user_id'], $detail['use_integral'], '取消套餐订单' . $detail['order_id'] . '积分退还');
                //返积分
                $this->baoSuccess('取消订单成功!', U('order/index'));
            } else {
                $this->baoError('操作失败');
            }
        }
    }
    //我的订单
    public function goods(){
        $Order = D('Order');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('closed' => 0, 'user_id' => $this->uid);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
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
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
        $count = $Order->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Order->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $shop_ids = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if (!empty($shop_ids)) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
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
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('types', D('Order')->getType());
        $this->assign('goodtypes', D('Ordergoods')->getType());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function refund(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Order = D('Order');
        $goods_order = $Order->where('order_id =' . $order_id)->find();
        $var = D('Order')->order_delivery($order_id, $type = 0);
        if (empty($var)) {
            $this->baoError('配送状态错误错误！');
        }
        if (!$goods_order) {
            $this->baoError('错误！');
        } else {
            if ($goods_order['is_daofu'] == 1) {
                if ($goods_order['status'] != 0) {
                    $this->baoError('订单状态有误');
                }
            } else {
                if ($goods_order['status'] != 1) {
                    $this->baoError('当前订单状态不正确');
                }
            }
            if ($goods_order['user_id'] != $this->uid) {
                $this->baoError('请不要操作他人的订单');
            }
            $goods_order = $Order->where('order_id =' . $order_id)->setField('status', 2);
			D('Weixintmpl')->weixin_user_refund_shop($order_id,1);//商城申请退款，传订单ID跟类型
            $this->baoSuccess('申请退款成功！', U('order/goods'));
        }
    }
    public function cancel_refund(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Order = D('Order');
        $goods_order = $Order->where('order_id =' . $order_id)->find();
        $var = D('Order')->order_delivery($order_id, $type = 0);
        if (empty($var)) {
            $this->baoError('配送状态错误错误！');
        }
        if (!$goods_order) {
            $this->baoError('错误！');
        } else {
            if ($goods_order['user_id'] != $this->uid) {
                $this->baoError('请不要操作他人的订单');
            }
            if ($goods_order['is_daofu'] == 1) {
                $goods_order = $Order->where('order_id =' . $order_id)->setField('status', 0);
            } else {
                $goods_order = $Order->where('order_id =' . $order_id)->setField('status', 1);
            }
            $this->baoSuccess('取消退款成功！', U('order/goods'));
        }
    }
    public function goodsshou($order_id = 0){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Order');
            if (!($detial = $obj->find($order_id))) {
                $this->baoError('该订单不存在');
            }
            if ($detial['user_id'] != $this->uid) {
                $this->baoError('请不要操作他人的订单');
            }
            //检测配送状态
            $shop = D('Shop')->find($detial['shop_id']);
            if ($shop['is_pei'] != 1) {
                $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 0))->find();
                if ($DeliveryOrder['status'] != 8) {
                    $this->baoError('配送员还未完成订单');
                }
            }
            if ($detial['is_daofu'] == 1) {
                $into = '货到付款确认收货成功';
            } else {
                if ($detial['status'] != 2) {
                    $this->baoError('该订单暂时不能确定收货');
                }
                $into = '确认收货成功';
            }
            if ($obj->save(array('order_id' => $order_id, 'status' => 3))) {
                D('Order')->overOrder($order_id);
                //确认到账入口
                $this->baoSuccess($into, U('order/goods'));
            } else {
                $this->baoError('操作失败');
            }
        } else {
            $this->baoError('请选择要确认收货的订单');
        }
    }
    //PC取消订单重做
    public function goodsdel($order_id = 0){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Order');
            if (!($detial = $obj->find($order_id))) {
                $this->baoError('该订单不存在');
            }
            if ($detial['user_id'] != $this->uid) {
                $this->baoError('请不要操作他人的订单');
            }
            //检测配送状态
            $shop = D('Shop')->find($detial['shop_id']);
            if ($shop['is_pei'] == 0) {
                $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 0))->find();
                if ($DeliveryOrder['status'] == 2 || $DeliveryOrder['status'] == 8) {
                    $this->baoError('配送员都接单了无法取消订单');
                } else {
                    D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 0))->setField('closed', 1);
                    //没接单就关闭配送
                }
            }
            if ($detial['is_daofu'] == 1) {
                $into = '到付订单取消成功';
            } else {
                $into = '订单取消成功';
                if ($detial['status'] != 0) {
                    $this->baoError('该订单暂时不能取消');
                }
            }
            if ($obj->save(array('order_id' => $order_id, 'closed' => 1))) {
                $obj->del_order_goods_closed($order_id);
                //更新状态
                $obj->del_goods_num($order_id);//取消后加库存
                if ($detail['use_integral']) {
                    D('Users')->addIntegral($detail['user_id'], $detail['use_integral'], '取消商城购物，订单号：' . $detail['order_id'] . '积分退还');
                }
				D('Weixintmpl')->weixin_delete_order_shop($order_id,2);//商城取消订单，传订单ID跟类型
                $this->baoSuccess($into, U('order/goods'));
            } else {
                $this->baoError('操作失败');
            }
        } else {
            $this->baoError('请选择要取消的订单');
        }
    }
    //PC商城详情
    public function details($order_id){
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D('Order')->find($order_id))) {
            $this->error('该订单不存在');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error('请不要查看他人的订单');
        }
        $order_goods = D('Ordergoods')->where(array('order_id' => $order_id))->select();
        $goods_ids = array();
        foreach ($order_goods as $k => $val) {
            $goods_ids[$val['goods_id']] = $val['goods_id'];
        }
        if (!empty($goods_ids)) {
            $this->assign('goods', D('Goods')->itemsByIds($goods_ids));
        }
        $this->assign('ordergoods', $order_goods);
        $this->assign('addr', D('Useraddr')->find($detail['addr_id']));
        $this->assign('types', D('Order')->getType());
        $this->assign('goodtypes', D('Order')->getType());
        $this->assign('detail', $detail);
        $this->display();
    }
    public function dianping($order_id) {
        $order_id = (int) $order_id;
        if (!($detail = D('Order')->find($order_id))) {
            $this->baoError('没有该商品');
        } else {
            if ($detail['user_id'] != $this->uid) {
                $this->baoError('不要评价别人的商品');
                die;
            }
        }
        if (D('Goodsdianping')->check($order_id, $this->uid)) {
            $this->baoError('已经评价过了');
        }
        $goodss = D('Ordergoods')->where('order_id =' . $detail['order_id'])->find();
        $goods_id = $goodss['goods_id'];
        if ($this->_Post()) {
            $data = $this->checkFields($this->_post('data', false), array('score', 'cost', 'contents'));
            $data['user_id'] = $this->uid;
            $data['shop_id'] = $detail['shop_id'];
            $data['goods_id'] = $goods_id;
            $data['order_id'] = $order_id;
            $data['score'] = (int) $data['score'];
            if (empty($data['score'])) {
                $this->baoError('评分不能为空');
            }
            if ($data['score'] > 5 || $data['score'] < 1) {
                $this->baoError('评分为1-5之间的数字');
            }
            $data['cost'] = (int) $data['cost'];
            $data['contents'] = htmlspecialchars($data['contents']);
            if (empty($data['contents'])) {
                $this->baoError('评价内容不能为空');
            }
            if ($words = D('Sensitive')->checkWords($data['contents'])) {
                $this->baoError('评价内容含有敏感词：' . $words);
            }
            $data_mall_dianping = $this->_CONFIG['mobile']['data_mall_dianping'];
            $data['show_date'] = date('Y-m-d', NOW_TIME + $data_mall_dianping * 86400);//15天生效
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            if (D('Goodsdianping')->add($data)) {
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val)) {
                        $local[] = $val;
                    }
                }
                if (!empty($local)) {
                    D('Goodsdianpingpics')->upload($order_id, $local);
                }
                D('Order')->save(array('order_id' => $order_id, 'is_dianping' => 1));
                D('Users')->prestige($this->uid, 'dianping');
                D('Users')->updateCount($this->uid, 'ping_num');
                $this->baoSuccess('恭喜您点评商品成功!', U('members/order/goods'));
            }
            $this->baoError('点评失败！');
        } else {
            $goodsdetails = D('Goods')->where('goods_id =' . $goods_id)->find();
            $this->assign('goodsdetails', $goodsdetails);
            $this->assign('order_id', $order_id);
            $this->display();
        }
    }
}