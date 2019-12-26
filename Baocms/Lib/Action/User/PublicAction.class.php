<?php

/*
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：baocms团队
 * 官网：www.taobao.com
 * 邮件: youge@baocms.com  QQ 800026911
 */

class PublicAction extends CommonAction {

    public function email() { //email验证接口
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
        if (empty($user))
            $this->error('用户不存在！', U('index/index'));
        if (!empty($user['email']))
            $this->error('用户已经通过邮件认证的！', U('index/index'));
        $data = array(
            'user_id' => $uid,
            'email' => $email
        );
        D('Users')->save($data);
        D('Users')->integral($this->uid, 'email');
        D('Users')->prestige($this->uid, 'email');
        $this->success('恭喜您邮件认证成功！', U('index/index'));
    }

    public function shopcate($parent_id = 0) {
        $datas = D('Shopcate')->fetchAll();
        $str = '';

        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['cate_id'] == $parent_id) {

                foreach ($datas as $var2) {

                    if ($var2['parent_id'] == $var['cate_id']) {
                        $str.='<option value="' . $var2['cate_id'] . '">' . $var2['cate_name'] . '</option>' . "\n\r";
                    }
                }
            }
        }
        echo $str;
        die;
    }

    public function child($parent_id = 0) {
        $datas = D('Activitytype')->fetchAll();
        $str = '';

        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['type_id'] == $parent_id) {

                foreach ($datas as $var2) {

                    if ($var2['parent_id'] == $var['type_id']) {
                        $str.='<option value="' . $var2['type_id'] . '">' . $var2['type_name'] . '</option>' . "\n\r";
                    }
                }
            }
        }
        echo $str;
        die;
    }

    public function business($area_id = 0) {

        $str = '<option value="0">请选择</option>';
        foreach ($this->bizs as $val) {
            if ($val['area_id'] == $area_id) {
                $str.='<option value="' . $val['business_id'] . '">' . $val['business_name'] . '</option>';
            }
        }
        echo $str;
        die;
    }
    
}
