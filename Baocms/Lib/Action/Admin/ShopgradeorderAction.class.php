<?php
class ShopgradeorderAction extends CommonAction{

    public function index(){
        $Shopgradeorder = D('Shopgradeorder');
        import('ORG.Util.Page');
        $map = array('closed'=>0);
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
        $count = $Shopgradeorder->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Shopgradeorder->where($map)->order(array('order_id' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = $grade_ids = $shop_ids = array();
        foreach ($list as $key => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$user_ids[$val['user_id']] = $val['user_id'];
			$grade_ids[$val['grade_id']] = $val['grade_id'];
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
		$this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('shopgrades', D('Shopgrade')->itemsByIds($grade_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    
    public function delete($order_id = 0){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Shopgradeorder');
            $obj->save(array('order_id' => $order_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('Shopgradeorder/index'));
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($grade_id)) {
                $obj = D('Shopgradeorder');
                foreach ($order_id as $id) {
                    $obj->save(array('order_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('批量删除成功！', U('Shopgradeorder/index'));
            }
            $this->baoError('请选择要删除的订单');
        }
    }
	
	
}