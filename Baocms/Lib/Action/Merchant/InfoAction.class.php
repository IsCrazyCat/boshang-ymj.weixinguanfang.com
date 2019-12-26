<?php



class InfoAction extends CommonAction {

    public function ranking() {
        if ($this->member['gold'] < 5) {
            $this->baoError('账户金块余额不足！');
        }
        if (D('Users')->updateCount($this->uid, 'gold', -5)) {
            D('Usergoldlogs')->add(array(
                'user_id' => $this->uid,
                'gold' => -5,
                'intro' => '刷新排名',
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip()
            ));
            D('Shop')->save(array('shop_id' => $this->shop_id, 'ranking' => NOW_TIME));
            $this->baoSuccess('刷新排名成功！', U('index/main'));
        }
        $this->baoError('操作失败');
    }

    public function password() {
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
            $user = D('Users')->getUserByAccount($this->member['account']);
            if (flase !== D('Users')->save(array('user_id' => $user['user_id'], 'password' => md5($newpwd)))) {
                session('uid', null);
                $this->baoSuccess('更改密码成功！', U('login/index'));
            }
            $this->baoError('修改密码失败！');
        } else {
            $this->display();
        }
    }

}
