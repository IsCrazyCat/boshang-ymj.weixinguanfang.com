<?php
class SmsAction extends CommonAction
{
    public function index()
    {
        $sms_shop_money = $this->_CONFIG['sms_shop']['sms_shop_money'];
        //单价
        $sms_shop_small = $this->_CONFIG['sms_shop']['sms_shop_small'];
        //最少购买多少条
        $sms_shop_big = $this->_CONFIG['sms_shop']['sms_shop_big'];
        //最大购买多少条
        $nums = D('Smsshop')->where(array('type' => shop, 'shop_id' => $this->shop_id))->find();
        if (IS_POST) {
            $num = (int) $_POST['num'];
            if ($num <= 0) {
                $this->baoError('购买数量不合法');
            }
            if ($num % 100 != 0) {
                $this->baoError('总需人次必须为100的倍数');
            }
            if ($num < $sms_shop_small) {
                $this->baoError('购买短信数量不得小于' . $sms_shop_small . '条');
            }
            if ($num > $sms_shop_big) {
                $this->baoError('购买短信数量不得大于' . $sms_shop_big . '条');
            }
            if ($nums['num'] >= 1000) {
                $this->baoError('您当前还有' . $nums['num'] . '条短信，用完再来买吧');
            }
            $money = $num * ($sms_shop_money * 100);
            //总金额
            if ($money > $this->member['money'] || $this->member['money'] == 0) {
                $this->baoError('你的余额不足，请先充值');
            }
            if (D('Users')->addMoney($this->uid, -$money, '商户购买短信：' . $num . '条')) {
                if (empty($nums)) {
                    //如果以前没有购买过
                    $data['user_id'] = $this->uid;
                    $data['shop_id'] = $this->shop_id;
                    $data['type'] = shop;
                    $data['num'] = $num;
                    $data['create_time'] = NOW_TIME;
                    $data['create_ip'] = get_client_ip();
                    D('Smsshop')->add($data);
                } else {
                    D('Smsshop')->where(array('log_id' => $nums['log_id']))->setInc('num', $num);
                    // 增加短信
                }
                $this->baoSuccess('购买短信成功', U('sms/index'));
            } else {
                $this->baoError('购买错误，没有付款成功！');
            }
        } else {
            $this->assign('sms_shop_money', $sms_shop_money);
            $this->assign('sms_shop_small', $sms_shop_small);
            $this->assign('sms_shop_big', $sms_shop_big);
            $this->assign('nums', $nums);
            $this->display();
        }
        $this->display();
    }
}