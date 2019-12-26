<?php
class BookingAction extends CommonAction {

	protected function _initialize(){
       parent::_initialize();
        if ($this->_CONFIG['operation']['booking'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
		$this->assign('types', $types = D('Bookingroom')->getType());
    }

    public function index() {
        $Bookingorder = D('Bookingorder');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array('user_id' => $this->uid);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars') ) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
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
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
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
        $count = $Bookingorder->where($map)->count(); 
        $Page = new Page($count,10); 
        $show = $Page->show(); 
        $list = $Bookingorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $order_ids  = $shops_ids = $room_ids= array();
        foreach ($list as $k => $val) {
            $order_ids[$val['order_id']] = $val['order_id'];
            $shops_ids[$val['shop_id']] = $val['shop_id'];
			$room_ids[$val['room_id']] = $val['room_id'];
        }
        if ($shops_ids) {
            $this->assign('shops', D('Booking')->itemsByIds($shops_ids));
        }
		if (!empty($room_ids)) {
            $this->assign('room', D('Bookingroom')->itemsByIds($room_ids));
        }
        $this->assign('list', $list); 
        $this->assign('page', $show);    
        $this->display();
    }

	public function detail($order_id){
		$Bookingorder = D('Bookingorder');
        if(!$order_id = (int) $order_id){
            $this->error('该订单不存在');
        }elseif(!$detail = $Bookingorder->find($order_id)){
            $this->error('该订单不存在');
        }elseif($detail['user_id'] != $this->uid){
            $this->error('非法操作');
        }else{
            $shop = D('Booking')->find($detail['shop_id']);
            $list = D('Bookingordermenu')->where(array('order_id'=>$order_id))->select();
            $menu_ids = array();
            foreach($list as $k=>$val){
                $menu_ids[$val['menu_id']] = $val['menu_id'];
            }
            if($menu_ids){
                $this->assign('menus',D('Bookingmenu')->itemsByIds($menu_ids));
            }
            $this->assign('list',$list);
			$this->assign('room', D('Bookingroom')->find($detail['room_id']));
            $this->assign('shop',$shop);
            $this->assign('detail',$detail);
			$logs = D('Paymentlogs')->getLogsByOrderId('booking', $order_id);
            $payments = D('Payment')->getPayments();
			$this->assign('type',$payments[$logs['code']]);
            $this->display();
        }
	}

    public function cancel($order_id){
       if(!$order_id = (int)$order_id){
           $this->baoError('订单不存在1');
       }elseif(!$detail = D('Bookingorder')->find($order_id)){
           $this->baoError('订单不存在2');
       }elseif($detail['user_id'] != $this->uid){
           $this->baoError('非法操作订单');
       }else{
           if(false !== D('Bookingorder')->cancel($order_id)){
               $this->baoSuccess('订单取消成功',U('booking/detail',array('order_id'=>$order_id)));
           }else{
               $this->baoError('订单取消失败');
           }
       }
    }
    
	public function comment($order_id) {
		$Bookingorder = D('Bookingorder');
        if(!$order_id = (int) $order_id){
            $this->error('没有该订单');
        }elseif (!$detail = $Bookingorder->find($order_id)) {
            $this->error('没有该订单');
        }elseif($detail['user_id'] != $this->uid){
            $this->error('不要评价别人的订座订单');
        }elseif($detail['comment_status'] ==1){
            $this->error('该订单已评价过了');
        }else{
            if ($this->_Post()) {
                $datas = $this->checkFields($this->_post('data', false), array('score','kw_score','hj_score','fw_score','contents'));
                $data['user_id'] = $this->uid;
                $data['shop_id'] = $detail['shop_id'];
                $data['order_id'] = $order_id;
                $data['score'] = (int) $datas['score'];
                if (empty($data['score'])) {
                    $this->baoError('评分不能为空');
                }
                if ($data['score'] > 5 || $data['score'] < 1) {
                    $this->baoError('评分为1-5之间的数字');
                }
                if (empty($datas['kw_score'])) {
                    $this->baoError('口味评分不能为空');
                }
                if ($datas['kw_score'] > 5 || $datas['kw_score'] < 1) {
                    $this->baoError('口味评分为1-5之间的数字');
                }
                if (empty($datas['hj_score'])) {
                    $this->baoError('环境评分不能为空');
                }
                if ($datas['hj_score'] > 5 || $datas['hj_score'] < 1) {
                    $this->baoError('环境评分为1-5之间的数字');
                }
                if (empty($datas['fw_score'])) {
                    $this->baoError('服务评分不能为空');
                }
                if ($datas['fw_score'] > 5 || $datas['fw_score'] < 1) {
                    $this->baoError('服务评分为1-5之间的数字');
                }

                $data['contents'] = htmlspecialchars($datas['contents']);
                if (empty($data['contents'])) {
                    $this->baoError('评价内容不能为空');
                }
                if ($words = D('Sensitive')->checkWords($datas['contents'])) {
                    $this->baoError('评价内容含有敏感词：' . $words);
                }
                $photos = $this->_post('photos', false);
                if($photos){
                    $data['have_photo'] = 1;
                }
				$data['show_date'] = date('Y-m-d', NOW_TIME + ($this->_CONFIG['mobile']['data_booking_dianping'] * 86400));
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                
                $data2 = array('shop_id'=>$detail['shop_id']);
                $shop = D('Booking')->find($detail['shop_id']);
                $data2['kw_score'] = round(($shop['comments']*$shop['kw_score']+$datas['kw_score'])/($shop['comments']+1),1);
                $data2['hj_score'] = round(($shop['comments']*$shop['hj_score']+$datas['hj_score'])/($shop['comments']+1),1);
                $data2['fw_score'] = round(($shop['comments']*$shop['fw_score']+$datas['fw_score'])/($shop['comments']+1),1);
                $data2['score'] = round(($shop['comments']*$shop['score']+$data['score'])/($shop['comments']+1),1);
                $data2['comments'] = $shop['comments'] + 1;
                if (D('Bookingdianping')->add($data)) {
                    $photos = $this->_post('photos', false);
                    $local = array();
                    foreach ($photos as $val) {
                        if (isImage($val))
                            $local[] = $val;
                    }
                    if (!empty($local)){
                        D('Bookingdianpingpic')->upload($order_id, $local);
                    }
                    D('Bookingorder')->updateCount($order_id, 'comment_status');
                    D('Booking')->save($data2);
                    D('Users')->updateCount($this->uid, 'ping_num');
                    $this->baoSuccess('恭喜您点评成功!', U('booking/index'));
                }
                $this->baoError('点评失败！');
            }else {
                $details = D('Shop')->find($detail['shop_id']);
                $this->assign('details', $details);
                $this->assign('order_id', $order_id);
                $this->display();
            }
        }
    }

}
