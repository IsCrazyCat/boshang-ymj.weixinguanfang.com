<?php
class EleorderAction extends CommonAction{
    public function index(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
    }
    public function loading(){
        $s = I('aready', '', 'trim,intval');
        $Eleorder = D('Eleorder');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'closed' => 0);
        if ($s == 0 || $s == 1 || $s == 2 || $s == 3 || $s == 4 || $s == 8) {
            $map['status'] = $s;
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
        $count = $Eleorder->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Eleorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $addr_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('shopss', D('Shop')->itemsByIds($shop_ids));
        if (!empty($order_ids)) {
            $products = D('Eleorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
            $product_ids = $shop_ids = array();
            foreach ($products as $val) {
                $product_ids[$val['product_id']] = $val['product_id'];
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $this->assign('products', $products);
            $this->assign('eleproducts', D('Eleproduct')->itemsByIds($product_ids));
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('cfg', D('Eleorder')->getCfg());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function detail($order_id){
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D('Eleorder')->find($order_id))) {
            $this->error('该订单不存在');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error('请不要操作他人的订单');
        }
        $ele_products = D('Eleorderproduct')->where(array('order_id' => $order_id))->select();
        $product_ids = array();
        foreach ($ele_products as $k => $val) {
            $product_ids[$val['product_id']] = $val['product_id'];
        }
        if (!empty($product_ids)) {
            $this->assign('products', D('Eleproduct')->itemsByIds($product_ids));
        }
        $this->assign('eleproducts', $ele_products);
        $this->assign('addr', D('Useraddr')->find($detail['addr_id']));
        $this->assign('cfg', D('Eleorder')->getCfg());
        $this->assign('detail', $detail);
        $this->display();
    }
    //确认订单
    public function yes($order_id = 0){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            if (!($detial = D('Eleorder')->find($order_id))) {
                $this->fengmiMsg('您确认收货的订单不存在');
            }
            if ($detial['user_id'] != $this->uid) {
                $this->fengmiMsg('请不要操作别人的订单');
            }
            $shop = D('Shop')->find($detial['shop_id']);
            if ($shop['is_pei'] == 0) {
                $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 1))->find();
                if ($DeliveryOrder['status'] == 2) {
                    $this->fengmiMsg('配送员还未完成订单');
                }
            } else {
                //不走配送
                if ($detial['status'] != 2) {
                    $this->fengmiMsg('当前状态不能确认收货');
                }
            }
            $obj = D('Eleorder');
            D('Eleorder')->overOrder($order_id);       //确认资金到账
            $obj->save(array('order_id' => $order_id, 'status' => 8));//更改为已完成
            $this->fengmiMsg('确认收货成功！', U('eleorder/index', array('s' => 1)));
        } else {
            $this->fengmiMsg('请选择要确认收货的订单');
        }
    }
    public function del() {
        $order_id = I('order_id', 0, 'trim,intval');
        $o = D('EleOrder');
        $f = $o->where('order_id =' . $order_id)->find();
        $shop = D('Shop')->find($f['shop_id']);
        if ($shop['is_pei'] == 0) {
            $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 1))->find();
            if ($DeliveryOrder['status'] == 2) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '配送员已经抢单，无法删除'));
            } elseif ($DeliveryOrder['status'] == 8) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '配送员都已经确认了，无法删除'));
            }
        }
        if (!$f) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '错误'));
        } else {
            if ($f['user_id'] != $this->uid) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '非法操作用'));
            }
            if ($detial['status'] != 0 && $detial['status'] != 8 && $detial['status'] != 4) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '当前状态不允许取消订单'));
            }
            $r = $o->where('order_id =' . $order_id)->setField('closed', 1);
            $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 1))->setField('closed', 1);
            D('Weixintmpl')->weixin_delete_order_shop($order_id,1);//外卖取消订单，传订单ID跟类型
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除订单成功', U('eleorder/index')));
        }
    }
    public function eletui(){
        $order_id = I('order_id', 0, 'trim,intval');
        $o = D('EleOrder');
        $f = $o->where('order_id =' . $order_id)->find();
        $shop = D('Shop')->find($f['shop_id']);
        if ($shop['is_pei'] != 1) {
            $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 1))->find();
            if ($DeliveryOrder['status'] != 1) {
                $this->fengmiMsg('亲，当前状态不能退款啦');
            }
        }
        $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 1))->setField('closed', 1);
        if (!$f) {
            $this->fengmiMsg('错误！');
        } else {
            if ($f['user_id'] != $this->uid) {
                $this->fengmiMsg('请不要操作他人的订单');
            }
            if ($f['status'] != 1) {
                $this->fengmiMsg('非法操作');
            }
            $r = $o->where('order_id =' . $order_id)->setField('status', 3);
			D('Weixintmpl')->weixin_user_refund_shop($order_id,1);//外卖申请退款，传订单ID跟类型
            $this->fengmiMsg('申请退款成功！', U('eleorder/index'));
        }
    }
    public function qx(){
        $order_id = I('order_id', 0, 'trim,intval');
        $o = D('EleOrder');
        $f = $o->where('order_id =' . $order_id)->find();
        $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 1))->setField('closed', 0);
        if (!$f) {
            $this->fengmiMsg('错误！');
        } else {
            if ($f['user_id'] != $this->uid) {
                $this->fengmiMsg('请不要操作他人的订单');
            }
            $r = $o->where('order_id =' . $order_id)->setField('status', 1);
            $this->fengmiMsg('取消退款成功！', U('eleorder/index'));
        }
    }
    public function dianping($order_id){
        $order_id = (int) $order_id;
        if (!($detail = D("Eleorder")->find($order_id))) {
            $this->error("没有该订单");
        } else {
            if ($detail['user_id'] != $this->uid) {
                $this->error("不要评价别人的订餐订单");
                exit;
            }
        }
        if (D("Eledianping")->check($order_id, $this->uid)) {
            $this->error("已经评价过了");
        }
        if ($this->_Post()) {
            $data = $this->checkFields($this->_post('data', FALSE), array('score', 'speed', 'cost', 'contents'));
            $data['user_id'] = $this->uid;
            $data['shop_id'] = $detail['shop_id'];
            $data['order_id'] = $order_id;
            $data['score'] = (int) $data['score'];
            if (empty($data['score'])) {
                $this->fengmiMsg("评分不能为空");
            }
            if (5 < $data['score'] || $data['score'] < 1) {
                $this->fengmiMsg("评分为1-5之间的数字");
            }
            $data['cost'] = (int) $data['cost'];
            if (empty($data['cost'])) {
                $this->fengmiMsg("平均消费金额不能为空");
            }
            $data['speed'] = (int) $data['speed'];
            if (empty($data['speed'])) {
                $this->fengmiMsg("送餐时间不能为空");
            }
            $data['contents'] = htmlspecialchars($data['contents']);
            if (empty($data['contents'])) {
                $this->fengmiMsg("评价内容不能为空");
            }
            if ($words = D("Sensitive")->checkWords($data['contents'])) {
                $this->fengmiMsg("评价内容含有敏感词：" . $words);
            }
            $data_waimai_dianping = $this->_CONFIG['mobile']['data_waimai_dianping'];
            $data['show_date'] = date('Y-m-d', NOW_TIME + $data_waimai_dianping * 86400);
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            if (D("Eledianping")->add($data)) {
                $photos = $this->_post("photos", FALSE);
                $local = array();
                foreach ($photos as $val) {
                    if (isimage($val)) {
                        $local[] = $val;
                    }
                }
                if (!empty($local)) {
                    D("Eledianpingpics")->upload($order_id, $local);
                }
                D("Users")->updateCount($this->uid, "ping_num");
                D("Eleorder")->updateCount($order_id, "is_dianping");
                $this->fengmiMsg("恭喜您点评成功!", u("eleorder/index"));
            }
            $this->fengmiMsg("点评失败！");
        } else {
            $this->assign("detail", $detail);
            $details = D("Shop")->find($detail['shop_id']);
            $this->assign("details", $details);
            $this->assign("order_id", $order_id);
            $this->display();
        }
    }
}