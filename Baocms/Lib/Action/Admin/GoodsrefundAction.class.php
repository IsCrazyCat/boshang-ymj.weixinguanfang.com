<?php
class GoodsrefundAction extends CommonAction{

    public function index(){
        $Order = D('Order');
        import('ORG.Util.Page');
		$map = array('status'=>4,'is_daofu'=>0,'closed'=>0);

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
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['code'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        $count = $Order->where($map)->count(); 
        $Page = new Page($count, 15);
        $show = $Page->show();// 分页显示输出
        $list = $Order->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $user_ids = $goods_ids = array();
        foreach ($list as $k => $val) {
            if (!empty($val['shop_id'])) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $user_ids[$val['user_id']] = $val['user_id'];
            $goods_ids[$val['goods_id']] = $val['goods_id'];
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('goods', D('Goods')->itemsByIds($goods_ids));
        $this->display();
    }
	
	
	//只支持单个退款
    public function refund($order_id = 0){
        $order_id = (int) $order_id;
		$gooder_order = D('Order');
        $detail = $gooder_order->find($order_id);
        if ($detail['is_daofu'] == 0) {
            if ($detail['status'] != 4) {
                $this->baoError('操作错误');
            }
			if ( FALSE !== $order->implemented_refund($order_id)){
               $this->baoSuccess('退款成功！', U('goodsrefund/index'));
            }
            else{
                $this->baoError('退款失败');
            }
        } else {
            $this->baoError('当前订单状态不正确');
        }
    }

}