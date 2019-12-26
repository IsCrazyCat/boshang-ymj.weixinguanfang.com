<?php
class PublicAction extends CommonAction{
    public function email(){
        $email = $this->_get('email');
        if (!isEmail($email)) {
            $this->error('EMAIL地址不正确', U('index/index'));
        }
        $uid = (int) $this->_get('uid');
        $time = (int) $this->_get('time');
        $sig = $this->_get('sig');
        if (empty($uid) || empty($time) || empty($sig)) {
            $this->error('参数不能为空', U('index/index'));
        }
        if (NOW_TIME - $time > 3600) {
            $this->error('验证链接已经超时了！', U('index/index'));
        }
        $sign = md5($uid . $email . $time . C('AUTH_KEY'));
        if ($sig != $sign) {
            $this->error('签名失败', U('index/index'));
        }
        $user = D('Users')->find($uid);
        if (empty($user)) {
            $this->error('用户不存在！', U('index/index'));
        }
        if (!empty($user['email'])) {
            $this->error('用户已经通过邮件认证的！', U('index/index'));
        }
        $data = array('user_id' => $uid, 'email' => $email);
        D('Users')->save($data);
        D('Users')->integral($this->uid, 'email');
        D('Users')->prestige($this->uid, 'email');
        $this->success('恭喜您邮件认证成功！', U('index/index'));
    }
}