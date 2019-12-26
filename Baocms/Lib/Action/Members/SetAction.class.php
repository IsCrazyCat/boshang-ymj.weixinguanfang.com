<?php
class SetAction extends CommonAction{
    public function base(){
        if ($this->isPost()) {
            $data['job'] = $this->_post('job', 'htmlspecialchars');
            $data['sex'] = (int) $this->_post('sex');
            $data['star_id'] = (int) $this->_post('star_id');
            $data['born_year'] = (int) $this->_post('born_year');
            $data['born_month'] = (int) $this->_post('born_month');
            $data['born_day'] = (int) $this->_post('born_day');
            $detail = D('Usersex')->getUserex($this->uid);
            $data['user_id'] = $detail['user_id'];
            if (false !== D('Usersex')->save($data)) {
                $this->baoSuccess('基本信息设置成功！', U('set/base'));
            }
            $this->baoError('基本信息设置失败');
        } else {
            $usersex = D('Usersex')->find($this->uid);
            $stars = D('Usersex')->getStar();
            $this->assign('stars', $stars);
            $this->assign('usersex', $usersex);
            $this->display();
        }
    }
    public function nickname(){
        if ($this->isPost()) {
            $nickname = $this->_post('nickname', 'htmlspecialchars');
            if (empty($nickname)) {
                $this->baoError('请填写昵称');
            }
            $data = array('user_id' => $this->uid, 'nickname' => $nickname);
            if (false !== D('Users')->save($data)) {
                $this->baoSuccess('昵称设置成功！', U('set/nickname'));
            }
            $this->baoError('昵称设置失败');
        } else {
            $this->display();
        }
    }
    public function face(){
        if ($this->isPost()) {
            $face = $this->_post('face', 'htmlspecialchars');
            if (empty($face)) {
                $this->baoError('请上传头像');
            }
            if (!isImage($face)) {
                $this->baoError('头像格式不正确');
            }
            $data = array('user_id' => $this->uid, 'face' => $face);
            if (false !== D('Users')->save($data)) {
                $this->baoSuccess('上传头像成功！', U('set/face'));
            }
            $this->baoError('更新头像失败');
        } else {
            $this->display();
        }
    }
	
	 public function pay_password(){
        if ($this->isPost()) {
			$type  = (int)$this->_post('type');
			$yzm = $this->_post('yzm');
            if (empty($yzm))
                $this->baoError('请填写正确的手机及手机收到的验证码！');
            $session_mobile = session('mobile');
            $session_code = session('code');
            if ($this->member['mobile'] != $session_mobile)
                $this->baoError('您绑定的手机号码和收取验证码的手机号不一致！');
            if ($yzm != $session_code){
				$this->baoError('验证码不正确');
			}
			if($type ==1){
				$pay_password = $this->_post('pay_password', 'htmlspecialchars');
				if (empty($pay_password)) {
					$this->baoError('支付密码不能为空！');
				}
				if (D('Passport')->set_pay_password($this->member['account'], $pay_password)) {
					$this->baoSuccess('设置支付密码成功！', U('set/pay_password'));
				}	
			}else{
				$pay_password = $this->_post('pay_password', 'htmlspecialchars');
				if (empty($pay_password)) {
					$this->baoError('旧支付密码不能为空！');
				}
				$new_pay_password = $this->_post('new_pay_password', 'htmlspecialchars');
				if (empty($new_pay_password)) {
					$this->baoError('新的支付密码不能为空');
				}
				if ($this->member['password'] == md5($new_pay_password)) {
					$this->baoError('支付密码不能跟登录密码一致');
				}
				if ($this->member['pay_password'] == md5(md5($new_pay_password))) {
					$this->baoError('新的支付密码不能跟旧的支付密码一致');
				}
				$new_pay_password2 = $this->_post('new_pay_password2', 'htmlspecialchars');
				if (empty($new_pay_password2) || $new_pay_password != $new_pay_password2) {
					$this->baoError('两次支付密码输入不一致！');
				}
				if ($this->member['pay_password'] != md5(md5($pay_password))) {
					$this->baoError('原支付密码不正确');
				}
				if (D('Passport')->set_pay_password($this->member['account'], $new_pay_password)) {
					$this->baoSuccess('更改支付密码成功！', U('set/pay_password'));
				}
			}
        } else {
            $this->display();
        }
    }
	
    public function password(){
        if ($this->isPost()) {
            $oldpwd = $this->_post('oldpwd', 'htmlspecialchars');
            if (empty($oldpwd)) {
                $this->baoError('旧密码不能为空！');
            }
            $newpwd = $this->_post('newpwd', 'htmlspecialchars');
            if (empty($newpwd)) {
                $this->baoError('请输入新密码');
            }
            $pwd2 = $this->_post('pwd2', 'htmlspecialchars');
            if (empty($pwd2) || $newpwd != $pwd2) {
                $this->baoError('两次密码输入不一致！');
            }
            if ($this->member['password'] != md5($oldpwd)) {
                $this->baoError('原密码不正确');
            }
            if (D('Passport')->uppwd($this->member['account'], $oldpwd, $newpwd)) {
                session('uid', null);
                $this->baoSuccess('更改密码成功！', U('Home/passport/login'));
            }
            $this->baoError('修改密码失败！');
        } else {
            $this->display();
        }
    }
    public function mobile(){
        if ($this->isPost()) {
            $mobile = $this->_post('mobile');
            $yzm = $this->_post('yzm');
            if (empty($mobile) || empty($yzm)) {
                $this->baoError('请填写正确的手机及手机收到的验证码！');
            }
            $s_mobile = session('mobile');
            $s_code = session('code');
            if ($mobile != $s_mobile) {
                $this->baoError('手机号码和收取验证码的手机号不一致！');
            }
            if ($yzm != $s_code) {
                $this->baoError('验证码不正确');
            }
            $data = array('user_id' => $this->uid, 'mobile' => $mobile);
            if (D('Users')->save($data)) {
                D('Users')->integral($this->uid, 'mobile');
                D('Users')->prestige($this->uid, 'mobile');
                $this->baoSuccess('恭喜您通过手机认证', U('set/mobile'));
            }
            $this->baoError('更新数据失败！');
        } else {
            $this->display();
        }
    }
    public function mobile2()
    {
        if ($this->isPost()) {
            $mobile = $this->_post('mobile');
            $yzm = $this->_post('yzm');
            if (empty($mobile) || empty($yzm)) {
                $this->baoError('请填写正确的手机及手机收到的验证码！');
            }
            $s_mobile = session('mobile');
            $s_code = session('code');
            if ($mobile != $s_mobile) {
                $this->baoError('手机号码和收取验证码的手机号不一致！');
            }
            if ($yzm != $s_code) {
                $this->baoError('验证码不正确');
            }
            $data = array('user_id' => $this->uid, 'mobile' => $mobile);
            if (D('Users')->save($data)) {
                $this->baoSuccess('恭喜您成功更换绑定手机号', U('set/mobile'));
            }
            $this->baoError('更新数据失败！');
        } else {
            $this->display();
        }
    }
    public function sendsms(){
        if (!($mobile = $this->_post('mobile'))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请输入正确的手机号码'));
        }
        if (!isMobile($mobile)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请输入正确的手机号码'));
        }
        if ($user = D('Users')->where(array('mobile' => $mobile))->find()) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '手机号码已经存在！'));
        }
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
        $this->ajaxReturn(array('status' => 'success', 'msg' => '短信发送成功，请留意收到的短信', 'code' => session('code')));
    }
    public function email()
    {
        $this->display();
    }
    public function sendemail()
    {
        $email = $this->_post('email');
        if (isEmail($email)) {
            $link = 'http://' . $_SERVER['HTTP_HOST'];
            $uid = $this->uid;
            $local = array('email' => $email, 'uid' => $uid, 'time' => NOW_TIME, 'sig' => md5($uid . $email . NOW_TIME . C('AUTH_KEY')));
            $link .= U('public/email', $local);
            D('Email')->sendMail('email_rz', $email, $this->_CONFIG['site']['sitename'] . '邮件认证', array('link' => $link));
        }
    }
}