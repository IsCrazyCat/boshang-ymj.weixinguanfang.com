<?php

class PintuanAction extends CommonAction {
	
	protected function _initialize(){
       parent::_initialize();
        if ($this->_CONFIG['operation']['pintuan'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }

	public function groups() {
		$aready = (int)$this -> _param('aready');
		if ($aready == 1) {
			$map['order_status'] = 1;
		}elseif ($aready == 2) {
			$map['tuan_status'] = 2;
		}elseif ($aready == 3){
			$map['order_status'] = array('IN', array(3, 4, 5,9));
		}
		$map['user_id'] = $this -> uid;
		$list = D('Porder') -> where($map) -> select();
		$this -> assign('nextpage', LinkTo('pintuan/groupsddata', array('t' => NOW_TIME, 'aready' => $aready, 'p' => '0000')));
		$this -> assign('list', $list);
		$this -> assign('aready', $aready);
		$this -> display();
	}

	public function groupsddata() {
		$Order = D('Porder');
		import('ORG.Util.Page');
		// 导入分页类
		$aready = (int)$this -> _param('aready');
		if ($aready == 1) {
			$map['order_status'] = 1;
		}elseif ($aready == 2) {
			$map['tuan_status'] = 2;
		}elseif ($aready == 3){
			$map['order_status'] = array('IN', array(3, 4, 5));
		}
		$map['user_id'] = $this -> uid;
		$count = $Order -> where($map) -> count();
		// 查询满足要求的总记录数
		$Page = new Page($count, 10);
		// 实例化分页类 传入总记录数和每页显示的记录数
		$show = $Page -> show();
		// 分页显示输出
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page -> totalPages < $p) {
			die('0');
		}
		$list = $Order -> where($map) -> order(array('id' => 'desc')) -> limit($Page -> firstRow . ',' . $Page -> listRows) -> select();
		$goods_ids = $odrer_ids = array();
		foreach ($list as $key => $val) {
			$goods_ids[$val['id']] = $val['goods_id'];
			$odrer_ids[$val['id']] = $val['order_id'];
			$tlevel = D('Ptuan') ->where(array('order_id' => $val['order_id'])) ->getField('tlevel');
		}
		$this -> assign('Pgoods', D('Pgoods') -> itemsByIds($goods_ids));
		$this -> assign('Tstatus', D('Porder') -> getTstatus());
		$this -> assign('orderStatus', D('Porder') -> getorderStatus());
		$this -> assign('list', $list);
		$this -> assign('page', $show);
		$this -> assign('tlevel', $tlevel);
		$this -> display();
	}

	public function order() {
		$order_id = (int)$this -> _get('id');
		$order = D('Porder') -> find($order_id);
		$goods = D('Pgoods') -> find($order['goods_id']);
		$stateStr = "";
		if ($order['order_status'] == 1 || $order['order_status'] == 2 || $order['order_status'] == 3) {
			$stateStr = "state_1";
		}
		if ($order['order_status'] == 4) {
			$stateStr = "state_2";
		}
		if ($order['order_status'] == 5) {
			$stateStr = "state_3";
		}
		$this -> assign('stateStr', $stateStr);
		$this -> assign('order', $order);
		$this -> assign('goods', $goods);
		$this -> assign('orderStatus', D('Porder') -> getorderStatus());
		$this -> display();
	}

	public function quxiao() {
		$order_id = (int)$this -> _get('id');
		$order = D('Porder'); 
		$order -> save(array('id' => $order_id, 'order_status' => 6));
		$this->fengmiMsg('取消成功！', U('user/pintuan/groups'));
	}

	public function queren() {
		$order_id = (int)$this -> _get('id');
		$order = D('Porder'); 
		$order -> save(array('id' => $order_id, 'order_status' => 5));
		$this->fengmiMsg('确认成功，感谢您的购买！', U('user/pintuan/order', array('id' => $order_id)));
	}
}
