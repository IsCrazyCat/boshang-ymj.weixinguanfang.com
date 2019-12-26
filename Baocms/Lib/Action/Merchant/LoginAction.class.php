<?php



class  LoginAction extends CommonAction{
    
    public function index(){
        
        if($this->isPost()){
            $yzm = $this->_post('yzm');
            if(strtolower($yzm) != strtolower(session('verify'))){
                session('verify',null);
                $this->baoError('验证码不正确!',2000,true);
            }
            $account = $this->_post('account');
            if(empty($account)) {
                session('verify',null);
                $this->baoError('请输入用户名!',2000,true);
            }
      
            $password = $this->_post('password');
            if(empty($password)) {
                session('verify',null);
                $this->baoError('请输入登录密码!',2000,true);
            }
            if(true == D('Passport')->login($account,$password)){
                $this->baoSuccess('恭喜您登录成功！',U('index/index'));
            }
            $this->baoError(D('Passport')->getError(),3000,true);
        }else{
            $this->display();
        }
    }
    
    public function logout(){
        D('Passport')->logout();
        $this->success('退出登录成功！',U('login/index'));
    }
        
}