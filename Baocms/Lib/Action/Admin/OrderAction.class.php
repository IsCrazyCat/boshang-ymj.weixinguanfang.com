<?php
class OrderAction extends CommonAction{
    public function index(){
        $Order = D('Order');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
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
			$address_ids[$val['address_id']] = $val['address_id'];
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
		$this->assign('addrs', D('Paddress')->itemsByIds($address_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('types', D('Order')->getType());
        $this->assign('goodtypes', D('Ordergoods')->getType());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //等待捡货的订单
    public function wait2(){
        $Order = D('Order');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'status' => 0, 'is_daofu' => 1, 'is_shop' => 0);
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
        $count = $Order->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Order->where($map)->order(array('order_id' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
        $this->assign('page', $show);
        $this->assign('picks', session('order'));
        $this->display();
    }
    public function wait(){
        $Order = D('Order');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('closed' => 0, 'status' => 1, 'is_shop' => 0);
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
        $count = $Order->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Order->where($map)->order(array('order_id' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
        $this->assign('page', $show);
        $this->assign('picks', session('order'));
        $this->display();
    }
    //
    public function pick()
    {
        $order_ids = session('order');
        $orders = $this->_post('order_id', false);
        foreach ($orders as $val) {
            if ($detail = D('Order')->find($val)) {
                if ($detail['status'] == 1 || $detail['staus'] == 0 && $detail['is_daofu'] == 1) {
                    $order_ids[$val] = $val;
                }
            }
        }
        session('order', $order_ids);
        if ($this->_get('wait')) {
            $this->baoSuccess('加入捡货单成功！', U('order/wait2'));
        } else {
            $this->baoSuccess('加入捡货单成功！', U('order/wait'));
        }
    }
    public function clean()
    {
        session('order', null);
        if ($this->_get('wait')) {
            $this->baoSuccess('清空捡货队列成功！', U('order/wait2'));
        } else {
            $this->baoSuccess('清空捡货队列成功！', U('order/wait'));
        }
    }
    //创建捡货单
    public function create()
    {
        $order_ids = session('order');
        $local = array();
        foreach ($order_ids as $val) {
            if ($detail = D('Order')->find($val)) {
                if ($detail['status'] == 1 || $detail['staus'] == 0 && $detail['is_daofu'] == 1) {
                    $local[$val] = $val;
                }
            }
        }
        if (empty($local)) {
            $this->baoError('请选择要加入捡货的订单！');
        }
        $data = array(
			'admin_id' => $this->_admin['admin_id'], 
			'shop_id' => 0, 
			'create_time' => NOW_TIME, 
			'create_ip' => get_client_ip(), 
			'order_ids' => join(',', $local), 
			'name' => '捡货单' . date('Y-m-d H:i:s')
		);
        if ($pick_id = D('Orderpick')->add($data)) {
            D('Order')->save(array('status' => 2), array("where" => array('order_id' => array('IN', $local))));
            D('Ordergoods')->save(array('status' => 1), array("where" => array('order_id' => array('IN', $local))));
            session('order', null);
            $this->baoSuccess('创建捡货单成功！', U('order/pickdetail', array('pick_id' => $pick_id)));
        }
        $this->baoError('创建捡货单失败');
    }

    public function picks()
    {
        $Orderpick = D('Orderpick');
        import('ORG.Util.Page');

        $map = array('shop_id' => 0);
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
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('keyword', $keyword);
        $count = $Orderpick->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Orderpick->where($map)->order('pick_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function pickdetail($pick_id)
    {
        $pick_id = (int) $pick_id;
        $pick = D('Orderpick')->find($pick_id);
        $orderids = explode(',', $pick['order_ids']);
        $Order = D('Order');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('order_id' => array('IN', $orderids));
        $list = $Order->where($map)->order(array('order_id' => 'asc'))->select();
        $user_ids = $order_ids = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
        }
        if (!empty($order_ids)) {
            $goods = D('Ordergoods')->where(array('order_id' => array('IN', $order_ids)))->select();
            $goods_ids = $shop_ids = array();
            foreach ($goods as $val) {
                $goods_ids[$val['goods_id']] = $val['goods_id'];
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $this->assign('goods', $goods);
            $this->assign('products', D('Goods')->itemsByIds($goods_ids));
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('types', D('Order')->getType());
        $this->assign('goodtypes', D('Ordergoods')->getType());
        $this->display();
    }
    public function send($pick_id)
    {
        $pick_id = (int) $pick_id;
        $pick = D('Orderpick')->find($pick_id);
        $orderids = explode(',', $pick['order_ids']);
        $Order = D('Order');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('order_id' => array('IN', $orderids));
        $list = $Order->where($map)->order(array('order_id' => 'asc'))->select();
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
        $this->display();
    }
    //发货管理
    public function delivery(){
        $Order = D('Order');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('closed' => 0, 'status' => 2);
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
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        $count = $Order->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Order->where($map)->order(array('order_id' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
        $this->assign('page', $show);
        // 赋值分页输出
        $this->assign('picks', session('order'));
        $this->display();
        // 输出模板
    }
    //发货管理
    public function distribution(){
        $order_id = (int) $this->_get('order_id');
        if (!empty($order_id)) {
            $order = D('Order')->find($order_id);
            $userobj = D('Users');
            if ($order['status'] == 2) {
                D('Order')->save(array('status' => 8, 'order_id' => $order_id));
                $goods = D('Ordergoods')->where(array('order_id' => $order_id))->select();
                if (!empty($goods)) {
                    D('Ordergoods')->save(array('status' => 8), array('where' => array('order_id' => $order_id)));
                    if ($order['is_daofu'] == 0) {
                        $ip = get_client_ip();
                        foreach ($goods as $val) {
                            if ($val['status'] == 1) {
                                $info = '产品ID' . $val['goods_id'];
                                $tg = $userobj->checkInvite($order['user_id'], $val['total_price']);
                                if ($tg !== false) {
                                    //推广员分层的判断
                                    $userobj->addIntegral($tg['uid'], $tg['integral'], "分享获得积分！");
                                }
                                $money = $val['total_price'];
                                if ($val['tui_uid']) {
                                    //推广员分成
                                    $gooddetail = D('Goods')->find($val['goods_id']);
                                    if (!empty($gooddetail['commission']) && $gooddetail['commission'] < $gooddetail['mall_price'] && $gooddetail['commission'] < $val['total_price']) {
                                        //小于的情况下才能返利不然你懂的
                                        $money -= $gooddetail['commission'];
                                        D('Users')->addMoney($val['tui_uid'], $gooddetail['commission'], '推广佣金');
                                        $info .= '扣除了佣金' . round($gooddetail['commission'] / 100, 2);
                                    }
                                }
                                D('Shopmoney')->add(array(
									'shop_id' => $val['shop_id'], 
									'money' => $money, 
									'create_time' => NOW_TIME, 
									'create_ip' => $ip, 
									'type' => 'goods', 
									'order_id' => $order_id, 
									'intro' => $info
								));
                            }
                        }
                    }
                    $this->baoSuccess('发货成功！', U('order/delivery'));
                }
            }
            $this->baoError('一键发货失败！');
        } else {
            $id = (int) $this->_get('id');
            $goods = D('Ordergoods')->find($id);
            $order = D('Order')->find($goods['order_id']);
            $userobj = D('Users');
            if ($goods['status'] == 1) {
                D('Ordergoods')->save(array('status' => 8, 'id' => $id));
                if ($goods['is_daofu'] == 0) {
                    $info = '产品ID' . $goods['goods_id'];
                    $tg = $userobj->checkInvite($order['user_id'], $goods['total_price']);
                    if ($tg !== false) {
                        $userobj->addIntegral($tg['uid'], $tg['integral'], "分享获得积分！");
                    }
                    $money = $goods['total_price'];
                    if ($goods['tui_uid']) {
                        //推广员分成
                        $gooddetail = D('Goods')->find($goods['goods_id']);
                        if (!empty($gooddetail['commission']) && $gooddetail['commission'] < $gooddetail['mall_price'] && $gooddetail['commission'] < $goods['total_price']) {
                            //小于的情况下才能返利不然你懂的
                            $money -= $gooddetail['commission'];
                            D('Users')->addMoney($goods['tui_uid'], $gooddetail['commission'], '推广佣金');
                            $info .= '扣除了佣金' . round($gooddetail['commission'] / 100, 2);
                        }
                    }
                    D('Shopmoney')->add(array(
						'shop_id' => $goods['shop_id'], 
						'money' => $money, 
						'create_time' => NOW_TIME, 
						'create_ip' => get_client_ip(), 
						'type' => 'goods', 
						'order_id' => $goods['order_id'], 
						'intro' => $info
					));
                }
                $this->baoSuccess('发货成功！', U('order/delivery'));
            }
            $this->baoError('发货失败');
        }
    }

    /**
     * 线下收款成功，线上更新状态并且分成
     */
    public function pay(){
        $order_id=$this->_param('order_id');
        $user_id = $this->_param('user_id');
        if(empty($order_id)){
            $this->baoError('请选择支付的订单！',U('order/index'));
        }
        //修改支付状态
        $logs = D('Paymentlogs')->getLogsByOrderId('goods', $order_id); //写入支付记录
        $need_pay = D('Order')->useIntegral($this->uid, array($order_id));//更新支付结果,这里加了配送费
        if(empty($logs)) {
            $logs = array(
                'type' => 'goods',
                'user_id' => $user_id,
                'order_id' => $order_id,
                'code' => 'offline',  //线下模式
                'need_pay' => $need_pay,
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
                'is_paid' => 0
            );
            $logs['log_id'] = D('Paymentlogs')->add($logs);
        } else {
            $logs['need_pay'] = $need_pay;
            $logs['code'] = 'offline';
            D('Paymentlogs')->save($logs);
        }
        D('Order')->where("order_id={$order_id}")->save(array('need_pay' => $need_pay));
        //添加用户金额变动日志，虽然线下支付的 但是线上记录下
        D('Usermoneylogs')->add(array(
            'user_id' => $user_id,
            'money' => -$need_pay,
            'create_time' => NOW_TIME,
            'create_ip' => get_client_ip(),
            'intro' => '线下支付' . $logs['log_id']
        ));
        //记录日志并分成
        D('Payment')->logsPaid($logs['log_id']);


        $this->baoSuccess('支付成功！',U('order/index'));
    }

    /**
     * 开始返款
     * 逻辑处理暂为：后台点击开始返款，执行一次返款操作，然后执行定时器，根据返还间隔执行下次返款
     */
    public function backmoney(){
        $order_id = $this->_param('order_id');
        $user_id = $this->_param('user_id');
        if(empty($order_id)){
            $this->baoError('请选择要返款的订单！');
        }
        $order = D('Order')->find($order_id);
        if(!empty($order['is_back'])){
            $this->baoSuccess('正在返款中或已返款完成！',U('order/index'));
        }
        //首先修改状态 订单表为返款中
        D('Order')->save(array('order_id'=>$order_id,'is_back'=>1));
        //修改订单下的所有商品 back_status=1返还中
        D('OrderGoods')->where(array('order_id'=>$order_id))->save(array('back_status'=>1,'back_start_time'=>NOW_TIME,'cur_back_count'=>1));
        $ordergoods = D('OrderGoods')->where(array('order_id'=>$order_id))->select();
        $first_back_money = 0;
        foreach ($ordergoods as $key=>$val){
            $first_back_money += $val['back_money'];
            //添加资金记录日志
            $goods = D('Goods')->find($val['goods_id']);
            //这里intro
            D('Usermoneylogs')->add(array(
                'user_id' => $user_id,
                'money' => $val['back_money'],
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
                'intro' => '订单：'.$val['order_id'].'中商品：'.$goods['title'].'第1次返款'
            ));
        }
        //添加用户余额变动日志
        $result = D('Users')->save(array('user_id'=>$user_id,'money'=>array('exp','money+'.$first_back_money)));
        if($result){
            $this->baoSuccess('第一次返款成功！',U('order/index'));
        }else{
            $this->baoError('第一次返款失败',U('order/index'));
        }
    }
}