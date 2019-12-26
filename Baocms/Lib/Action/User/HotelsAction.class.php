<?php
class HotelsAction extends CommonAction { 

	protected function _initialize(){
       parent::_initialize();
        if ($this->_CONFIG['operation']['hotels'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
	
    public function index() {
        $st = (int) $this->_param('st');
		$this->assign('st', $st);
		$this->display(); 
    }	
	
    public function loaddata() {
		$hotelorder = D('Hotelorder');
		import('ORG.Util.Page'); 
		$map = array('user_id' => $this->uid); 
		$st = (int) $this->_param('st');
		if ($st == 1) {
			$map['online_pay'] = 1;
		}elseif ($st == 0) {
			$map['online_pay'] = 0;
		}else{
			$map['online_pay'] = 0;
		}
		$count = $hotelorder->where($map)->count(); 
          
		$Page = new Page($count, 10); 
		$show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
			die('0');
		}
		$list = $hotelorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$room_ids  = $hotel_ids = array();
        foreach ($list as $k => $val) {
            $room_ids[$val['room_id']] = $val['room_id'];
            $hotel_ids[$val['hotel_id']] = $val['hotel_id'];
        }
        if (!empty($hotel_ids)) {
            $this->assign('hotels', D('Hotel')->itemsByIds($hotel_ids));
        }
        if($room_ids){
            $this->assign('rooms', D('Hotelroom')->itemsByIds($room_ids));
        }
		$this->assign('list', $list); 
		$this->assign('page', $show); 
		$this->display(); 
	}
    
    
    public function detail($order_id){
        if(!$order_id = (int)$order_id){
            $this->error('该订单不存在');
        }elseif(!$detail = D('Hotelorder')->find($order_id)){
            $this->error('该订单不存在');
        }elseif($detail['user_id'] != $this->uid){
            $this->error('非法的订单操作');
        }else{
           $detail['night_num'] = $this->diffBetweenTwoDays($detail['stime'],$detail['ltime']); 
           $detail['room'] = D('Hotelroom')->find($detail['room_id']); 
           $detail['hotel'] = D('Hotel')->find($detail['hotel_id']);
           $this->assign('detail',$detail);
           $this->assign('roomtype',D('Hotelroom')->getRoomType());
           $this->display();
        }
    }
    
    function diffBetweenTwoDays ($day1, $day2){
          $second1 = strtotime($day1);
          $second2 = strtotime($day2);

          if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
          }
          return ($second1 - $second2) / 86400;
    }

    
   public function cancel($order_id){
       if(!$order_id = (int)$order_id){
           $this->error('订单不存在');
       }elseif(!$detail = D('Hotelorder')->find($order_id)){
           $this->error('订单不存在');
       }elseif($detail['user_id'] != $this->uid){
           $this->error('非法操作订单');
       }else{
           if(false !== D('Hotelorder')->cancel($order_id)){
               $this->success('订单取消成功');
           }else{
               $this->error('订单取消失败');
           }
       }
       
       
   }
   
   public function comment($order_id) {
        if(!$order_id = (int) $order_id){
            $this->error('该订单不存在');
        }elseif(!$detail = D('Hotelorder')->find($order_id)){
            $this->error('该订单不存在');
        }elseif($detail['user_id'] != $this->uid){
            $this->error('非法操作订单');
        }elseif($detail['comment_status'] == 1){
            $this->error('已经评价过了');
        }else{
            if ($this->_Post()) {
                $data = $this->checkFields($this->_post('data', false), array('score', 'content'));
                $data['user_id'] = $this->uid;
				if (!$Hotel = D('Hotel')->find($detail['hotel_id'])) {
                    $this->fengmiMsg('没有找到对应的酒店，暂时无法点评，请稍后再试');
                }
				$data['shop_id'] = $Hotel['shop_id'];
                $data['hotel_id'] = $detail['hotel_id'];
                $data['order_id'] = $order_id;
                $data['score'] = (int) $data['score'];
                if (empty($data['score'])) {
                    $this->fengmiMsg('评分不能为空');
                }
                if ($data['score'] > 5 || $data['score'] < 1) {
                    $this->fengmiMsg('评分为1-5之间的数字');
                }
                $data['cost'] = (int) $data['cost'];
                $data['content'] = htmlspecialchars($data['content']);
                if (empty($data['content'])) {
                    $this->fengmiMsg('评价内容不能为空');
                }
                if ($words = D('Sensitive')->checkWords($data['contents'])) {
                    $this->fengmiMsg('评价内容含有敏感词：' . $words);
                }
				$data['show_date'] = date('Y-m-d', NOW_TIME + ($this->_CONFIG['mobile']['data_hotel_dianping'] * 86400));
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                $photos = $this->_post('photos', false);
                if($photos){
                    $data['have_photo'] = 1;
                }
                
                if ($comment_id = D('Hotelcomment')->add($data)) {
                    $local = array();
                    foreach ($photos as $val) {
                        if (isImage($val))
                            $local[] = $val;
                    }
                    if (!empty($local)){
                        foreach($local as $k=>$val){
                            D('Hotelcommentpics')->add(array('comment_id'=>$comment_id,'photo'=>$val));
                        }
                    }
                    D('Hotelorder')->save(array('order_id'=>$order_id,'comment_status'=>1));
                    D('Users')->updateCount($this->uid, 'ping_num');
                    D('Hotel')->updateCount($detail['hotel_id'],'comments');
                    D('Hotel')->updateCount($detail['hotel_id'],'score',$data['score']);
                    $this->fengmiMsg('恭喜您点评成功!', U('hotels/index'));
                }
                $this->fengmiMsg('点评失败！');
            }else {
                $this->assign('detail', $detail);
                $this->assign('room',D('Hotelroom')->find($detail['room_id']));
                $this->assign('roomtype',D('Hotelroom')->getRoomType());
                $this->display();
            }
        }
    }

}
