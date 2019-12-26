<?php



class VipAction extends CommonAction {

	public function _initialize() {
		parent::_initialize();
		$check = D('Shop')->where(array('shop_id' => $this->shop_id, 'audit' => 1))->find();
		if ($check['card_date'] < TODAY || empty($check['card_date'])) {
			$this->error('您没有开通会员卡服务或者会员卡服务已过期');
		}
	}

	public function index() {
		if ($keyword = $this->_post('keyword', 'htmlspecialchars')) {
			if (!isMobile($keyword)) {
				$this->error('手机号码不正确');
			}
			$map['account|mobile'] = trim($keyword);
			$Users = D('Users');
			$user = $Users->where($map)->find();
			$this->assign('user', $user);
			$this->assign('keyword', $keyword);
		}
		$this->display();
	}

	public function bonus() {
		$Uid = (int) ($_GET['uid']);
		$User = D('Users')->find($Uid);
		if(empty($User)){
			$this->error('用户不存在！');
		}
		if ($this->isPost()) {
			$integral = (int) ($_POST['integral']);
			if($integral <= 0){
				$this->error('请输入正确的积分');
			}
			if($this->member['integral'] < $integral){
				$this->error('您的账户积分不足');
			}
			D('Users')->addIntegral($this->uid,-$integral,'赠送会员积分');
			D('Users')->addIntegral($Uid,$integral,'获得商家赠送积分');
			$this->success('赠送积分成功!',U('vip/bonus',array('uid'=>$Uid)));
		} else {
			$this->assign('user', $User);
			$this->display();
		}
	}

}
