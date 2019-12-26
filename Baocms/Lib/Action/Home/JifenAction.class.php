<?php
class JifenAction extends CommonAction{
    protected function _initialize(){
        parent::_initialize();
        $jifen = (int) $this->_CONFIG['operation']['jifen'];
        if ($jifen == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
    public function main(){
        $this->display();
    }
    public function index(){
        $Integralgoods = D('Integralgoods');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'audit' => 1);
        $count = $Integralgoods->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 'ex':
                $orderby = array('exchange_num' => 'desc');
                break;
            case 'j':
                $orderby = array('integral' => 'asc');
                break;
            default:
                $orderby = array('orderby' => 'asc');
                break;
        }
        $this->assign('order', $order);
        $list = $Integralgoods->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function shop($shop_id){
        $shop_id = (int) $shop_id;
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('该联盟商家不存在');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->error('该联盟商家不存在');
        }
        $this->assign('shopdetail', D('Shopdetails')->find($shop_id));
        $this->seodatas['shop_name'] = $detail['shop_name'];
        $this->assign('detail', $detail);
        $this->display();
    }
    public function detail($goods_id){
        $goods_id = (int) $goods_id;
        if (!($detail = D('Integralgoods')->find($goods_id))) {
            $this->error('该积分商品不存在或者已经下架！');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->error('该积分商品不存在或者已经下架！');
        }
        $this->assign('shop', D('Shop')->find($detail['shop_id']));
        $sd = D('ShopDetails');
        $rsd = $sd->where(array('shop_id' => $detail['shop_id']))->find();
        $this->assign('rsd', $rsd);
        $this->assign('detail', $detail);

        $this->seodatas['title'] = $detail['title'];
        $this->seodatas['price'] = $detail['price'];
        $this->seodatas['num'] = $detail['num'];
        if (!empty($detail['details'])) {
            $this->seodatas['details'] = bao_Msubstr($detail['details'], 0, 200, false);
        } else {
            $this->seodatas['details'] = $detail['title'];
        }
        $this->display();
    }
    public function exchange($goods_id){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $goods_id = (int) $goods_id;
        if (!($detail = D('Integralgoods')->find($goods_id))) {
            $this->baoError('该积分商品不存在或者已经下架！');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->baoError('该积分商品不存在或者已经下架！');
        }
		
		$user_exchange = D('Integralexchange')->where(array('user_id'=>$this->uid,'goods_id'=>$goods_id))->count();
			if ($detail['limit_num'] < $user_exchange) {
                $this->baoError('此商品每人限制兑换'.$detail['limit_num'].'份');
        }
			
        if ($this->isPost()) {
            if ($detail['num'] <= 0) {
                $this->baoError('该商品已经兑换完了！');
            }
            $addr_id = (int) $this->_post('addr_id');
            if (empty($addr_id)) {
                $data = $this->checkFields($this->_post('data', false), array('addr_id', 'area_id', 'business_id', 'name', 'mobile', 'addr'));
                $data['name'] = htmlspecialchars($data['name']);
                if (empty($data['name'])) {
                    $this->baoError('收货人不能为空');
                }
                $data['user_id'] = (int) $this->uid;
                $data['area_id'] = (int) $data['area_id'];
                $data['business_id'] = (int) $data['business_id'];
                if (empty($data['area_id'])) {
                    $this->baoError('地区不能为空');
                }
                if (empty($data['business_id'])) {
                    $this->baoError('商圈不能为空');
                }
                $data['mobile'] = htmlspecialchars($data['mobile']);
                if (empty($data['mobile'])) {
                    $this->baoError('手机号码不能为空');
                }
                if (!isMobile($data['mobile'])) {
                    $this->baoError('手机号码格式不正确');
                }
                $data['addr'] = htmlspecialchars($data['addr']);
                if (empty($data['addr'])) {
                    $this->baoError('具体地址不能为空');
                }
            } else {
                if (!($addr = D('Useraddr')->find($addr_id))) {
                    $this->baoError('请选择收货地址！');
                }
                if ($addr['user_id'] != $this->uid) {
                    $this->baoError('请选择收货地址！');
                }
            }
            $member = D('Users')->find($this->uid);
            if ($member['integral'] < $detail['integral']) {
                $this->baoError('您的积分不足！该商品您兑换不了！');
            }
            $ip = get_client_ip();
            if (D('Users')->save(array('user_id' => $this->uid, 'integral' => $member['integral'] - $detail['integral']))) {
                if (empty($addr_id)) {
                    $addr_id = D('Useraddr')->add($data);
                }
                D('Userintegrallogs')->add(array(
					'user_id' => $this->uid, 
					'integral' => -$detail['integral'], 
					'intro' => "兑换积分产品" . $goods_id, 
					'create_time' => NOW_TIME, 
					'create_ip' => $ip
				));
                D('Integralexchange')->add(array(
					'user_id' => $this->uid, 
					'shop_id' => $detail['shop_id'], 
					'addr_id' => $addr_id, 
					'goods_id' => $detail['goods_id'], 
					'create_time' => NOW_TIME, 
					'create_ip' => $ip
				));

                D('Integralgoods')->save(array('goods_id' => $goods_id, 'num' => $detail['num'] - 1, 'exchange_num' => $detail['exchange_num'] + 1));
				//返还积分测试
				if($this->_CONFIG['integral']['return'] == 1){
					$shop = D('Shop')->find($detail['shop_id']);
					D('Users')->return_integral($shop['user_id'], $detail['integral'] , '用户积分兑换返还给商家积分');
				}
                $this->baoSuccess('兑换成功！', U('members/exchange'));
            }
            $this->baoError('兑换失败');
        } else {
            $useraddr = D('Useraddr')->where(array('user_id' => $this->uid))->limit(0, 5)->select();
            $this->assign('useraddr', $useraddr);
            $this->assign('detail', $detail);
            $this->display();
        }
    }
}