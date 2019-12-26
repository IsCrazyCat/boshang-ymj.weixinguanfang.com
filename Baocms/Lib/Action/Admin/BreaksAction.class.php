<?php

class BreaksAction extends CommonAction {

	public function index() {
        $breaks = D('Breaksorder');
		import('ORG.Util.Page');
		$map = array();
		
		$keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
		
		if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $user = D('Users')->find($user_id);
            $this->assign('nickname', $user['nickname']);
            $this->assign('user_id', $user_id);
        }
		
		
		$count = $breaks->where($map)->count();
		$Page = new Page($count, 20);
		$show = $Page->show();
		$list = $breaks->where($map)->order(array('order_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = $shop_ids = array();
		foreach ($list as $k => $val) {
            $list[$k]['yh'] = $val['amount'] - $val['need_pay'];
			$shop_ids[$val['shop_id']] = $val['shop_id'];
		    $user_ids[$val['user_id']] = $val['user_id'];
		}
		
		if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($shop_ids)) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
  
  
    
}