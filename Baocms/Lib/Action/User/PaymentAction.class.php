<?php
class PaymentAction extends CommonAction {

    public function respond() {
        $code = $this->_get('code');
        if (empty($code)) {
            $this->error('没有该支付方式！');
            die;
        }
        $ret = D('Payment')->respond($code);
        if ($ret == false) {
            $this->error('支付验证失败！');
            die;
        }
        if ($this->isPost()) {
            echo 'SUCESS';
            die;
        }
        $type = D('Payment')->getType();
        $this->success('支付成功！', U('user/member/index'));
    }
}
