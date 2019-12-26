<?php
class InfoAction extends CommonAction{
    public function face(){
        $this->display();
    }
	public function wxpayexception(){
		$this->error('非法错误，请联系网站管理员解决', U('member/index'));
        $this->display();
    }
    public function nickname(){
        if ($this->isPost()) {
            $nickname = $this->_post('nickname');
            $user = D('Users')->where(array('nickname' => $nickname))->find();
            if (!empty($user)) {
                $this->fengmiMsg('该昵称已被使用');
            }
            D('Users')->save(array('nickname' => $nickname, 'user_id' => $this->uid));
            $this->fengmiMsg('昵称已经更新', U('info/nickname'));
        }
        $this->display();
    }
    public function nickcheck(){
        $nickname = $this->_get('nickname');
        $user = D('Users')->where(array('nickname' => $nickname))->find();
        if (empty($user)) {
            echo '1';
        } else {
            echo '0';
        }
    }
    public function sendsms(){
        $mobile = $this->_post('mobile');
        if (isMobile($mobile)) {
            session('mobile', $mobile);
            $randstring = session('code');
            if (empty($randstring)) {
                $randstring = rand_string(6, 1);
                session('code', $randstring);
            }
            //大鱼短信
            if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
                D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_yzm', $mobile, array(
					'sitename' => $this->_CONFIG['site']['sitename'], 
					'code' => $randstring
				));
            } else {
                D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
            }
        }
    }
    public function password(){
        if ($this->isPost()) {
            $newpwd = $this->_post('newpwd', 'htmlspecialchars');
            if (empty($newpwd)) {
                $this->fengmiMsg('请输入新密码');
            }
            $pwd2 = $this->_post('pwd2', 'htmlspecialchars');
            if (empty($pwd2) || $newpwd != $pwd2) {
                $this->fengmiMsg('两次密码输入不一致！');
            }
			$used_password = D('Users')->where(array('user_id' => $this->uid))->find();
			if ($used_password['password'] == md5($newpwd) ) {
                $this->fengmiMsg('不能跟原来的密码一样');
            }
            if (D('Users')->save(array('user_id' => $this->uid, 'password' => md5($newpwd)))) {
                $this->fengmiMsg('更改密码成功！', U('member/index'));
            }
            $this->fengmiMsg('修改密码失败！');
        } else {
            $this->display();
        }
    }
    public function account(){
        if ($this->isPost()) {
            $mobile = $this->_post('mobile');
            $yzm = $this->_post('yzm');
            if (empty($mobile) || empty($yzm)) {
                $this->fengmiMsg('请填写正确的手机及手机收到的验证码！');
            }
            $s_mobile = session('mobile');
            $s_code = session('code');
            if ($mobile != $s_mobile) {
                $this->fengmiMsg('手机号码和收取验证码的手机号不一致！');
            }
            if ($yzm != $s_code) {
                $this->fengmiMsg('验证码不正确');
            }
            $user_id = D('Users')->where(array('mobile' => $mobile))->getField('user_id');
            $uids = D('Users')->where(array('user_id' => $this->uid))->getField('user_id');
            $connect = M('Connect');
            //连接connect表
            $open_id = $connect->where(array('uid' => $uids))->getField('open_id');
            $result = $connect->where(array('open_id' => $open_id))->setField('uid', $user_id);
            D('Passport')->logout();
            $this->fengmiMsg('您的帐号已经更新！', U('Wap/index/index'));
        }
        $this->display();
    }
	//设置，编辑支付密码
	 public function pay_password(){
        if ($this->isPost()) {
			$type  = (int)$this->_post('type');
			$yzm = $this->_post('yzm');
            if (empty($yzm))
                $this->fengmiMsg('请填写正确的手机及手机收到的验证码！');

            $session_mobile = session('mobile');
            $session_code = session('code');
            if ($yzm != $session_code){
				$this->fengmiMsg('验证码不正确');
			}
			if($type ==1){
				$pay_password = $this->_post('pay_password', 'htmlspecialchars');
				if (empty($pay_password)) {
					$this->fengmiMsg('支付密码不能为空！');
				}
				if (D('Passport')->set_pay_password($this->member['account'], $pay_password)) {
					$this->fengmiMsg('设置支付密码成功！', U('set/pay_password'));
				}	
			}else{
				$pay_password = $this->_post('pay_password', 'htmlspecialchars');
				if (empty($pay_password)) {
					$this->fengmiMsg('旧支付密码不能为空！');
				}
				$new_pay_password = $this->_post('new_pay_password', 'htmlspecialchars');
				if (empty($new_pay_password)) {
					$this->fengmiMsg('新的支付密码不能为空');
				}
				if ($this->member['password'] == md5($new_pay_password)) {
					$this->fengmiMsg('支付密码不能跟登录密码一致');
				}
				if ($this->member['pay_password'] == md5(md5($new_pay_password))) {
					$this->fengmiMsg('新的支付密码不能跟旧的支付密码一致');
				}
				$new_pay_password2 = $this->_post('new_pay_password2', 'htmlspecialchars');
				if (empty($new_pay_password2) || $new_pay_password != $new_pay_password2) {
					$this->fengmiMsg('两次支付密码输入不一致！');
				}
				if ($this->member['pay_password'] != md5(md5($pay_password))) {
					$this->fengmiMsg('原支付密码不正确'.$this->member['pay_password']);
				}
				if (D('Passport')->set_pay_password($this->member['account'], $new_pay_password)) {
					$this->fengmiMsg('更改支付密码成功！', U('member/index'));
				}
			}
        } else {
            $this->display();
        }
    }
}