<?php
class PaymentAction extends CommonAction
{
    public function index()
    {
        $Payment = D('Payment');
        import('ORG.Util.Page');
        // 导入分页类
        $count = $Payment->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Payment->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function uninstall()
    {
        $payment_id = (int) $this->_get('payment_id');
        $payments = D('Payment')->fetchAll();
        if (!$payments[$payment_id]) {
            $this->baoError('没有该支付方式！');
        }
        $datas = array('payment_id' => $payment_id, 'is_open' => 0);
        D('Payment')->save($datas);
        D('Payment')->cleanCache();
        $this->baoSuccess('卸载支付方式成功！', U('payment/index'));
    }
    public function install()
    {
        $payment_id = (int) $this->_get('payment_id');
        $payments = D('Payment')->fetchAll();
        if (!$payments[$payment_id]) {
            $this->error('没有该支付方式！');
            die;
        }
        if ($payments[$payment_id]['code'] == 'money') {
            D('Payment')->save(array('payment_id' => $payment_id, 'is_open' => 1));
            $this->success("安装成功", U('payment/index'));
            die;
        }
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $datas = array('payment_id' => $payment_id, 'setting' => serialize($data), 'is_open' => 1);
            D('Payment')->save($datas);
            D('Payment')->cleanCache();
            $this->baoSuccess('恭喜您安装支付方式成功！', U('payment/index'));
        } else {
            $this->assign('detail', $payments[$payment_id]);
            $this->display($payments[$payment_id]['code']);
        }
    }
}