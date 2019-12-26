<?php


class HotelsAction extends CommonAction {

    protected $types = array();
    protected $cates = array();

    public function _initialize() {
        parent::_initialize();
		 if ($this->_CONFIG['operation']['hotels'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $this->types = D('Hotelbrand')->fetchAll();
        $this->assign('types', $this->types);
        $this->cates = D('Hotel')->getHotelCate();
        $this->assign('cates', $this->cates);
        $this->stars = D('Hotel')->getHotelStar();
        $this->assign('stars', $this->stars);
        $this->assign('roomtype',D('Hotelroom')->getRoomType());
    }


    public function index() {
        $hotel = D('Hotel');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('audit' => 1, 'closed' => 0, 'city_id' => $this->city_id);
        $linkArr = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['hotel_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
            $linkArr['keywrod'] = $map['hotel_name'];
        }
        $cate_id = (int) $this->_param('cate_id');
        if($cate_id){
            $map['cate_id'] = $cate_id;
            $linkArr['cate_id'] = $cate_id;
        }
        $this->assign('cate_id', $cate_id);
        $type = (int) $this->_param('type');
        if($type){
            $map['type'] = $type;
            $linkArr['type'] = $type;
        }
        $this->assign('type', $type);
        $area_id = (int) $this->_param('area_id');
        if ($area_id) {
            $map['area_id'] = $area_id;
            $linkArr['area_id'] = $area_id;
        }
        $this->assign('area_id', $area_id);
        $business_id = (int) $this->_param('business_id');
        if ($business_id) {
            $map['business_id'] = $business_id;
            $linkArr['business_id'] = $business_id;
        }
        $this->assign('business_id', $business_id);
        $from_price = (int) $this->_param('fp');
        $to_price = (int) $this->_param('tp');
        if(!$from_price && $to_price){
            $map['price'] = array('ELT', $to_price);
        }elseif($from_price && !$to_price){
            $map['price'] = array('GT', $from_price);
        }elseif($from_price&&$to_price){
            $map['price'] = array('between', $from_price.','.$to_price);
        }
        $this->assign('fp',$from_price);
        $this->assign('tp',$to_price);
        
        $order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 's':
                $orderby = array('star' => 'desc');
                $linkArr['order'] = $order;
                break;
            case 'p':
                $orderby = array('price' => 'asc');
                $linkArr['order'] = $order;
                break;
            case 'f':
                $orderby = array('score' => 'desc');
                $linkArr['order'] = $order;
                break;
            default:
                $orderby = array('star' => 'desc', 'sold_num' => 'desc', 'hotel_id' => 'desc');
                break;
        }
        $this->assign('order', $order);

        if ($is_wifi = (int) $this->_param('is_wifi')) {
            $linkArr['is_wifi'] = $is_wifi;
            $map['is_wifi'] = $is_wifi;
        }
        $this->assign('is_wifi', $is_wifi);

        if ($is_kt = (int) $this->_param('is_kt')) {
            $linkArr['is_kt'] = $is_kt;
            $map['is_kt'] = $is_kt;
        }
        $this->assign('is_kt', $is_kt);
        
        if ($is_nq = (int) $this->_param('is_nq')) {
            $linkArr['is_nq'] = $is_nq;
            $map['is_nq'] = $is_nq;
        }
        $this->assign('is_nq', $is_nq);
        
        if ($is_xyj = (int) $this->_param('is_xyj')) {
            $linkArr['is_xyj'] = $is_xyj;
            $map['is_xyj'] = $is_xyj;
        }
        $this->assign('is_xyj', $is_xyj);
        
        if ($is_tv = (int) $this->_param('is_tv')) {
            $linkArr['is_tv'] = $is_tv;
            $map['is_tv'] = $is_tv;
        }
        $this->assign('is_tv', $is_tv);
        
        if ($is_ly = (int) $this->_param('is_ly')) {
            $linkArr['is_ly'] = $is_ly;
            $map['is_ly'] = $is_ly;
        }
        $this->assign('is_ly', $is_ly);
        
        if ($is_bx = (int) $this->_param('is_bx')) {
            $linkArr['is_bx'] = $is_bx;
            $map['is_bx'] = $is_bx;
        }
        $this->assign('is_bx', $is_bx);
        
        if ($is_base = (int) $this->_param('is_base')) {
            $linkArr['is_base'] = $is_base;
            $map['is_base'] = $is_base;
        }
        $this->assign('is_base', $is_base);
        
        if ($is_rsh = (int) $this->_param('is_rsh')) {
            $linkArr['is_rsh'] = $is_rsh;
            $map['is_rsh'] = $is_rsh;
        }
        $this->assign('is_rsh', $is_rsh);
        $into_time = htmlspecialchars($_COOKIE['into_time']);
        $out_time = htmlspecialchars($_COOKIE['out_time']);    
        $this->assign('into_time',$into_time); 
        $this->assign('out_time',$out_time);
        $count = $hotel->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $hotel->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids  = array();
        foreach($list as $k=>$val){
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $tuans = D('Tuan')->where(array('shop_id'=>array('IN',$shop_ids)))->select();
        foreach($list as $k=>$val){
            foreach($tuans as $kk=>$v){
                if($val['shop_id'] == $v['shop_id']){
                    $list[$k]['have_tuan'] = 1;
                }
            }
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('linkArr', $linkArr);
        $this->display(); // 输出模板
    }

    public function business(){
        if(IS_AJAX){
            $area_id = (int)$this->_param('area_id');
            $list = D('Business')->where(array('area_id'=>$area_id))->order(array('orderby'=>'asc'))->select();
            if($list){
                $this->ajaxReturn(array('status'=>'success','list'=>$list));
            }
        }
    }

    

    public function detail($hotel_id){
        $obj = D('Hotel');
        if(!$hotel_id = (int)$hotel_id){
            $this->error('该酒店不存在');
        }elseif(!$detail = $obj->find($hotel_id)){
            $this->error('该酒店不存在');
        }elseif($detail['closed'] == 1||$detail['audit'] == 0){
            $this->error('该酒店已删除或未通过审核');
        }else{
            $pics = D('Hotelpics')->where(array('hotel_id'=>$hotel_id))->select();
            $pics[] = array('photo'=>$detail['photo']);
            $this->assign('photos',$pics);
            $into_time = htmlspecialchars($_COOKIE['into_time']);
            $out_time = htmlspecialchars($_COOKIE['out_time']); 
            //房间
            $room_list = D('Hotelroom')->where(array('hotel_id'=>$hotel_id))->select();
            $room_count = D('Hotelroom')->where(array('hotel_id'=>$hotel_id))->count();
            $this->assign('room_list',$room_list);
            $this->assign('room_count',$room_count);
            //评论
            $comment = D('Hotelcomment');
            import('ORG.Util.Page'); // 导入分页类
            $map = array('closed' => 0, 'hotel_id' => $hotel_id);
            if($have_photo = (int)$this->_param('have_photo')){
                $map['have_photo'] = $have_photo;
                $this->assign('have_photo',$have_photo);
            }
            $count = $comment->where($map)->count(); // 查询满足要求的总记录数 
            $Page = new Page($count, 5); // 实例化分页类 传入总记录数和每页显示的记录数
            $show = $Page->show(); // 分页显示输出
            $list = $comment->where($map)->order(array('comment_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $user_ids = $comment_ids = array();
            foreach ($list as $k => $val) {
                $user_ids[$val['user_id']] = $val['user_id'];
                $comment_ids[$val['comment_id']] = $val['comment_id'];
            }
            if (!empty($user_ids)) {
                $this->assign('users', D('Users')->itemsByIds($user_ids));
            }
            if (!empty($comment_ids)) {
                $this->assign('pics', D('Hotelcommentpics')->where(array('comment_id' => array('IN', $comment_ids)))->select());
            }
            //套餐
            $tuan_list = D('Tuan')->where(array('audit' => 1, 'closed' => 0, 'city_id' => $this->city_id, 'end_date' => array('EGT', NOW),'bg_date' => array('ELT', NOW),'shop_id'=>$detail['shop_id']))->limit(3)->select();
            $this->assign('tuan_list',$tuan_list);
            $this->assign('list', $list); // 赋值数据集
            $this->assign('page', $show); // 赋值分页输出
            $this->assign('into_time',$into_time); 
            $this->assign('out_time',$out_time);
            $this->assign('detail',$detail);
            $this->assign('height_num',675);
            $this->display();
        }
    }

    
    
    public function order($room_id){
        $obj = D('Hotelroom');
        if(!$room_id = (int)$room_id){
            $this->error('房间不存在');
        }elseif(!$detail = $obj->find($room_id)){
            $this->error('房间不存在');
        }elseif($detail['sku'] == 0){
            $this->error('房间已经预订完了');
        }else{
            $hotel = D('Hotel')->find($detail['hotel_id']);
            $into_time = htmlspecialchars($_COOKIE['into_time']);
            $out_time = htmlspecialchars($_COOKIE['out_time']);
            $this->assign('hotel',$hotel);
            $this->assign('detail',$detail);
            $this->assign('into_time',$into_time); 
            $this->assign('out_time',$out_time);
            $this->display();
        }
    }

    
    public function orderCreate(){
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        $room_id = (int) $_POST['room_id'];
        $detail = D('Hotelroom')->find($room_id);
        if (empty($detail)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该房间不存在'));
        }
		$Hotel = D('Hotel')->find($detail['hotel_id']);
		if (false == D('Shop')->check_shop_user_id($Hotel['shop_id'],$this->uid)) {//不能购买自己家的产品
			 $this->ajaxReturn(array('status' => 'error', 'msg' => '您不能订购自己的酒店'));
		}
        if (IS_AJAX) {
            $num = (int) $_POST['num'];
            if (empty($num)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '房间数不能为空'));
            }
            if ($num > $detail['sku'] ) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '房间不够了'));
            }
            $data = array(
                'room_id' => $room_id,
                'num' => $num,
                'price'=> $detail['price'],
                'user_id' => $this->uid,
                'hotel_id'=>$detail['hotel_id'],
                'create_time'=>NOW_TIME,
                'create_ip'=>  get_client_ip(),
            );
            $data['online_pay'] = (int) $_POST['online_pay'];
            $data['stime'] = htmlspecialchars($_POST['stime']);
            $data['ltime'] = htmlspecialchars($_POST['ltime']);
            if(!$data['stime'] || !$data['ltime']){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '选择时间不能为空'));
            }
            if($data['stime'] > $data['ltime']){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '选择时间不正确'));
            }
            $data['name'] = htmlspecialchars($_POST['realname']);
            if(!$data['name']){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '入住人姓名不能为空'));
            }
            $data['mobile'] = htmlspecialchars($_POST['mobile']);
            if(!$data['mobile']){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '入住人手机号不能为空'));
            }
            $data['note'] = htmlspecialchars($_POST['note']);
            $data['last_time'] = htmlspecialchars($_POST['last_time']);
            $night_num = $this->diffBetweenTwoDays($data['stime'],$data['ltime']);
            $data['amount'] = $night_num*$num*$detail['price'];
            $data['jiesuan_amount'] = $night_num*$num*$detail['settlement_price'];
            if($order_id = D('Hotelorder')->add($data)){
                D('Hotel')->updateCount($detail['hotel_id'],'sold_num');
                D('Hotelroom')->updateCount($room_id,'sku','-1');
                cookie('into_time', null);
                cookie('out_time', null);
                $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜下单成功','order_id'=>$order_id,'online_pay'=>$data['online_pay']));
            }else{
                $this->ajaxReturn(array('status' => 'error', 'msg' => '下单失败'));
            }
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

    public function pay(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Hotelorder')->find($order_id);
        if (empty($order) || $order['order_status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
        $room = D('Hotelroom')->find($order['room_id']);
        if (empty($room) || $room['hotel_id'] != $order['hotel_id'] ) {
            $this->error('该房间不存在');
            die;
        }
        $this->assign('payment', D('Payment')->getPayments());
        $this->assign('room', $room);
        $this->assign('order', $order);
        $this->display();
    }
    
    
    public function pay2(){

        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
		 
        $order_id = (int) $this->_get('order_id');
        $order = D('Hotelorder')->find($order_id);
        if (empty($order) || $order['order_status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
        if (!$code = $this->_post('code')) {
            $this->baoError('请选择支付方式！');
        }
        
        $payment = D('Payment')->checkPayment($code);
        if (empty($payment)) {
            $this->baoError('该支付方式不存在');
        }
        $room = D('Hotelroom')->find($order['room_id']);
        if (empty($room) || $room['hotel_id'] != $order['hotel_id'] ) {
            $this->baoError('该房间不存在');
        }
        $logs = D('Paymentlogs')->getLogsByOrderId('hotel', $order_id);
        if (empty($logs)) {
            $logs = array(
                'type' => 'hotel',
                'user_id' => $this->uid,
                'order_id' => $order_id,
                'code' => $code,
                'need_pay' => $order['amount']*100,
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
                'is_paid' => 0
            );
            $logs['log_id'] = D('Paymentlogs')->add($logs);
        } else {
            $logs['need_pay'] = $order['amount']*100;
            $logs['code'] = $code;
            D('Paymentlogs')->save($logs);
        }
        D('Weixintmpl')->weixin_notice_hotel_user($order_id,$this->uid,1);//酒店微信通知用户
        $this->baoSuccess('选择支付方式成功！下面请进行支付！', U('payment/payment', array('log_id' => $logs['log_id'])));
    }

}
