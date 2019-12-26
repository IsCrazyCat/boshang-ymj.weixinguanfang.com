<?php
class PassportAction extends CommonAction
{
    private $create_fields = array('account', 'password', 'nickname');
    public function bind()
    {
        $this->display();
    }
    public function login(){
        if ($this->isPost()) {
            $account = $this->_post('account');
            if (empty($account)) {
                $this->error('请输入用户名!');
            }
            $password = $this->_post('password');
            if (empty($password)) {
                $this->error('请输入登录密码!');
            }
            $backurl = $this->_post('backurl', 'htmlspecialchars');
            if (empty($backurl)) {
                $backurl = U('index/index');
            }
            if (true == D('Passport')->login($account, $password)) {
                $this->success('恭喜您登录成功！', $backurl);
            }
            $this->error(D('Passport')->getError());
        } else {
            if (!empty($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) && !strstr($_SERVER['HTTP_REFERER'], 'passport')) {
                $backurl = $_SERVER['HTTP_REFERER'];
            } else {
                $backurl = U('index/index');
            }
            $this->assign('backurl', $backurl);
            $this->display();
        }
    }
   
    public function logout()
    {
        D('Passport')->logout();
        $this->success('退出登录成功！', U('passport/login'));
    }
}