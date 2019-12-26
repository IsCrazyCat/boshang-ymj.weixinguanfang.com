<?php
class LoginAction extends CommonAction{
    public function index(){
        $this->display();
    }
    public function loging(){
        $yzm = $this->_post('yzm');
        if (strtolower($yzm) != strtolower(session('verify'))) {
            session('verify', null);
            $this->baoError('验证码不正确!', 2000, true);
        }
        $username = $this->_post('username', 'trim');
        $password = $this->_post('password', 'trim,md5');
        $adminObj = D('Admin');
        $admin = $adminObj->getAdminByUsername($username);
		
        if (empty($admin)) {
            session('verify', null);
            $this->baoError('账户不能为空', 2000, true);
        }
		
		if ($admin['closed'] == 1) {//关闭账户
            session('verify', null);
            $this->baoError('该账户已经被禁用!', 2000, true);
        }
        if ($admin['role_id'] == 2) {//类型错误
            session('verify', null);
            $this->baoError('分站管理员请登录分站后台', 2000, true);
        }
		
        if ($admin['is_admin_lock'] == 1) {
            $present_time = NOW_TIME;//当前时间
            $cha = 900;//这里后台设置
            $present_time_cha = $present_time - $admin['is_admin_lock_time'];
            if ($present_time_cha < $cha) {
                $echo_time = $cha - $present_time_cha;
                $this->baoError('您的账户已经被锁定，请' . $echo_time . '秒后登陆', 2000, true);
            }
        }

        if ($admin['password'] != $password) {
            $adminObj->where(array('admin_id' => $admin['admin_id']))->setInc('lock_admin_mum');
            if ($admin['lock_admin_mum'] >= 2) {
                $adminObj->save(array('admin_id' => $admin['admin_id'], 'is_admin_lock' => 1, 'is_admin_lock_time' => NOW_TIME));
                $this->baoError('您的账户已经被锁定，请15分钟后登陆', 2000, true);
                session('verify', null);
            }
			
			$this->baoError('用户名或密码不正确', 2000, true);
			session('verify', null);
        }
       
	   //判断IP
        $last_ip = get_client_ip();
		$t=time();
 		$time = date("Y-m-d H:i:s",$t);  
        if (!empty($ip)) {
            if ($admin['last_ip'] != $last_ip) {
                $adminObj->where(array('admin_id' => $admin['admin_id']))->save(array('is_ip' => 1));
				if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
					D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_TZ_admin', $admin['mobile'], array(
						'username' => $admin['username'], 
						'time' => $time
					));
					
				} else {
					D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
				}
					
            }
        }
		
        $adminObj->where(array('user_id' => $admin['user_id']))->save(array(
			'last_time' => NOW_TIME, 
			'last_ip' => $last_ip, 
			'is_admin_lock' => 0, 
			'lock_admin_mum' => 0, 
			'is_admin_lock_time' => ''
		));
		
        session('admin', $admin);
        $this->baoSuccess('登录成功！', U('index/index'));
    }
    public function logout(){
        $admin_ids = $this->_admin = session('admin');
		
         D('Admin')->where(array('user_id' => $admin_ids['user_id']))->save(array(
			'is_ip' => 0, 
			'is_lock' => 0, 
			'lock_num' => 0, 
			'is_lock_time' => ''
		));
		
        session('admin', null);
        $this->success('退出成功', U('login/index'));
    }
	
    public function verify(){
        import('ORG.Util.Image');
        Image::buildImageVerify(5, 2, 'png', 60, 30);
    }

	
    public function close2(){
        if (IS_AJAX) {
            $admin_id = $_POST['admin_id'];
            D('Admin')->where(array('admin_id' => $admin_id))->save(array('is_ip' => 00));
        }
    }
}