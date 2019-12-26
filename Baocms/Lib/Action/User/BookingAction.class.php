<?php

class BookingAction extends CommonAction {
	
	
	 protected function _initialize(){
        parent::_initialize();
        if ($this->_CONFIG['operation']['booking'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }

    public function index() {
        $st = (int) $this->_param('st');
		$this->assign('st', $st);
        $this->mobile_title = '我的订座';
        $this->display();
    }

    public function loaddata() {
		$Bookingorder = D('Bookingorder');
		import('ORG.Util.Page'); 
		$map = array('user_id' => $this->uid); 
		$st = (int) $this->_param('st');
		$map['order_status'] = $st;
		$count = $Bookingorder->where($map)->count(); 
          
		$Page = new Page($count, 10); 
		$show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
			die('0');
		}
		$list = $Bookingorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$shop_ids = $room_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$room_ids[$val['room_id']] = $val['room_id'];
        }
        if (!empty($shop_ids)) {
            $this->assign('shops', D('Booking')->itemsByIds($shop_ids));
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
            $log = D('Paymentlogs')->where(array('type'=>'ding','order_id'=>$order_id))->find();
			$this->assign('room', D('Bookingroom')->find($detail['room_id']));
            $this->assign('log',$log);
            $this->assign('list',$list);
            $this->assign('shop',$shop);
            $this->assign('detail',$detail);
            $this->display();
        }
	}

    public function cancel($order_id){
       if(!$order_id = (int)$order_id){
           $this->error('订单不存在');
       }elseif(!$detail = D('Bookingorder')->find($order_id)){
           $this->error('订单不存在');
       }elseif($detail['user_id'] != $this->uid){
           $this->error('非法操作订单');
       }else{
           if(false !== D('Bookingorder')->cancel($order_id)){
               $this->success('订单取消成功',U('booking/detail',array('order_id'=>$order_id)));
           }else{
               $this->error('订单取消失败');
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
                    $this->fengmiMsg('评分不能为空');
                }
                if ($data['score'] > 5 || $data['score'] < 1) {
                    $this->fengmiMsg('评分为1-5之间的数字');
                }
                if (empty($datas['kw_score'])) {
                    $this->baoError('口味评分不能为空');
                }
                if ($datas['kw_score'] > 5 || $datas['kw_score'] < 1) {
                    $this->fengmiMsg('口味评分为1-5之间的数字');
                }
                if (empty($datas['hj_score'])) {
                    $this->fengmiMsg('环境评分不能为空');
                }
                if ($datas['hj_score'] > 5 || $datas['hj_score'] < 1) {
                    $this->fengmiMsg('环境评分为1-5之间的数字');
                }
                if (empty($datas['fw_score'])) {
                    $this->fengmiMsg('服务评分不能为空');
                }
                if ($datas['fw_score'] > 5 || $datas['fw_score'] < 1) {
                    $this->fengmiMsg('服务评分为1-5之间的数字');
                }

                $data['contents'] = htmlspecialchars($datas['contents']);
                if (empty($data['contents'])) {
                    $this->fengmiMsg('评价内容不能为空');
                }
                if ($words = D('Sensitive')->checkWords($datas['contents'])) {
                    $this->fengmiMsg('评价内容含有敏感词：' . $words);
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
                    $this->fengmiMsg('恭喜您点评成功!', U('booking/index'));
                }
                $this->fengmiMsg('点评失败！');
            }else {
                $details = D('Booking')->find($detail['shop_id']);
                $this->assign('details', $details);
                $this->assign('order_id', $order_id);
                $this->display();
            }
        }
    }
}
