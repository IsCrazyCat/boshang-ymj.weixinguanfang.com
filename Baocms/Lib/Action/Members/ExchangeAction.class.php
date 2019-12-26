<?php



class ExchangeAction extends CommonAction {
	protected function _initialize() {
        parent::_initialize();
		$jifen = (int)$this->_CONFIG['operation']['jifen'];
		if ($jifen == 0) {
				$this->error('此功能已关闭');
				die;
			}
     }

    public function index() {
        $Integralexchange = D('Integralexchange');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('user_id' => $this->uid);
        $count = $Integralexchange->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Integralexchange->where($map)->order(array('exchange_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $good_ids = $addr_ids = array();
        foreach ($list as $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
            $good_ids[$val['goods_id']] = $val['goods_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
        }

 	$this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('goods', D('Integralgoods')->itemsByIds($good_ids));
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
  
}
