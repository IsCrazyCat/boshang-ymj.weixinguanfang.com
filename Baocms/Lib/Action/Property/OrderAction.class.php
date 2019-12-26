<?php
class OrderAction extends CommonAction
{
    public function index()
    {
        $orders = D('Communityorder');
        import('ORG.Util.Page');
        $map = array('community_id' => $this->community_id);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $map['order_date'] = array(array('ELT', $end_date), array('EGT', $bg_date));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $this->assign('bg_date', $bg_date);
                $map['order_date'] = array('EGT', $bg_date);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $this->assign('end_date', $end_date);
                $map['order_date'] = array('ELT', $end_date);
            }
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $this->assign('user_id', $user_id);
        }
        $count = $orders->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $orders->order(array('order_date' => 'desc'))->where($map)->select();
        $user_ids = $order_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $products = D('Communityorderproducts')->where(array('order_id' => array('IN', $order_ids)))->select();
        foreach ($list as $k => $val) {
            foreach ($products as $kk => $v) {
                if ($v['order_id'] == $val['order_id']) {
                    $list[$k]['type' . $v['type']] = $v;
                }
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create($user_id)
    {
        $user_id = (int) $user_id;
        $obj = D('Communityorder');
        if (empty($user_id)) {
            $this->error('该用户不存在');
        }
        if (!($detail = D('Communityowner')->where(array('user_id' => $user_id, 'community_id' => $this->community_id))->find())) {
            $this->error('该业主不存在');
        }
        if ($detail['audit'] != 1 || empty($detail['number'])) {
            $this->error('该业主不符合条件');
        }
        $community_id = $this->community_id;
        if ($this->isPost()) {
            $data['order_date'] = htmlspecialchars($_POST['order_date']);
            $data['community_id'] = $this->community_id;
            $data['user_id'] = $user_id;
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $datas = $this->_post('data', false);
            if (!($res = $obj->where(array('user_id' => $user_id, 'order_date' => $data['order_date']))->find())) {
                if ($order_id = $obj->add($data)) {
                    foreach ($datas as $k => $val) {
                        D('Communityorderproducts')->add(array('order_id' => $order_id, 'community_id' => $community_id, 'type' => $k, 'money' => $val['money'] * 100, 'bg_date' => $val['bg_date'], 'end_date' => $val['end_date']));
                    }
                    $this->baoSuccess('添加成功', U('order/index', array('user_id' => $user_id)));
                } else {
                    $this->baoError('添加失败');
                }
            }
            $this->baoError('该账单已存在');
        } else {
            $this->assign('types', D('Communityorder')->getType());
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    public function edit($order_id)
    {
        $order_id = (int) $order_id;
        $obj = D('Communityorder');
        if (empty($order_id)) {
            $this->error('该账单不存在');
        }
        if (!($detail = D('Communityorder')->find($order_id))) {
            $this->error('该账单不存在');
        }
        if ($detail['community_id'] != $this->community_id) {
            $this->error('不能操作其他小区账单');
        }
        $products = D('Communityorderproducts')->where(array('order_id' => $order_id))->select();
        foreach ($products as $k => $val) {
            $products[$val['type']] = $val;
        }
        if ($this->isPost()) {
            $datas = $this->_post('data', false);
            foreach ($datas as $k => $val) {
                if ($val['is_pay'] != 1) {
                    D('Communityorderproducts')->save(array('id' => $val['id'], 'type' => $k, 'money' => $val['money'] * 100, 'bg_date' => $val['bg_date'], 'end_date' => $val['end_date']));
                }
            }
            $this->baoSuccess('修改成功', U('order/index', array('user_id' => $detail['user_id'])));
        } else {
            $this->assign('products', $products);
            $this->assign('types', D('Communityorder')->getType());
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    public function edpay($owner_id, $pay_id)
    {
        $owner_id = (int) $owner_id;
        $pay_id = (int) $pay_id;
        $obj = D('Communitypay');
        if (empty($owner_id)) {
            $this->error('该业主不存在');
        }
        if (!($detail = D('Communityowner')->find($owner_id))) {
            $this->error('该业主不存在');
        }
        if ($detail['community_id'] != $this->community_id) {
            $this->error('不能操作其他小区的业主');
        }
        if ($detail['audit'] != 1 || empty($detail['number'])) {
            $this->error('该业主不符合条件');
        }
        if (empty($pay_id)) {
            $this->error('该账单不存在');
        }
        if (!($result = $obj->find($pay_id))) {
            $this->error('该账单不存在');
        }
        if ($result['owner_id'] != $owner_id) {
            $this->error('该账单不存在');
        }
        if ($this->isPost()) {
            $data['electric'] = intval($_POST['electric'] * 100);
            $data['water'] = intval($_POST['water'] * 100);
            $data['gas'] = intval($_POST['gas'] * 100);
            $data['property'] = intval($_POST['property'] * 100);
            $data['parking'] = intval($_POST['parking'] * 100);
            $data['pay_id'] = $pay_id;
            if (false !== $obj->save($data)) {
                $this->baoSuccess('编辑成功', U('owner/pays', array('owner_id' => $owner_id)));
            } else {
                $this->baoError('编辑失败');
            }
        } else {
            $this->assign('detail', $detail);
            $this->assign('result', $result);
            $this->display();
        }
    }
    public function is_pay($id = 0)
    {
        $id = (int) $id;
        $obj = D('Communityorderproducts');
        if (empty($id)) {
            $this->error('该账单不存在1');
        }
        if (!($detail = D('Communityorderproducts')->find($id))) {
            $this->error('该账单不存在2');
        }
        $order_id = $detail['order_id'];
        $Communityorder = D('Communityorder')->where(array('order_id' => $order_id))->find();
        $community_id = $Communityorder['community_id'];
        if ($community_id != $this->community_id) {
            $this->error('不能操作其他小区账单');
        }
        $total = 1;
        $user_id = $Communityorder['user_id'];
        $obj->save(array('id' => $id, 'is_pay' => 1));
        D('Users')->addMoney($user_id, -$total, '物业设置为已缴费，未扣费');
        D('Communityorderlogs')->add(array(
			'user_id' => $Communityorder['user_id'], 
			'community_id' => $community_id, 
			'money' => 0, 
			'type' => $detail['type'], 
			'create_time' => NOW_TIME, 
			'create_ip' => get_client_ip())
		);
        $this->error('您已成功修改成已缴费状态', U('order/edit', array('order_id' => $order_id)));
    }
}