<?php
class IntegralexchangeAction extends CommonAction
{
    public function index()
    {
        $Integralexchange = D('Integralexchange');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
        $count = $Integralexchange->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Integralexchange->where($map)->order(array('exchange_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $shop_ids = $good_ids = $addr_ids = array();
        foreach ($list as $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
            $good_ids[$val['goods_id']] = $val['goods_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
        }
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('goods', D('Integralgoods')->itemsByIds($good_ids));
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function audit($exchange_id = 0)
    {
        if (is_numeric($exchange_id) && ($exchange_id = (int) $exchange_id)) {
            $obj = D('Integralexchange');
            $obj->save(array('exchange_id' => $exchange_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('integralexchange/index'));
        } else {
            $exchange_id = $this->_post('exchange_id', false);
            if (is_array($exchange_id)) {
                $obj = D('Integralexchange');
                foreach ($exchange_id as $id) {
                    $obj->save(array('exchange_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('integralexchange/index'));
            }
            $this->baoError('请选择要审核的积分兑换');
        }
    }
}