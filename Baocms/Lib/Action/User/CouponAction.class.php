<?php
class CouponAction extends CommonAction{
    public function index() {
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
    }
    public function couponloading(){
        $Coupondownloads = D('Coupondownload');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $aready = (int) $this->_param('aready');
        if ($aready == 2) {
            $map['is_used'] = array('egt', 1);
        } elseif ($aready == 1) {
            $map['is_used'] = 0;
        } else {
            $aready == null;
        }
        $count = $Coupondownloads->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Coupondownloads->where($map)->order('is_used asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $coupon_ids = array();
        foreach ($list as $k => $val) {
            $coupon_ids[$val['coupon_id']] = $val['coupon_id'];
        }
        $shops = D('Shop')->itemsByIds($shop_ids);
        $coupon = D('Coupon')->itemsByIds($coupon_ids);
        $this->assign('coupon', $coupon);
        $this->assign('shops', $shops);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function coupondel($download_id){
        $download_id = (int) $download_id;
        if (empty($download_id)) {
            $this->error('该优惠券不存在');
        }
        if (!($detail = D('Coupondownload')->find($download_id))) {
            $this->error('该优惠券不存在');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error('请不要操作别人的优惠券');
        }
        D('Coupondownload')->delete($download_id);
        $this->success('删除成功！', U('coupon/index'));
    }
    public function weixin(){
        $download_id = $this->_get('download_id');
        if (!($detail = D('Coupondownload')->find($download_id))) {
            $this->error('没有该优惠券');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error("优惠券不存在！");
        }
        if ($detail['is_used'] != 0) {
            $this->error('该优惠券属于不可消费的状态');
        }
        $url = U('weixin/coupon', array('download_id' => $download_id, 't' => NOW_TIME, 'sign' => md5($download_id . C('AUTH_KEY') . NOW_TIME)));
        $token = 'couponcode_' . $download_id;
        $file = baoQrCode($token, $url);
        $this->assign('file', $file);
        $this->assign('detail', $detail);
        $this->display();
    }
	//短信通知
    public function sms($download_id){
        $download_id = (int) $download_id;
        $obj = D('Coupondownload');
        if ($detail = D('Coupondownload')->find($download_id)) {
            if ($detail['user_id'] != $this->uid) {
                $this->error('非法操作');
            }
            if ($detail['is_sms'] != 0) {
                $this->error('您已请求过短信啦~');
            }
			D('Sms')->sms_coupon_user($download_id,$type =2);
            $obj->save(array('download_id' => $download_id, 'is_sms' => 1));
            $this->success('短信已成功发送到您手机！', U('coupon/index'));
        } else {
            $this->error('操作失败');
        }
    }
    public function give(){
        $download_id = $this->_get('download_id');
		
		//检测优惠劵状态封装
		if (!D('Coupondownload')->check_coupondownload_state($download_id,$this->uid)) {
		   $this->error(D('Coupondownload')->getError());	  
	    }
        if ($this->isPost()) {
            $mobile = $this->_post('mobile');
            if (empty($mobile)) {
                $this->fengmiMsg('请输入手机号码');
            }
			if (!isMobile($mobile) && !isPhone($mobile)) {
				$this->fengmiMsg('手机号码不正确');
			}
			$user = D('Users')->where(array('mobile' => $mobile))->find();
            if ($user['user_id'] == $this->uid) {
                $this->fengmiMsg('不能赠送给自己');
            }
			if(!empty($user['mobile'])){
				if (FALSE !== D('Coupondownload')->save(array('download_id' => $download_id, 'user_id' => $user['user_id']))){
					D('Sms')->sms_coupon_give_user($download_id,$user['user_id']);
                    $this->fengmiMsg('恭喜您赠送成功', U('coupon/index'));
                }else{
                    $this->fengmiMsg('操作失败');
                }
			}else{//如果会员没有手机号就直接去注册
				if (FALSE !== D('Coupondownload')->register_account_give_coupon($download_id,$mobile)){
                    $this->fengmiMsg('恭喜您赠送成功', U('coupon/index'));
                }else{
                    $this->fengmiMsg('注册账户操作失败');
                }
			}

        }
		$this->assign('coupon', $coupon = D('Coupon')->find($detail['coupon_id']));
        $this->assign('detail', $detail);
        $this->display();
    }
   
}