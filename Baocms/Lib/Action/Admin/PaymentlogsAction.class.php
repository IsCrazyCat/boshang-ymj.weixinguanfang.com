<?php
class PaymentlogsAction extends CommonAction{
    public function index(){
        $Paymentlogs = D('Paymentlogs');
        import('ORG.Util.Page');
        $map = array();
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time|pay_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time|pay_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time|pay_time'] = array('ELT', $end_time);
            }
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id|log_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Paymentlogs->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Paymentlogs->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
			$type = $Paymentlogs->get_payment_logs_type($val['type']);
            $list[$k]['type'] = $type;
			
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}