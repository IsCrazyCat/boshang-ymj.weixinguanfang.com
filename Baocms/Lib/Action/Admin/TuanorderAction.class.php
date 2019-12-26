<?php
class TuanorderAction extends CommonAction
{
    public function index()
    {
        $Tuanorder = D('Tuanorder');
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
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
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
        $count = $Tuanorder->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Tuanorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $user_ids = $tuan_ids = array();
        foreach ($list as $k => $val) {
            if (!empty($val['shop_id'])) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $user_ids[$val['user_id']] = $val['user_id'];
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('tuan', D('Tuan')->itemsByIds($tuan_ids));
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function delete($order_id = 0)
    {
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Tuanorder');
            $orderinfo = $obj->Field('tuan_id,num')->find($order_id);
            $tuan_num = $orderinfo['num'];
            $where['tuan_id'] = $orderinfo['tuan_id'];
            D("Tuan")->where($where)->setInc("num", $tuan_num);
            $obj->delete($order_id);
            $this->baoSuccess('删除成功！', U('tuanorder/index'));
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                $obj = D('Tuanorder');
                $tuan = D("Tuan");
                foreach ($order_id as $id) {
                    $orderinfo = $obj->Field('tuan_id,num')->find($id);
                    $tuan_num = $orderinfo['num'];
                    $where['tuan_id'] = $orderinfo['tuan_id'];
                    $tuan->where($where)->setInc("num", $tuan_num);
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('tuanorder/index'));
            }
            $this->baoError('请选择要删除的套餐订单');
        }
    }
}