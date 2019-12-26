<?php
class MartAction extends CommonAction{
    private $create_fields = array('title', 'photo', 'cate_id', 'price', 'shopcate_id', 'mall_price', 'commission', 'instructions', 'details', 'end_date');
    private $edit_fields = array('title', 'photo', 'cate_id', 'price', 'shopcate_id', 'mall_price', 'commission', 'instructions', 'details', 'end_date');
    public function _initialize(){
        parent::_initialize();
        if ($this->_CONFIG['operation']['mall'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $this->autocates = D('Goodsshopcate')->where(array('shop_id' => $this->shop_id))->select();
        $this->assign('autocates', $this->autocates);
    }
    public function index(){
//        $this->check_weidian();
        $Goods = D('Goods');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'shop_id' => $this->shop_id, 'is_mall' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Goodscate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = $Goods->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Goods->where($map)->order(array('goods_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val = $Goods->_format($val);
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('cates', D('Goodscate')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function goodscate(){
//        $this->check_weidian();
        $autocates = D('Goodsshopcate')->order(array('orderby' => 'asc'))->where(array('shop_id' => $this->shop_id))->select();
        $this->assign('autocates', $autocates);
        $this->display();
    }
    public function all(){
//        $this->check_weidian();
        $SHOP = D('Shop')->where(array('shop_id' => $this->shop_id))->find();
        if ($SHOP['is_pei'] != 0) {
            $this->error('您无限查看');
        }
        $Order = D('Order');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'shop_id' => $this->shop_id);
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
		
		if (isset($_GET['st'])){
            $st = (int) $this->_param('st');
            if (!empty($st)) {
                $map['status'] = $st;
            }else{
				$map['status'] = 0;
				$map['is_daofu'] = 0;
			}
            $this->assign('st', $st);
        }
		
        $count = $Order->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Order->where($map)->order(array('create_time' => 'DESC'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
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
    public function order(){
//        $this->check_weidian();
        if (empty($this->shop['is_pei'])) {
            $this->error('您签订的是由配送员配送！您管理不了订单！');
        }
        $Order = D('Order');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'shop_id' => $this->shop_id);
        if (strtotime($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && strtotime($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            if (!empty($bg_time) && !empty($end_date)) {
                $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            }
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                if (!empty($bg_time)) {
                    $map['create_time'] = array('EGT', $bg_time);
                }
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                if (!empty($end_time)) {
                    $map['create_time'] = array('ELT', $end_time);
                }
                $this->assign('end_date', $end_date);
            }
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $keyword = intval($keyword);
            if (!empty($keyword)) {
                $map['order_id'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }
        }
			  
        if (isset($_GET['st'])){
            $st = (int) $this->_param('st');
            if (!empty($st)) {
                $map['status'] = $st;
            }else{
				$map['status'] = 0;
				$map['is_daofu'] = 0;
			}
            $this->assign('st', $st);
        }

        $count = $Order->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Order->where($map)->order(array('create_time' => 'DESC'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
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
        $this->assign('citys', D('City')->fetchAll());
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
//        $this->check_weidian();
        if (empty($this->shop['is_pei'])) {
            $this->error('您签订的是由配送员配送！您管理不了订单！');
        }
        $Order = D('Order');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'status' => 1, 'shop_id' => $this->shop_id);
        if (strtotime($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && strtotime($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            if (!empty($bg_time) && !empty($end_date)) {
                $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            }
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                if (!empty($bg_time)) {
                    $map['create_time'] = array('EGT', $bg_time);
                }
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                if (!empty($end_time)) {
                    $map['create_time'] = array('ELT', $end_time);
                }
                $this->assign('end_date', $end_date);
            }
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $keyword = intval($keyword);
            if (!empty($keyword)) {
                $map['order_id'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }
        }
        $count = $Order->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Order->where($map)->order(array('create_time' => 'DESC'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
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
    public function wait2(){
//        $this->check_weidian();
        if (empty($this->shop['is_pei'])) {
            $this->error('您签订的是由配送员配送！您管理不了订单！');
        }
        $Order = D('Order');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'is_daofu' => 1, 'shop_id' => $this->shop_id);
        if (strtotime($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && strtotime($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            if (!empty($bg_time) && !empty($end_date)) {
                $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            }
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                if (!empty($bg_time)) {
                    $map['create_time'] = array('EGT', $bg_time);
                }
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                if (!empty($end_time)) {
                    $map['create_time'] = array('ELT', $end_time);
                }
                $this->assign('end_date', $end_date);
            }
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $keyword = intval($keyword);
            if (!empty($keyword)) {
                $map['order_id'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }
        }
        $count = $Order->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Order->where($map)->order(array('order_id' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
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
    //创建发货
    public function deliver(){
//        $this->check_weidian();
        $order_id = (int) $this->_get('order_id');
        if (!$order_id) {
            $this->fengmiMsg('参数错误');
        } else {
            if (!($order = D('Order')->find($order_id))) {
                $this->fengmiMsg('该订单不存在');
            } else {
                if ($order['shop_id'] != $this->shop_id) {
                    $this->fengmiMsg('非法操作');
                } else {
                    if ($order['status'] == 2 || $order['status'] == 3 || $order['status'] == 8 || $order['status'] == 4 || $order['status'] == 5) {
                        $this->fengmiMsg('该订单状态不正确，不能发货');
                    } else {
                        $data = array(
							'admin_id' => 0, 
							'shop_id' => $this->shop_id, 
							'create_time' => NOW_TIME, 
							'create_ip' => get_client_ip(), 
							'order_ids' => $order_id, 
							'name' => '商户手机创建捡货单' . date('Y-m-d H:i:s')
						);
                        D('Orderpick')->add($data);
                        D('Order')->save(array('status' => 2), array("where" => array('order_id' => $order_id)));
                        D('Ordergoods')->save(array('status' => 1), array("where" => array('order_id' => $order_id)));
						D('Weixintmpl')->weixin_shop_delivery_user($order_id,$this->uid,2);//发货通知买家接口，1外卖，2商城，3家政
                        if ($this->_get('wait')) {
                            $this->fengmiMsg('恭喜您，货到付款发货成功！', U('mart/wait2'));
                        } else {
                            $this->fengmiMsg('恭喜您，一键发货成功！', U('mart/wait'));
                        }
                    }
                }
            }
        }
        $this->error('发货失败！');
    }
    private function check_weidian(){
        $wd = D('WeidianDetails');
        $wd_res = $wd->where('shop_id =' . $this->shop_id)->find();
        if (!$wd_res) {
            $this->error('错误，请先完善微店资料！正在为你跳转中...', U('goods/weidian'));
        } elseif ($wd_res['audit'] == 0) {
            $this->error('您的微店正在审核中，请耐心等待！', U('goods/weidian'));
        } elseif ($wd_res['audit'] == 2) {
            $this->error('您的微店未通过审核！', U('goods/weidian'));
        }
    }
    public function create(){
        if (IS_AJAX) {
            $obj = D('Goodsshopcate');
            $data['shop_id'] = $this->shop_id;
            $cate_name = I('cate_name', '', 'trim,htmlspecialchars');
            $orderby = I('orderby', '', 'trim,intval');
            if (empty($cate_name)) {
                $this->ajaxReturn(array('status' => 'error', 'message' => '分类不能为空!'));
            }
            $detail = D('Goodsshopcate')->where(array('shop_id' => $this->shop_id, 'cate_name' => $cate_name))->select();
            if (!empty($detail)) {
                $this->ajaxReturn(array('status' => 'error', 'message' => '分类名称已存在!'));
            }
            $data['orderby'] = $orderby;
            $data['cate_name'] = $cate_name;
            if ($obj->add($data)) {
                $this->ajaxReturn(array('status' => 'success', 'message' => '添加成功!'));
            }
            $this->ajaxReturn(array('status' => 'error', 'message' => '操作失败!'));
        } else {
            $this->display();
        }
    }
    public function edit($cate_id = 0){
        $cate_id = I('v', '', 'intval,trim');
        if (IS_AJAX) {
            if ($cate_id) {
                $obj = D('Goodsshopcate');
                if (!($detail = $obj->find($cate_id))) {
                    $this->ajaxReturn(array('status' => 'error', 'message' => '请选择要编辑的商家分类!'));
                }
                if ($detail['shop_id'] != $this->shop_id) {
                    $this->ajaxReturn(array('status' => 'error', 'message' => '不可以修改别人的内容!'));
                }
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
                $data['shop_id'] = $this->shop_id;
                if (false !== $obj->save($data)) {
                    $this->ajaxReturn(array('status' => 'success', 'message' => '操作成功!'));
                }
                $this->ajaxReturn(array('status' => 'success', 'message' => '操作失败!'));
            } else {
                $this->ajaxReturn(array('status' => 'success', 'message' => '请选择要编辑的商家分类!'));
            }
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    private function editCheck(){
        $data['shop_id'] = $this->shop_id;
        $cate_name = I('cate_name', '', 'trim,htmlspecialchars');
        if (empty($cate_name)) {
            $this->ajaxReturn(array('status' => 'error', 'message' => '分类不能为空!'));
        }
        $detail = D('Goodsshopcate')->where(array('shop_id' => $this->shop_id, 'cate_name' => $cate_name))->find();
        if (!empty($detail) && $detail['cate_id'] != $cate_id) {
            $this->ajaxReturn(array('status' => 'error', 'message' => '分类名称已存在!'));
        }
        $data['orderby'] = I('orderby', '', 'trim,intval');
        $data['cate_name'] = $cate_name;
        if (empty($data['orderby'])) {
            $data['orderby'] = 100;
        }
        return $data;
    }
    public function delete2($goods_id = 0){
        $goods_id = (int) $goods_id;
        $obj = D('Goods');
        if (empty($goods_id)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商品信息不存在！'));
        }
        if (!($detail = D('Goods')->find($goods_id))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商品信息不存在！'));
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '不要操作别人的商品'));
        }
        $obj->save(array('goods_id' => $goods_id, 'closed' => 1));
        $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您删除成功'));
    }
    public function delete($cate_id = 0){
        if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
            $obj = D('Goodsshopcate');
            if (!($detail = $obj->find($cate_id))) {
                $this->baoError('改分类不存在');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->baoError('改分类不存在');
            }
            $obj->delete($cate_id);
            $this->success('删除成功！', U('goodsshopcate/index'));
        }
    }
	
	public function express($order_id = 0){
		$order_id = (int) $order_id;
        if (!($detail = D('Order')->find($order_id))) {
			$this->error('没有该订单'); 
        }
        if ($detail['closed'] != 0) {
			$this->error('订单被删除');        
		}
		if ($detail['status'] == 2 || $detail['status'] == 3 || $detail['status'] == 8 || $detail['status'] == 4 || $detail['status'] == 5) {
			$this->error('该订单状态不正确，不能发货');
        }
		if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('express_id', 'express_number'));
			$data['express_id'] = (int) $data['express_id'];
			if (empty($data['express_id'])) {
					$this->fengmiMsg('请选择快递');
			}
			if (!($Logistics = D('Logistics')->find($data['express_id']))) {
				$this->fengmiMsg('没有'.$detail['express_name'].'快递');
			}
			if ($Logistics['closed'] != 0) {
				$this->fengmiMsg('该快递已关闭');
			}
			$data['express_number'] = (int) $data['express_number'];
			if (empty($data['express_number'])) {
				$this->fengmiMsg('快递单号不能为空');
			}
			
			$add_express = array(
				'order_id' => $order_id,
				'express_id' => $data['express_id'], 
				'express_number' => $data['express_number']  
			);
			if(D('Order')->save($add_express)){
				D('Order')->pc_express_deliver($order_id);//执行发货
				if ($this->_get('wait')) {
					$this->fengmiMsg('恭喜您，货到付款发货成功！', U('mart/wait2'));
				} else {
					$this->fengmiMsg('恭喜您，一键发货成功！', U('mart/wait'));
				}
			}else{
				$this->fengmiMsg('发货失败');
			}
		}else{
			$this->assign('detail', $detail);
			$this->assign('logistics', D('Logistics')->where(array('closed' => 0))->select());
			$this->display();
	    }
		
		
	}
	
	
	 public function detail($order_id){
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D('Order')->find($order_id))) {
            $this->error('该订单不存在');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error('请不要操作其他商家的订单');
        }
        $order_goods = D('Ordergoods')->where(array('order_id' => $order_id))->select();
        $goods_ids = array();
        foreach ($order_goods as $k => $val) {
            $goods_ids[$val['goods_id']] = $val['goods_id'];
        }
        if (!empty($goods_ids)) {
            $this->assign('goods', D('Goods')->itemsByIds($goods_ids));
        }
		
		$data = D('Logistics')->get_order_express($order_id);//查询清单物流
		$this->assign('data', $data);
		
        $this->assign('ordergoods', $order_goods);
		$this->assign('users', D('Users')->find($detail['user_id']));
        $this->assign('Paddress', D('Paddress')->find($detail['address_id']));
		$this->assign('logistics', D('Logistics')->find($detail['express_id']));
        $this->assign('types', D('Order')->getType());
        $this->assign('goodtypes', D('Ordergoods')->getType());
        $this->assign('detail', $detail);
        $this->display();
    }
}