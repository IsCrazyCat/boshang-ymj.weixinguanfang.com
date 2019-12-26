<?php



class PassportAction extends CommonAction {

    private $create_fields = array('account', 'password', 'nickname');

    public function bind() {
        $this->display();
    }

    public function login(){
        if ($this->isPost()){        
            
            $yzm = $this->_post('yzm');
            if (strtolower($yzm) != strtolower(session('verify'))) {
                session('verify', null);
                $backurl = U('index/index');
                $this->ajaxReturn(array('status'=>'error','message'=>'验证码不正确!','backurl'=>$backurl));
            }
            
            $account = $this->_post('account');
            if (empty($account)) {
                 $this->ajaxReturn(array('status'=>'error','message'=>'请输入用户名!'));
            }

            $password = $this->_post('password');
            if (empty($password)) {
                $this->ajaxReturn(array('status'=>'error','message'=>'请输入登录密码!'));
            }
            $backurl = $this->_post('backurl', 'htmlspecialchars');
            if (empty($backurl))
                $backurl = U('index/index');
            if (true == D('Passport')->login($account, $password)) {
                $this->ajaxReturn(array('status'=>'success','message'=>'登录成功!','backurl'=>$backurl));
            }
            $this->ajaxReturn(array('status'=>'error','message'=>D('Passport')->getError()));

        } else{
            if (!empty($_SERVER['HTTP_REFERER'])&&strstr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) && !strstr($_SERVER['HTTP_REFERER'], 'passport')) {
                $backurl = $_SERVER['HTTP_REFERER'];
            } else {
                $backurl = U('index/index');
            }
            $this->assign('backurl', $backurl);
            $this->display();
        }
    }
    public function logout() {

        D('Passport')->logout();
        $this->success('退出登录成功！', U('passport/login'));
    }

}
