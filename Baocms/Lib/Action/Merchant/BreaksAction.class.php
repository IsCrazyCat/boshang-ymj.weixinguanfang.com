<?php

class BreaksAction extends CommonAction {

	public function index() {
        $breaks = D('Breaksorder');
		import('ORG.Util.Page');
		$map = array('shop_id' => $this->shop_id);
        
		$count = $breaks->where($map)->count();
		$Page = new Page($count, 20);
		$show = $Page->show();

		$list = $breaks->where($map)->order(array('order_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$shop_ids = array();
		foreach ($list as $k => $val) {
            $list[$k]['yh'] = $val['amount'] - $val['need_pay'];
			$shop_ids[$val['shop_id']] = $val['shop_id'];
			$user_ids[$val['user_id']] = $val['user_id'];
		}
		$this->assign('users', $users = D('Users')->itemsByIds($user_ids));
		$shops = D('Shop')->itemsByIds($shop_ids);
		$this->assign('shops', $shops);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
  
  
    
}